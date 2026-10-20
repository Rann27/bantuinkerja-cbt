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
	
    /**
     * Upload banyak gambar/audio sekaligus ke bank soal. Nama file dipertahankan (spasi jadi "_",
     * ekstensi huruf kecil); bila sudah ada file bernama sama, sistem menambah angka di belakangnya.
     * Nama final ditampilkan agar guru tahu nama yang harus ditulis di kolom File pada Excel.
     */
    public function upload_image()
    {
        $this->file_config2();
        $hasil = [];
        foreach (array_keys($_FILES) as $field) {
            if (strpos($field, 'filefoto') !== 0 || empty($_FILES[$field]['name'])) {
                continue;
            }
            if ($this->upload->do_upload($field)) {
                $hasil[] = ['asal' => $_FILES[$field]['name'], 'nama' => $this->upload->data('file_name'), 'error' => ''];
            } else {
                $hasil[] = ['asal' => $_FILES[$field]['name'], 'nama' => '', 'error' => strip_tags($this->upload->display_errors())];
            }
            $this->upload->error_msg = [];
        }
        $this->uploadgambar($hasil);
    }

    public function uploadgambar($hasil = null)
    {
            $user = $this->ion_auth->user()->row();
            $data = [
                'user'      => $user,
                'judul'     => 'Soal',
                'subjudul'  => 'Upload Gambar Soal dan Jawaban',
                'hasil'     => is_array($hasil) ? $hasil : null,
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
        $config['allowed_types']    = 'jpeg|jpg|png|gif|bmp|mpeg|mpg|mpeg3|mp3|wav|wave|mp4';
        $config['encrypt_name']     = FALSE;
        $config['file_ext_tolower'] = TRUE;

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
    public function import($import_data = null, $pesan = [])
    {

        $user = $this->ion_auth->user()->row();

        $data = [
            'user' => $this->ion_auth->user()->row(),
            'judul' => 'Soal',
            'subjudul' => 'Import Soal',
            'pesan' => is_array($pesan) ? $pesan : [],
        ];

        if ($this->ion_auth->is_admin()) {
            //Jika admin maka tampilkan semua matkul
            $data['dosen'] = $this->soal->getAllDosen();
        } else {
            //Jika bukan maka matkul dipilih otomatis sesuai matkul dosen
            $data['dosen'] = $this->soal->getDosenByNip($user->username);
        }

        if (is_array($import_data)) $data['import'] = $import_data;

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('soal/import');
        $this->load->view('_templates/dashboard/_footer');
    }

    // Kolom template import soal (indeks 0 = kolom A). Kolom F "Tipe File" diisi otomatis dari ekstensi.
    private $kolom_import = [
        'dosen_id'  => [1, 'teks'],
        'matkul_id' => [2, 'teks'],
        'bobot'     => [3, 'teks'],
        'file'      => [4, 'teks'],
        'soal'      => [6, 'html'],
        'opsi_a'    => [7, 'html'],
        'opsi_b'    => [8, 'html'],
        'opsi_c'    => [9, 'html'],
        'opsi_d'    => [10, 'html'],
        'opsi_e'    => [11, 'html'],
        'file_a'    => [12, 'teks'],
        'file_b'    => [13, 'teks'],
        'file_c'    => [14, 'teks'],
        'file_d'    => [15, 'teks'],
        'file_e'    => [16, 'teks'],
        'jawaban'   => [17, 'teks'],
    ];

    private $kolom_file = ['file', 'file_a', 'file_b', 'file_c', 'file_d', 'file_e'];

    /**
     * Preview import dari .xlsx/.xls/.csv, atau .zip berisi file Excel + gambar/audio/video.
     * File media disimpan sementara di uploads/import/tmp/<token>/ dengan nama acak baru,
     * lalu baru dipindah ke bank soal saat import dikonfirmasi (do_import).
     */
    public function preview()
    {
        $this->load->helper('import');

        $folder_induk = FCPATH . 'uploads/import/tmp/';
        if (!is_dir($folder_induk)) {
            mkdir($folder_induk, 0755, true);
        }
        bersihkan_staging_import($folder_induk);

        $token   = bin2hex(random_bytes(8));
        $staging = $folder_induk . $token . '/';
        mkdir($staging, 0755);

        $config['upload_path']   = $staging;
        $config['allowed_types'] = 'xls|xlsx|csv|zip';
        $config['max_size']      = 51200;
        $config['file_name']     = 'unggahan';
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('upload_file')) {
            hapus_folder($staging);
            return $this->import(null, ['error' => [strip_tags($this->upload->display_errors())]]);
        }

        $file  = $this->upload->data('full_path');
        $media = [];
        $peringatan = [];
        $is_zip = strtolower($this->upload->data('file_ext')) === '.zip';
        if ($is_zip) {
            $zip = ekstrak_zip_import($file, $staging);
            unlink($file);
            $excel = $zip['excel'];
            $media = $zip['media'];
            $peringatan = $zip['error'];
        } else {
            $excel = $file;
        }

        try {
            if ($excel === null) {
                throw new RuntimeException(implode(' ', $peringatan));
            }
            $rows = baca_baris_excel(buka_spreadsheet($excel), $this->kolom_import);
        } catch (Exception $e) {
            hapus_folder($staging);
            return $this->import(null, ['error' => ['File tidak bisa dibaca. ' . $e->getMessage()]]);
        }
        unlink($excel);

        $dipakai = [];
        foreach ($rows as &$row) {
            $row['error'] = [];
            $row['jawaban'] = strtoupper($row['jawaban']);
            if (!in_array($row['jawaban'], ['A', 'B', 'C', 'D', 'E'], true)) {
                $row['error'][] = 'Jawaban harus salah satu dari A, B, C, D, E.';
            }
            if ($row['soal'] === null && $row['file'] === '') {
                $row['error'][] = 'Soal kosong.';
            }

            foreach ($this->kolom_file as $f) {
                $nama = $row[$f];
                if ($nama === '') {
                    continue;
                }
                $kunci = strtolower(basename(str_replace('\\', '/', $nama)));
                if (isset($media[$kunci])) {
                    // File dari ZIP, sudah di-rename acak agar tidak bentrok
                    $row[$f] = $media[$kunci];
                    $dipakai[$media[$kunci]] = true;
                } elseif (is_file(FCPATH . 'uploads/bank_soal/' . basename($nama))) {
                    // File yang sudah ada di server (diunggah lewat menu Gambar)
                    $row[$f] = basename($nama);
                } else {
                    $row['error'][] = 'File "' . $nama . '" tidak ditemukan' . ($is_zip ? ' di dalam ZIP. Pastikan nama file sama persis dan filenya ikut dimasukkan ke ZIP.' : ' di server. Unggah lewat menu Gambar, atau import sebagai ZIP bersama gambarnya.');
                }
            }
            $row['tipe_file'] = tipe_media($row['file']);
        }
        unset($row);

        // Media di ZIP yang tidak dipakai soal mana pun tidak perlu disimpan
        foreach ($media as $nama_baru) {
            if (!isset($dipakai[$nama_baru])) {
                unlink($staging . $nama_baru);
            }
        }

        $this->session->set_userdata('import_soal', ['token' => $token, 'files' => array_keys($dipakai)]);
        $this->import($rows, [
            'peringatan' => $peringatan,
            'staging' => 'uploads/import/tmp/' . $token . '/',
            'staging_files' => $dipakai,
        ]);
    }

    public function do_import()
    {
        $this->load->helper('import');

        $input = json_decode($this->input->post('data'));
        if (empty($input)) {
            redirect('soal/import');
        }

        // File media hasil preview ZIP yang boleh dipindahkan ke bank soal
        $sesi = $this->session->userdata('import_soal');
        $staging = $sesi ? FCPATH . 'uploads/import/tmp/' . $sesi['token'] . '/' : null;
        $boleh_pindah = $sesi ? array_flip($sesi['files']) : [];

        $data = [];
        foreach ($input as $d) {
            $row = [
                'dosen_id'  => $d->dosen_id,
                'matkul_id' => $d->matkul_id,
                'bobot'     => $d->bobot,
                'soal'      => $d->soal,
                'opsi_a'    => $d->opsi_a,
                'opsi_b'    => $d->opsi_b,
                'opsi_c'    => $d->opsi_c,
                'opsi_d'    => $d->opsi_d,
                'opsi_e'    => $d->opsi_e,
                'jawaban'   => strtoupper($d->jawaban),
                'created_on' => time(),
                'updated_on' => time()
            ];
            foreach ($this->kolom_file as $f) {
                $nama = isset($d->$f) ? basename((string) $d->$f) : '';
                if ($nama !== '' && isset($boleh_pindah[$nama]) && is_file($staging . $nama)) {
                    rename($staging . $nama, FCPATH . 'uploads/bank_soal/' . $nama);
                }
                $row[$f] = $nama;
            }
            $row['tipe_file'] = tipe_media($row['file']);
            $data[] = $row;
        }

        $save = $this->master->create('tb_soal', $data, true);

        if ($staging) {
            hapus_folder($staging);
        }
        $this->session->unset_userdata('import_soal');

        if ($save) {
            redirect('soal');
        } else {
            redirect('soal/import');
        }
    }
}
