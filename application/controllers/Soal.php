<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Soal extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        } else if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('dosen')) {
            show_error('Hanya Administrator dan dosen yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->helper('my'); // Load Library Ignited-Datatables
        $this->load->model('Master_model', 'master');
        $this->load->model('Soal_model', 'soal');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function output_json($data, $encode = true)
    {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index()
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul'    => 'Soal',
            'subjudul' => 'Bank Soal'
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['matkul'] = $this->master->getAllMatkul();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['matkul'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('soal/data');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function detail($id)
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal',
            'subjudul'  => 'Edit Soal',
            'soal'      => $this->soal->getSoalById($id),
        ];

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('soal/detail');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function add()
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal',
            'subjudul'  => 'Buat Soal'
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('soal/add');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function edit($id)
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal',
            'subjudul'  => 'Edit Soal',
            'soal'      => $this->soal->getSoalById($id),
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('soal/edit');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function data($id = null, $dosen = null)
    {
        // $this->output_json($this->soal->getDataSoal($id, $dosen), false);
        $this->output_json($this->soal->getSoalBaru($id, $dosen), false);
    }

    public function detailsoal($id = null, $dosen = null)
    {
        $this->output_json($this->soal->getDataSoal($id, $dosen), false);
    }

    public function soaldetail($id_soal){
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul'    => 'Soal',
            'subjudul' => 'Detail Soal'
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            // $data['matkul'] = $this->master->getAllMatkul();
            $data['matkul'] = $this->soal->getMatkulDosen($id_soal);
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['matkul'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('soal/detailsoal');
        $this->load->view('_templates/dashboard/_footer.php');
    }
	
	   public  function upload_image()
    {
        $this->file_config2();
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_FILES['filefoto' . $i]['name'])) {
                if (!$this->upload->do_upload('filefoto' . $i))
                    $this->upload->display_errors();
                else
                    redirect('soal');
            }
        }
    }

    public function uploadgambar(){
            $user = $this->ion_auth->user()->row();
            $data = [
                'user'      => $user,
                'judul'     => 'Soal',
                'subjudul'  => 'Upload Gambar Soal dan Jawaban'
            ];
    
            $this->load->view('_templates/dashboard/_header.php', $data);
            $this->load->view('soal/uploadgambar');
            $this->load->view('_templates/dashboard/_footer.php');
    
    }
	
	   public function file_config2()
    {
        $allowed_type     = [
            "image/jpeg", "image/jpg", "image/png", "image/gif",
            "audio/mpeg", "audio/mpg", "audio/mpeg3", "audio/mp3", "audio/x-wav", "audio/wave", "audio/wav",
            "video/mp4", "application/octet-stream"
        ];
        $config['upload_path']      = FCPATH . 'uploads/bank_soal/';
        $config['allowed_types']    = 'jpeg|jpg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4';
        $config['encrypt_name']     = FALSE;

        return $this->load->library('upload', $config);
    }

    public function validasi()
    {
        if ($this->ion_auth->is_admin()) {
            $this->form_validation->set_rules('dosen_id', 'Dosen', 'required');
        }
        // $this->form_validation->set_rules('soal', 'Soal', 'required');
        // $this->form_validation->set_rules('jawaban_a', 'Jawaban A', 'required');
        // $this->form_validation->set_rules('jawaban_b', 'Jawaban B', 'required');
        // $this->form_validation->set_rules('jawaban_c', 'Jawaban C', 'required');
        // $this->form_validation->set_rules('jawaban_d', 'Jawaban D', 'required');
        // $this->form_validation->set_rules('jawaban_e', 'Jawaban E', 'required');
        $this->form_validation->set_rules('jawaban', 'Kunci Jawaban', 'required');
        $this->form_validation->set_rules('bobot', 'Bobot Soal', 'required|max_length[10]');
    }

    public function file_config()
    {
        $allowed_type     = [
            "image/jpeg", "image/jpg", "image/png", "image/gif",
            "audio/mpeg", "audio/mpg", "audio/mpeg3", "audio/mp3", "audio/x-wav", "audio/wave", "audio/wav",
            "video/mp4", "application/octet-stream"
        ];
        $config['upload_path']      = FCPATH . 'uploads/bank_soal/';
        $config['allowed_types']    = 'jpeg|jpg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4';
        $config['encrypt_name']     = TRUE;

        return $this->load->library('upload', $config);
    }

    public function save()
    {
        $method = $this->input->post('method', true);
        $this->validasi();
        $this->file_config();


        if ($this->form_validation->run() === FALSE) {
            $method === 'add' ? $this->add() : $this->edit($this->input->post('id_soal', true));
        } else {
            $data = [
                'soal'      => $this->input->post('soal', true),
                'jawaban'   => $this->input->post('jawaban', true),
                'bobot'     => $this->input->post('bobot', true),
            ];

            $abjad = ['a', 'b', 'c', 'd', 'e'];

            // Inputan Opsi
            foreach ($abjad as $abj) {
                $data['opsi_' . $abj]    = $this->input->post('jawaban_' . $abj, true);
            }

            $i = 0;
            foreach ($_FILES as $key => $val) {
                $img_src = FCPATH . 'uploads/bank_soal/';
                $getsoal = $this->soal->getSoalById($this->input->post('id_soal', true));

                $error = '';
                if ($key === 'file_soal') {
                    if (!empty($_FILES['file_soal']['name'])) {
                        if (!$this->upload->do_upload('file_soal')) {
                            $error = $this->upload->display_errors();
                            show_error($error, 500, 'File Soal Error');
                            exit();
                        } else {
                            if ($method === 'edit') {
								if($getsoal->file!='0' && $getsoal->file!=''){
									if (unlink('uploads/bank_soal/'. $getsoal->file)) {
										show_error('Error saat delete gambarsss <br/>' . var_dump($getsoal), 500, 'Error Edit Gambar');
										exit();
									}
								}
                            }
                            $data['file'] = $this->upload->data('file_name');
                            $data['tipe_file'] = $this->upload->data('file_type');
                        }
                    }
                } else {
                    $file_abj = 'file_' . $abjad[$i];
                    if (!empty($_FILES[$file_abj]['name'])) {
                        if (!$this->upload->do_upload($key)) {
                            $error = $this->upload->display_errors();
                            show_error($error, 500, 'File Opsi ' . strtoupper($abjad[$i]) . ' Error');
                            exit();
                        } else {
                            if ($method === 'edit') {
								if($getsoal->$file_abj!='0' && $getsoal->$file_abj!=''){
									if (!unlink('uploads/bank_soal/' . $getsoal->$file_abj)) {
										show_error('Error saat delete gambar', 500, 'Error Edit Gambar');
										exit();
									}
								}

                            }
                            $data[$file_abj] = $this->upload->data('file_name');
                        }
                    }
                    $i++;
                }
            }

            if ($this->ion_auth->is_admin()) {
                $pecah = $this->input->post('dosen_id', true);
                $pecah = explode(':', $pecah);
                $data['dosen_id'] = $pecah[0];
                $data['matkul_id'] = end($pecah);
            } else {
                $data['dosen_id'] = $this->input->post('dosen_id', true);
                $data['matkul_id'] = $this->input->post('matkul_id', true);
            }

            if ($method === 'add') {
                //push array
                $data['created_on'] = time();
                $data['updated_on'] = time();
                //insert data
                $this->master->create('tb_soal', $data);
            } else if ($method === 'edit') {
                //push array
                $data['updated_on'] = time();
                //update data
                $id_soal = $this->input->post('id_soal', true);
                $this->master->update('tb_soal', $data, 'id_soal', $id_soal);
            } else {
                show_error('Method tidak diketahui', 404);
            }
            redirect('soal');
        }
    }

    public function delete()
    {
        $chk = $this->input->post('checked', true);

        // Delete File
        foreach ($chk as $id) {
            $abjad = ['a', 'b', 'c', 'd', 'e'];
            $path = FCPATH . 'uploads/bank_soal/';
            $soal = $this->soal->getSoalById($id);
            // Hapus File Soal
            if (!empty($soal->file)) {
                if (file_exists($path . $soal->file)) {
                    unlink($path . $soal->file);
                }
            }
            //Hapus File Opsi
            $i = 0; //index
            foreach ($abjad as $abj) {
                $file_opsi = 'file_' . $abj;
                if (!empty($soal->$file_opsi)) {
                    if (file_exists($path . $soal->$file_opsi)) {
                        unlink($path . $soal->$file_opsi);
                    }
                }
            }
        }

        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            if ($this->master->delete('tb_soal', $chk, 'id_soal')) {
                $this->output_json(['status' => true, 'total' => count($chk)]);
            }
        }
    }

    public function import($import_data = null)
    {

        $user = $this->ion_auth->user()->row();

        $data = [
            'user' => $this->ion_auth->user()->row(),
            'judul' => 'Soal',
            'subjudul' => 'Import Soal',
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getDosenByNip($user->username);
        }

        if ($import_data != null) $data['import'] = $import_data;
        // echo "<pre>";print_r($data);echo "</pre>";

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('soal/import');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function preview()
    {
        $config['upload_path']      = './uploads/bank_soal/';
        $config['allowed_types']    = 'xls|xlsx|csv';
        $config['max_size']         = 2048;
        $config['encrypt_name']     = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('upload_file')) {
            $error = $this->upload->display_errors();
            echo $error;
            die;
        } else {
            $file = $this->upload->data('full_path');
            $ext = $this->upload->data('file_ext');

            switch ($ext) {
                case '.xlsx':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    break;
                case '.xls':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    break;
                case '.csv':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                    break;
                default:
                    echo "unknown file ext";
                    die;
            }

            $spreadsheet = $reader->load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $data = [];
            for ($i = 1; $i < count($sheetData); $i++) {
                $data[] = [
                    'dosen_id'  => $sheetData[$i][1],
                    'matkul_id' => $sheetData[$i][2],
                    'bobot'     => $sheetData[$i][3],
                    'file'      => $sheetData[$i][4],
                    'tipe_file' => $sheetData[$i][5],
                    'soal'      => $sheetData[$i][6],
                    'opsi_a'    => $sheetData[$i][7],
                    'opsi_b'    => $sheetData[$i][8],
                    'opsi_c'    => $sheetData[$i][9],
                    'opsi_d'    => $sheetData[$i][10],
                    'opsi_e'    => $sheetData[$i][11],
                    'file_a'    => $sheetData[$i][12],
                    'file_b'    => $sheetData[$i][13],
                    'file_c'    => $sheetData[$i][14],
                    'file_d'    => $sheetData[$i][15],
                    'file_e'    => $sheetData[$i][16],
                    'jawaban'   => $sheetData[$i][17],
                    'created_on' => time(),
                    'updated_on' => time()

                ];
            }

            unlink($file);

            $this->import($data);
        }
    }

    public function do_import()
    {
        $input = json_decode($this->input->post('data'));
        $data = [];
        foreach ($input as $d) {
            $data[] = [
                'dosen_id'  => $d->dosen_id,
                'matkul_id' => $d->matkul_id,
                'bobot'     => $d->bobot,
                'file'      => $d->file,//!empty($d->file) ?? NULL,
                'tipe_file' => $d->tipe_file,//!empty($d->tipe_file) ?? NULL,
                'soal'      => $d->soal,
                'opsi_a'    => $d->opsi_a,
                'opsi_b'    => $d->opsi_b,
                'opsi_c'    => $d->opsi_c,
                'opsi_d'    => $d->opsi_d,
                'opsi_e'    => $d->opsi_e,
                'file_a'    => $d->file_a,//!empty($d->file_a) ?? NULL,
                'file_b'    => $d->file_b, //!empty($d->file_b) ?? NULL,
                'file_c'    => $d->file_c,////!empty($d->file_c) ?? NULL,
                'file_d'    => $d->file_d,//!empty($d->file_d) ?? NULL,
                'file_e'    => $d->file_e,//!empty($d->file_e) ?? NULL,
                'jawaban'   => $d->jawaban,
                'created_on' => time(),
                'updated_on' => time()
            ];
        }

        $save = $this->master->create('tb_soal', $data, true);
        if ($save) {
            redirect('soal');
        } else {
            redirect('soal/import');
        }
    }
}
