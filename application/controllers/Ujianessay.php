<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ujianessay extends CI_Controller
{

    public $mhs, $user;

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->helper('my');
        $this->load->model('Master_model', 'master');
        $this->load->model('Essay_model', 'essay');
        $this->load->model('Ujianessay_model', 'ujianessay');
        $this->load->model('Ujian_model', 'ujian');
        $this->form_validation->set_error_delimiters('', '');

        $this->user = $this->ion_auth->user()->row();
        $this->mhs     = $this->ujian->getIdMahasiswa($this->user->username);
    }

    public function akses_dosen()
    {
        if (!$this->ion_auth->in_group('dosen')) {
            show_error('Halaman ini khusus untuk dosen untuk membuat Test Online, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
    }

    public function akses_mahasiswa()
    {
        if (!$this->ion_auth->in_group('mahasiswa')) {
            show_error('Halaman ini khusus untuk mahasiswa mengikuti ujian, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
    }

    public function master()
    {
        $this->akses_dosen();
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul'    => 'Ujian Essay',
            'subjudul' => 'Data Ujian Essay',
            'dosen' => $this->ujianessay->getIdDosen($user->username),
        ];
        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('ujianessay/data');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function output_json($data, $encode = true)
    {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function json($id = null)
    {
        $this->akses_dosen();

        $this->output_json($this->ujianessay->getDataUjian($id), false);
    }

    public function add()
    {
        $this->akses_dosen();

        $user = $this->ion_auth->user()->row();

        $data = [
            'user'         => $user,
            'judul'        => 'Ujian essay',
            'subjudul'    => 'Tambah Ujian Esaay',
            'matkul'    => $this->essay->getMatkulDosen($user->username),
            'dosen'        => $this->ujian->getIdDosen($user->username),
        ];

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('ujianessay/add');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function edit($id)
    {
        $this->akses_dosen();

        $user = $this->ion_auth->user()->row();

        $data = [
            'user'         => $user,
            'judul'        => 'Ujian Essay',
            'subjudul'    => 'Edit Ujian Essay',
            'matkul'    => $this->essay->getMatkulDosen($user->username),
            'dosen'        => $this->ujianessay->getIdDosen($user->username),
            'ujian'        => $this->ujianessay->getUjianById($id),
        ];

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('ujianessay/edit');
        $this->load->view('_templates/dashboard/_footer.php');
    }


    public function convert_tgl($tgl)
    {
        $this->akses_dosen();
        return date('Y-m-d H:i:s', strtotime($tgl));
    }

    public function validasi()
    {
        $this->akses_dosen();

        $user     = $this->ion_auth->user()->row();
        $dosen     = $this->ujianessay->getIdDosen($user->username);
        $jml     = $this->ujianessay->getJumlahSoal($dosen->id_dosen)->jml_soal;
        $jml_a     = $jml + 1; // Jika tidak mengerti, silahkan baca user_guide codeigniter tentang form_validation pada bagian less_than

        $this->form_validation->set_rules('nama_ujian', 'Nama Ujian', 'required|alpha_numeric_spaces|max_length[50]');
        $this->form_validation->set_rules('jumlah_soal', 'Jumlah Soal', "required|integer|less_than[{$jml_a}]|greater_than[0]", ['less_than' => "Soal tidak cukup, anda hanya punya {$jml} soal"]);
        $this->form_validation->set_rules('tgl_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('tgl_selesai', 'Tanggal Selesai', 'required');
        $this->form_validation->set_rules('waktu', 'Waktu', 'required|integer|max_length[4]|greater_than[0]');
        $this->form_validation->set_rules('jenis', 'Acak Soal', 'required|in_list[acak,urut]');
    }

    public function save()
    {
        $this->validasi();
        $this->load->helper('string');

        $method         = $this->input->post('method', true);
        $dosen_id       = $this->input->post('dosen_id', true);
        $matkul_id      = $this->input->post('matkul_id', true);
        $nama_ujian     = $this->input->post('nama_ujian', true);
        $jumlah_soal    = $this->input->post('jumlah_soal', true);
        $tgl_mulai      = $this->convert_tgl($this->input->post('tgl_mulai',     true));
        $tgl_selesai    = $this->convert_tgl($this->input->post('tgl_selesai', true));
        $waktu          = $this->input->post('waktu', true);
        $jenis          = $this->input->post('jenis', true);
        $token          = strtoupper(random_string('alpha', 5));

        if ($this->form_validation->run() === FALSE) {
            $data['status'] = false;
            $data['errors'] = [
                'nama_ujian'     => form_error('nama_ujian'),
                'jumlah_soal'     => form_error('jumlah_soal'),
                'tgl_mulai'     => form_error('tgl_mulai'),
                'tgl_selesai'     => form_error('tgl_selesai'),
                'waktu'         => form_error('waktu'),
                'jenis'         => form_error('jenis'),
            ];
        } else {
            $input = [
                'nama_ujian_essay'     => $nama_ujian,
                'jumlah_soal_essay' => $jumlah_soal,
                'tgl_mulai_essay'     => $tgl_mulai,
                'tgl_akhir_essay'     => $tgl_selesai,
                'waktu_essay'         => $waktu,
                'jenis_essay'         => $jenis,
            ];
            if ($method === 'add') {
                $input['dosen_id']        = $dosen_id;
                $input['matkul_id']     = $matkul_id;
                $input['token_essay']    = $token;
                $action = $this->master->create('m_ujian_essay', $input);
            } else if ($method === 'edit') {
                $id_ujian = $this->input->post('id_ujian_essay', true);
                $action = $this->master->update('m_ujian_essay', $input, 'id_ujian_essay', $id_ujian);
            }
            $data['status'] = $action ? TRUE : FALSE;
        }
        $this->output_json($data);
    }

    public function delete()
    {
        $this->akses_dosen();
        $chk = $this->input->post('checked', true);
        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            if ($this->master->delete('m_ujian_essay', $chk, 'id_ujian_essay')) {
                $this->output_json(['status' => true, 'total' => count($chk)]);
            }
        }
    }

    public function refresh_token($id)
    {
        $this->load->helper('string');
        $data['token_essay'] = strtoupper(random_string('alpha', 5));
        $refresh = $this->master->update('m_ujian_essay', $data, 'id_ujian_essay', $id);
        $data['status'] = $refresh ? TRUE : FALSE;
        $this->output_json($data);
    }

    /*INI UNTUK UJIAN MAHASISWA*/
    public function list_json()
    {
        $this->akses_mahasiswa();

        $list = $this->ujianessay->getListUjian($this->mhs->id_mahasiswa, $this->mhs->kelas_id);
        $this->output_json($list, false);
    }

    public function list()
    {
        $this->akses_mahasiswa();

        $user = $this->ion_auth->user()->row();

        $data = [
            'user'         => $user,
            'judul'        => 'Ujian Essay',
            'subjudul'    => 'List Ujian Essay',
            'mhs'         => $this->ujianessay->getIdMahasiswa($user->username),
        ];
        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('ujianessay/list');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function token($id)
    {
        $this->akses_mahasiswa();
        $user = $this->ion_auth->user()->row();

        $data = [
            'user'         => $user,
            'judul'        => 'Ujian Essay',
            'subjudul'     => 'Token Ujian Essay',
            'mhs'          => $this->ujianessay->getIdMahasiswa($user->username),
            'ujian'        => $this->ujianessay->getUjianById($id),
            'encrypted_id' => urlencode($this->encryption->encrypt($id))
        ];
        $this->load->view('_templates/topnav/_header.php', $data);
        $this->load->view('ujianessay/token');
        $this->load->view('_templates/topnav/_footer.php');
    }

    public function cektoken()
    {
        $id = $this->input->post('id_ujian', true);
        $token = $this->input->post('token', true);
        $cek = $this->ujianessay->getUjianById($id);

        $data['status'] = $token === $cek->token_essay ? TRUE : FALSE;
        $this->output_json($data);
    }

    public function index()
    {
        $this->akses_mahasiswa();
        $key = $this->input->get('key', true);
        $id  = $this->encryption->decrypt(rawurldecode($key));

        $ujian         = $this->ujianessay->getUjianById($id);
        $soal         = $this->ujianessay->getSoal($id);

        $mhs        = $this->mhs;
        $h_ujian     = $this->ujianessay->HslUjian($id, $mhs->id_mahasiswa);

        $cek_sudah_ikut = $h_ujian->num_rows();

        if ($cek_sudah_ikut < 1) {
            $soal_urut_ok     = array();
            $i = 0;
            foreach ($soal as $s) {
                $soal_per = new stdClass();
                $soal_per->id_soal_essay    = $s->id_soal_essay;
                $soal_per->essay_soal       = $s->essay_soal;
                $soal_per->file_essay       = $s->file_essay;
                $soal_per->tipe_file_essay  = $s->tipe_file_essay;
                $soal_urut_ok[$i]         = $soal_per;
                $i++;
            }
            $soal_urut_ok     = $soal_urut_ok;
            $list_id_soal    = "";
            if (!empty($soal)) {
                foreach ($soal as $d) {
                    $list_id_soal .= $d->id_soal_essay . ",";
                }
            }
            $list_id_soal     = substr($list_id_soal, 0, -1);
            $waktu_selesai     = date('Y-m-d H:i:s', strtotime("+{$ujian->waktu_essay} minute"));
            $time_mulai        = date('Y-m-d H:i:s');

            $input = [
                'ujian_id_essay'  => $id,
                'mahasiswa_id'    => $mhs->id_mahasiswa,
                'list_soal_essay' => $list_id_soal,
                'tgl_mulai'       => $time_mulai,
                'tgl_selesai'     => $waktu_selesai,
                'status'          => 'Y'
            ];

            $this->master->create('h_ujian_essay', $input);

            // Setelah insert wajib refresh dulu
            redirect('ujianessay/?key=' . urlencode($key), 'location', 301);
        }
        // echo "Hallo";

        $q_soal = $h_ujian->row();
        $detail_tes = $q_soal;


        $html = '';
        $no = 1;


        //Enkripsi Id Tes
        $id_tes = $this->encryption->encrypt($detail_tes->id);

        $data = [
            'user'      => $this->user,
            'mhs'       => $this->mhs,
            'judul'     => 'Ujian',
            'subjudul'  => 'Lembar Ujian',
            'soal'      => $soal,
            'no'        => $no,
            'html'      => $html,
            'id_tes'    => $id_tes
        ];

        // echo $id;

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        $this->load->view('_templates/topnav/_header.php', $data);
        $this->load->view('ujianessay/sheet');
        $this->load->view('_templates/topnav/_footer.php');
    }
}
