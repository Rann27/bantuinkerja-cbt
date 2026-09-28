<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ujian_model extends CI_Model
{
    // Kelonggaran setelah tgl_selesai untuk request simpan yang masih di jalan (jaringan lambat)
    const TOLERANSI_DETIK = 30;

    public function getTesById($id_tes)
    {
        return $this->db->get_where('h_ujian', ['id' => $id_tes])->row();
    }

    public function waktuHabis($tes)
    {
        return time() > strtotime($tes->tgl_selesai) + self::TOLERANSI_DETIK;
    }

    /**
     * Hitung nilai dari jawaban yang tersimpan di h_ujian.
     * Rumus sama dengan versi lama: nilai = jumlah benar * rata-rata bobot soal.
     */
    public function hitungNilai($id_tes)
    {
        $pc_jawaban  = explode(",", $this->getJawaban($id_tes));
        $jumlah_soal = sizeof($pc_jawaban);

        $jawaban = [];
        foreach ($pc_jawaban as $jwb) {
            $pc_dt = explode(":", $jwb);
            $jawaban[$pc_dt[0]] = isset($pc_dt[1]) ? $pc_dt[1] : '';
        }

        $kunci = $this->db->select('id_soal, jawaban, bobot')
            ->where_in('id_soal', array_keys($jawaban))
            ->get('tb_soal')->result();

        $jumlah_benar = 0;
        $total_bobot  = 0;
        foreach ($kunci as $k) {
            $total_bobot += $k->bobot;
            if ($jawaban[$k->id_soal] !== '' && $jawaban[$k->id_soal] === $k->jawaban) {
                $jumlah_benar++;
            }
        }

        $nilai_bobot = $total_bobot / $jumlah_soal;

        return [
            'jml_benar'   => $jumlah_benar,
            'nilai'       => floor($jumlah_benar * $nilai_bobot),
            'nilai_bobot' => floor($nilai_bobot),
        ];
    }

    public function selesaikanUjian($id_tes)
    {
        $d_update = $this->hitungNilai($id_tes);
        $d_update['status'] = 'N';
        $this->db->where('id', $id_tes)->update('h_ujian', $d_update);
        return $d_update;
    }

    /**
     * Menilai & menutup ujian yang waktunya sudah habis tetapi tidak pernah di-submit
     * (browser ditutup, koneksi putus, perangkat mati). Nilai dihitung dari jawaban tersimpan.
     */
    public function finalisasiKedaluwarsa($where = [])
    {
        $batas = date('Y-m-d H:i:s', time() - self::TOLERANSI_DETIK);
        $this->db->select('id')->from('h_ujian')->where('status', 'Y')->where('tgl_selesai <', $batas);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $tes = $this->db->get()->result();

        foreach ($tes as $t) {
            $this->selesaikanUjian($t->id);
        }
        return count($tes);
    }

    public function getDataUjian($id)
    {
        $this->datatables->select('a.id_ujian,a.status_ujian, a.token, a.nama_ujian, b.nama_matkul,a.status_curang,a.status_essay, a.jumlah_soal, CONCAT(a.tgl_mulai, " <br/> (", a.waktu, " Menit)") as waktu, a.jenis');
        $this->datatables->from('m_ujian a');
        $this->datatables->join('matkul b', 'a.matkul_id = b.id_matkul');
        if ($id !== null) {
            $this->datatables->where('dosen_id', $id);
        }
        return $this->datatables->generate();
    }

    public function getListUjian($id, $kelas)
    {
        $this->datatables->select("a.id_ujian,a.status_ujian, e.nama_dosen, d.nama_kelas, a.nama_ujian, b.nama_matkul,a.status_curang, a.jumlah_soal,CONCAT(a.tgl_mulai, ' <br/> (', a.waktu, ' Menit)') as waktu,  (SELECT COUNT(id) FROM h_ujian h WHERE h.mahasiswa_id = {$id} AND h.ujian_id = a.id_ujian) AS ada,(SELECT status FROM h_ujian h WHERE h.mahasiswa_id = {$id} AND h.ujian_id = a.id_ujian) AS statusujian,(SELECT tgl_selesai FROM h_ujian h WHERE h.mahasiswa_id = {$id} AND h.ujian_id = a.id_ujian) AS cektanggal");
        $this->datatables->from('m_ujian a');
        $this->datatables->join('matkul b', 'a.matkul_id = b.id_matkul');
        $this->datatables->join('kelas_dosen c', "a.dosen_id = c.dosen_id");
        $this->datatables->join('kelas d', 'c.kelas_id = d.id_kelas');
        $this->datatables->join('dosen e', 'e.id_dosen = c.dosen_id');
        $this->datatables->where('d.id_kelas', $kelas);
        if ($this->ion_auth->in_group('mahasiswa')) {
            $this->datatables->where('a.status_ujian', '1');
        }
        return $this->datatables->generate();
    }

    public function getUjianById($id)
    {
        $this->db->select('*');
        $this->db->from('m_ujian a');
        $this->db->join('dosen b', 'a.dosen_id=b.id_dosen');
        $this->db->join('matkul c', 'a.matkul_id=c.id_matkul');
        $this->db->where('id_ujian', $id);
        return $this->db->get()->row();
    }

    public function getIdDosen($nip)
    {
        $this->db->select('id_dosen, nama_dosen')->from('dosen')->where('nip', $nip);
        return $this->db->get()->row();
    }

    public function getJumlahSoal($dosen)
    {
        $this->db->select('COUNT(id_soal) as jml_soal');
        $this->db->from('tb_soal');
        $this->db->where('dosen_id', $dosen);
        return $this->db->get()->row();
    }

    public function getIdMahasiswa($nim)
    {
        $this->db->select('*');
        $this->db->from('mahasiswa a');
        $this->db->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->db->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        $this->db->where('nim', $nim);
        return $this->db->get()->row();
    }

    public function HslUjian($id, $mhs)
    {
        $this->db->select('*, UNIX_TIMESTAMP(tgl_selesai) as waktu_habis');
        $this->db->from('h_ujian');
        $this->db->where('ujian_id', $id);
        $this->db->where('mahasiswa_id', $mhs);
        return $this->db->get();
    }

    public function getSoal($id)
    {
        $ujian = $this->getUjianById($id);
        $order = $ujian->jenis === "acak" ? 'rand()' : 'id_soal';

        $this->db->select('id_soal, soal, file, tipe_file, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban');
        $this->db->from('tb_soal');
        $this->db->where('dosen_id', $ujian->dosen_id);
        $this->db->where('matkul_id', $ujian->matkul_id);
        $this->db->order_by($order);
        $this->db->limit($ujian->jumlah_soal);
        return $this->db->get()->result();
    }

    public function getSoalEssay($id)
    {
        $ujian = $this->getUjianById($id);
        $this->db->select('id_soal_essay, essay_soal, file_essay, tipe_file_essay');
        $this->db->from('soal_essay');
        $this->db->where('dosen_id', $ujian->dosen_id);
        $this->db->where('matkul_id', $ujian->matkul_id);
        return $this->db->get()->result();
    }

    public function ambilSoal($jawaban_peserta, $pc_urut_soal_arr)
    {
        // Kolom jawaban (kunci) ditimpa jawaban peserta agar kunci tidak ikut terkirim ke browser
        $this->db->select('*');
        $this->db->select($this->db->escape((string) $jawaban_peserta) . ' AS jawaban', FALSE);
        $this->db->from('tb_soal');
        $this->db->where('id_soal', $pc_urut_soal_arr);
        return $this->db->get()->row();
    }

    public function getJawaban($id_tes)
    {
        $this->db->select('list_jawaban');
        $this->db->from('h_ujian');
        $this->db->where('id', $id_tes);
        return $this->db->get()->row()->list_jawaban;
    }

    public function getHasilUjian($nip = null)
    {
        $this->datatables->select('b.id_ujian, b.nama_ujian, b.jumlah_soal, CONCAT(b.waktu, " Menit") as waktu, b.tgl_mulai');
        $this->datatables->select('c.nama_matkul, d.nama_dosen');
        $this->datatables->from('h_ujian a');
        $this->datatables->join('m_ujian b', 'a.ujian_id = b.id_ujian');
        $this->datatables->join('matkul c', 'b.matkul_id = c.id_matkul');
        $this->datatables->join('dosen d', 'b.dosen_id = d.id_dosen');
        $this->datatables->group_by('b.id_ujian');
        if ($nip !== null) {
            $this->datatables->where('d.nip', $nip);
        }
        return $this->datatables->generate();
    }

    public function HslUjianById($id, $dt = false)
    {
        if ($dt === false) {
            $db = "db";
            $get = "get";
        } else {
            $db = "datatables";
            $get = "generate";
        }

        $this->$db->select('d.id,a.nim, a.nama, b.nama_kelas, c.nama_jurusan, d.jml_benar, d.nilai,d.nilai_essay,d.tgl_mulai,d.tgl_selesai,d.status,d.mahasiswa_id as idmahasiswa,d.ujian_id as idujian,d.list_jawaban');
        $this->$db->from('mahasiswa a');
        $this->$db->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->$db->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        $this->$db->join('h_ujian d', 'a.id_mahasiswa=d.mahasiswa_id');
        // $this->db->order_by('a.kelas_id');
        // $this->db->order_by('a.nama','asc');
        $this->$db->where(['d.ujian_id' => $id]);
        return $this->$db->$get();
    }

    public function HslUjianByIdPdf($id, $dt = false)
    {
        if ($dt === false) {
            $db = "db";
            $get = "get";
        } else {
            $db = "datatables";
            $get = "generate";
        }

        $this->$db->select('d.id,a.nim, a.nama, b.nama_kelas, c.nama_jurusan, d.jml_benar, d.nilai,d.nilai_essay,d.tgl_mulai,d.tgl_selesai,d.status,d.mahasiswa_id as idmahasiswa,d.ujian_id as idujian,d.list_jawaban');
        $this->$db->from('mahasiswa a');
        $this->$db->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->$db->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        $this->$db->join('h_ujian d', 'a.id_mahasiswa=d.mahasiswa_id');
        $this->db->order_by('a.kelas_id');
        $this->db->order_by('a.nama', 'asc');
        $this->$db->where(['d.ujian_id' => $id]);
        return $this->$db->$get();
    }

    public function carisiswa($idujian)
    {
        $this->db->select('a.id_ujian,a.nama_ujian,c.id_mahasiswa,c.nama,c.nim,d.nama_kelas,e.nama_jurusan,f.nilai');
        $this->db->from('m_ujian a');
        $this->db->join('kelas_dosen b', 'a.dosen_id = b.dosen_id');
        $this->db->join('mahasiswa c', 'c.kelas_id = b.kelas_id');
        $this->db->join('kelas  d', 'd.id_kelas = c.kelas_id');
        $this->db->join('jurusan e', 'd.jurusan_id = e.id_jurusan');
        $this->db->join('h_ujian f', 'f.ujian_id = a.id_ujian AND f.mahasiswa_id = c.id_mahasiswa', 'left');
        $this->db->where('a.id_ujian', $idujian);
        $this->db->where('f.nilai IS NULL');
        return $this->db->get()->result();
    }

    public function bandingNilai($id)
    {
        $this->db->select_min('nilai', 'min_nilai');
        $this->db->select_max('nilai', 'max_nilai');
        $this->db->select_avg('FORMAT(FLOOR(nilai),0)', 'avg_nilai');
        $this->db->where('ujian_id', $id);
        return $this->db->get('h_ujian')->row();
    }

    public function deleteUjian($table, $array)
    {
        $this->db->where($array);
        return $this->db->delete($table);
    }
}
