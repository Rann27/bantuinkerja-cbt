<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SoalEssay extends CI_Controller
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
        $this->load->model('Essay_model', 'essay');
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
            'judul'    => 'Soal Essay',
            'subjudul' => 'Bank Soal Essay'
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['matkul'] = $this->master->getAllMatkul();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['matkul'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('essay/data');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function data($id = null, $dosen = null)
    {
        $this->output_json($this->essay->getSoalEssay($id, $dosen), false);
    }

    public function add()
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal Essay',
            'subjudul'  => 'Buat Soal Essay'
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('essay/add');
        $this->load->view('_templates/dashboard/_footer.php');
    }


    public function validasi()
    {
        if ($this->ion_auth->is_admin()) {
            $this->form_validation->set_rules('dosen_id', 'Dosen', 'required');
        }else{
            $this->form_validation->set_rules('dosen_id', 'Dosen', 'required');
        }
        // $this->form_validation->set_rules('jawaban', 'Kunci Jawaban', 'required');
        // $this->form_validation->set_rules('bobot', 'Bobot Soal', 'required|max_length[2]');
    }

    public function file_config()
    {
        $allowed_type     = [
            "image/jpeg", "image/jpg", "image/png", "image/gif",
            "audio/mpeg", "audio/mpg", "audio/mpeg3", "audio/mp3", "audio/x-wav", "audio/wave", "audio/wav",
            "video/mp4", "application/octet-stream"
        ];
        $config['upload_path']      = FCPATH . 'uploads/soalessay/';
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
            $method === 'add' ? $this->add() : $this->edit();
        } else {
            $data = [
                'essay_soal'      => $this->input->post('soal', true)
            ];


            $i = 0;
            foreach ($_FILES as $key => $val) {
                $img_src = FCPATH . 'uploads/soalessay/';
                $getsoal = $this->essay->getSoalById($this->input->post('id_soal_essay', true));

                $error = '';
                if ($key === 'file_soal') {
                    if (!empty($_FILES['file_soal']['name'])) {
                        if (!$this->upload->do_upload('file_soal')) {
                            $error = $this->upload->display_errors();
                            show_error($error, 500, 'File Soal Error');
                            exit();
                        } else {
                            if ($method === 'edit') {
                                if (!unlink($img_src . $getsoal->file)) {
                                    show_error('Error saat delete gambar <br/>' . var_dump($getsoal), 500, 'Error Edit Gambar');
                                    exit();
                                }
                            }
                            $data['file'] = $this->upload->data('file_name');
                            $data['tipe_file'] = $this->upload->data('file_type');
                        }
                    }
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
                $this->master->create('soal_essay', $data);
            } else if ($method === 'edit') {
                //push array
                $data['updated_on'] = time();
                //update data
                $id_soal = $this->input->post('id_soal_essay', true);
                $this->master->update('soal_essay', $data, 'id_soal_essay', $id_soal);
            } else {
                show_error('Method tidak diketahui', 404);
            }
            redirect('soalessay');
        }
    }

    public function detail($id)
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal Essay',
            'subjudul'  => 'Edit Soal Essay',
            'soal'      => $this->essay->getSoalById($id),
        ];

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('essay/detail');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function edit($id)
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'        => 'Soal Essay',
            'subjudul'  => 'Edit Soal Essay',
            'soal'      => $this->essay->getSoalById($id),
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getMatkulDosen($user->username);
        }

        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('essay/edit');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function import($import_data = null)
    {
        $user = $this->ion_auth->user()->row();
        // $jurusan = $this->master->getJurusan();
        $data = [
            'user' => $this->ion_auth->user()->row(),
            'judul' => 'Soal Essay',
            'subjudul' => 'Import Soal essay',
            // 'jurusan' => $jurusan
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getDosenByNip($user->username);
        }
        if ($import_data != null) $data['import'] = $import_data;

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('essay/import');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function preview()
    {
        $config['upload_path']      = './uploads/soalessay/';
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
                    'file'      => $sheetData[$i][3],
                    'tipe_file' => $sheetData[$i][4],
                    'essay_soal' => $sheetData[$i][5],
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
                'file_essay'      => "",
                'tipe_file_essay' => "",
                'essay_soal' => $d->essay_soal,
                'created_on' => $d->created_on,
                'updated_on' => $d->updated_on
            ];
        }

        $save = $this->master->create('soal_essay', $data, true);
        if ($save) {
            redirect('soalessay');
        } else {
            redirect('soalessay/import');
        }
    }

    public function delete()
    {
        $chk = $this->input->post('checked', true);

        // Delete File
        foreach ($chk as $id) {
            $path = FCPATH . 'uploads/soalessay/';
            $soal = $this->essay->getSoalById($id);
            // Hapus File Soal
            if (!empty($soal->file)) {
                if (file_exists($path . $soal->file_essay)) {
                    unlink($path . $soal->file_essay);
                }
            }
        }

        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            if ($this->master->delete('soal_essay', $chk, 'id_soal_essay')) {
                $this->output_json(['status' => true, 'total' => count($chk)]);
            }
        }
    }
}
