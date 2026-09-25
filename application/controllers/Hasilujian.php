<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HasilUjian extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		}

		$this->load->library(['datatables']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Ujian_model', 'ujian');

		$this->user = $this->ion_auth->user()->row();
	}



	public function output_json($data, $encode = true)
	{
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function data()
	{
		$nip_dosen = null;

		if ($this->ion_auth->in_group('dosen')) {
			$nip_dosen = $this->user->username;
		}

		$this->output_json($this->ujian->getHasilUjian($nip_dosen), false);
	}

	public function NilaiMhs($id)
	{
		$this->output_json($this->ujian->HslUjianById($id, true), false);
	}

	public function index()
	{
		$data = [
			'user' => $this->user,
			'judul'	=> 'Ujian',
			'subjudul' => 'Hasil Ujian',
		];
		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('ujian/hasil');
		$this->load->view('_templates/dashboard/_footer.php');
	}

	public function detail($id)
	{
		$ujian  = $this->ujian->getUjianById($id);
		$nilai  = $this->ujian->bandingNilai($id);
		$tidak_ikut = count($this->ujian->carisiswa($id));

		$data = [
			'user' => $this->user,
			'judul'	=> 'Ujian',
			'subjudul' => 'Detail Hasil Ujian',
			'ujian'	=> $ujian,
			'nilai'	=> $nilai,
			'tidakikut' => $tidak_ikut
		];

		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('ujian/detail_hasil');
		$this->load->view('_templates/dashboard/_footer.php');
	}

	public function cetak($id)
	{
		$this->load->library('Pdf');

		$mhs 	= $this->ujian->getIdMahasiswa($this->user->username);
		$hasil 	= $this->ujian->HslUjian($id, $mhs->id_mahasiswa)->row();
		$ujian 	= $this->ujian->getUjianById($id);

		$data = [
			'ujian' => $ujian,
			'hasil' => $hasil,
			'mhs'	=> $mhs
		];
		redirect('ujian/list');

		//$this->load->view('ujian/cetak', $data);
	}

	public function cetak_detail($id)
	{
		$this->load->library('Pdf');

		$ujian = $this->ujian->getUjianById($id);
		$nilai = $this->ujian->bandingNilai($id);
		$hasil = $this->ujian->HslUjianByIdPdf($id)->result();

		$data = [
			'ujian'	=> $ujian,
			'nilai'	=> $nilai,
			'hasil'	=> $hasil
		];

		$this->load->view('ujian/cetak_detail', $data);
	}

	public function cetakexcel($id)
	{
		$ujian = $this->ujian->getUjianById($id);
		$nilai = $this->ujian->bandingNilai($id);
		$hasil = $this->ujian->HslUjianByIdPdf($id)->result();

		$spreadsheet = new Spreadsheet(); // instantiate Spreadsheet

		$sheet = $spreadsheet->getActiveSheet();

		$sheet->mergeCells('B2:H3');
		$sheet->setCellValue('B2','Hasil Ujian '.$ujian->nama_ujian);
		$sheet->getStyle('B2')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('B5:H5')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('E:H')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('B')->getAlignment()->setHorizontal('center');

		//Setup untuk headernya saja
		$sheet->setCellValue('B5', 'NO');
		$sheet->setCellValue('C5', "NIS"); // Set kolom B1 dengan tulisan "NIS"
		$sheet->setCellValue('D5', "NAMA"); // Set kolom C1 dengan tulisan "NAMA"
		$sheet->setCellValue('E5', "KELAS"); // Set kolom D1 dengan tulisan "JENIS KELAMIN"
		$sheet->setCellValue('F5', "JURUSAN"); // Set kolom E1 dengan tulisan "ALAMAT"
		$sheet->setCellValue('G5', "BENAR"); // Set kolom F1 dengan tulisan "ALAMAT"
		$sheet->setCellValue('H5', "NILAI"); // Set kolom G1 dengan tulisan "ALAMAT"


		$no 	= 1; // Untuk penomoran tabel, di awal set dengan 1
		$numrow = 6;
		foreach ($hasil as $data) {
			$sheet->setCellValue('B'.$numrow, $no);
			$sheet->setCellValue('C'.$numrow, $data->nim);
			$sheet->setCellValue('D'.$numrow, $data->nama);
			$sheet->setCellValue('E'.$numrow, $data->nama_kelas);
			$sheet->setCellValue('F'.$numrow, $data->nama_jurusan);
			$sheet->setCellValue('G'.$numrow, $data->jml_benar);
			$sheet->setCellValue('H'.$numrow, $data->nilai);
			$numrow++;	  
			$no++;
		}
		

		$writer = new Xlsx($spreadsheet); // instantiate Xlsx


		header('Content-Type: application/vnd.ms-excel'); // generate excel file
		header('Content-Disposition: attachment;filename="' . $ujian->nama_ujian . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');	// download file 
	}

	public function resetujian()
	{
		$chk = $this->input->get('siswa');
		$ujian = $this->input->get('ujian');
		if (!$chk) {
			$this->output_json(['status' => false]);
		} else {
			$array = ['mahasiswa_id' => $chk, 'ujian_id' => $ujian];
			if ($this->ujian->deleteUjian('h_ujian', $array)) {
				$this->output_json(['status' => true, 'msg' => "Sudah Update"]);
			}
		}
	}

	public function cektidakikut()
	{
		$id	  	= $this->input->get('idujian');
		$data 	= $this->ujian->carisiswa($id);
		$ujian  = $this->ujian->getUjianById($id);

		$data = [
			'user' => $this->user,
			'judul'	=> 'Ujian',
			'subjudul' => 'List Siswa Tidak Ikut Ujian',
			'ujian'	=> $ujian,
			'data'	=> $data
		];

		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('ujian/tidak_ikut');
		$this->load->view('_templates/dashboard/_footer.php');
	}
}
