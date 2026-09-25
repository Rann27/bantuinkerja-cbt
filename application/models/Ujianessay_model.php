<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ujianessay_model extends CI_Model {

    public function getIdDosen($nip)
    {
        $this->db->select('id_dosen, nama_dosen')->from('dosen')->where('nip', $nip);
        return $this->db->get()->row();
    }

    public function getJumlahSoal($dosen)
    {
        $this->db->select('COUNT(id_soal_essay) as jml_soal');
        $this->db->from('soal_essay');
        $this->db->where('dosen_id', $dosen);
        return $this->db->get()->row();
    }

    public function getDataUjian($id)
    {
        $this->datatables->select('a.id_ujian_essay, a.token_essay, a.nama_ujian_essay, b.nama_matkul, a.jumlah_soal_essay, CONCAT(a.tgl_mulai_essay, " <br/> (", a.waktu_essay, " Menit)") as waktu, a.jenis_essay');
        $this->datatables->from('m_ujian_essay a');
        $this->datatables->join('matkul b', 'a.matkul_id = b.id_matkul');
        if($id!==null){
            $this->datatables->where('dosen_id', $id);
        }
        return $this->datatables->generate();
    }

    public function getUjianById($id)
    {
        $this->db->select('*');
        $this->db->from('m_ujian_essay a');
        $this->db->join('dosen b', 'a.dosen_id=b.id_dosen');
        $this->db->join('matkul c', 'a.matkul_id=c.id_matkul');
        $this->db->where('id_ujian_essay', $id);
        return $this->db->get()->row();
    }

    public function getSoal($id)
    {
        $ujian = $this->getUjianById($id);
        $order = $ujian->jenis_essay==="acak" ? 'rand()' : 'id_soal_essay';

        $this->db->select('id_soal_essay, essay_soal, file_essay, tipe_file_essay');
        $this->db->from('soal_essay');
        $this->db->where('dosen_id', $ujian->dosen_id);
        $this->db->where('matkul_id', $ujian->matkul_id);
        $this->db->order_by($order);
        $this->db->limit($ujian->jumlah_soal_essay);
        return $this->db->get()->result();
    }

    public function HslUjianById($id, $dt=false)
    {
        if($dt===false){
            $db = "db";
            $get = "get";
        }else{
            $db = "datatables";
            $get = "generate";
        }
        
        $this->$db->select('d.id, a.nama, b.nama_kelas, c.nama_jurusan');
        $this->$db->from('mahasiswa a');
        $this->$db->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->$db->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        $this->$db->join('h_ujian_essay d', 'a.id_mahasiswa=d.mahasiswa_id');
        $this->$db->where(['d.ujian_id_essay' => $id]);
        return $this->$db->$get();
    }

    public function HslUjian($id, $mhs)
    {
        $this->db->select('*, UNIX_TIMESTAMP(tgl_selesai) as waktu_habis');
        $this->db->from('h_ujian_essay');
        $this->db->where('ujian_id_essay', $id);
        $this->db->where('mahasiswa_id', $mhs);
        return $this->db->get();
    }

    public function ambilSoal($pc_urut_soal1, $pc_urut_soal_arr)
    {
        $this->db->select("*, {$pc_urut_soal1} AS jawaban");
        $this->db->from('tb_soal');
        $this->db->where('id_soal', $pc_urut_soal_arr);
        return $this->db->get()->row();
    }

    public function getListUjian($id, $kelas)
    {
        $this->datatables->select("a.id_ujian_essay, e.nama_dosen, d.nama_kelas, a.nama_ujian_essay, b.nama_matkul, a.jumlah_soal_essay, CONCAT(a.tgl_mulai_essay, ' <br/> (', a.waktu_essay, ' Menit)') as waktu,  (SELECT COUNT(id) FROM h_ujian_essay h WHERE h.mahasiswa_id = {$id} AND h.ujian_id_essay = a.id_ujian_essay) AS ada");
        $this->datatables->from('m_ujian_essay a');
        $this->datatables->join('matkul b', 'a.matkul_id = b.id_matkul');
        $this->datatables->join('kelas_dosen c', "a.dosen_id = c.dosen_id");
        $this->datatables->join('kelas d', 'c.kelas_id = d.id_kelas');
        $this->datatables->join('dosen e', 'e.id_dosen = c.dosen_id');
        $this->datatables->where('d.id_kelas', $kelas);
        return $this->datatables->generate();
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

}