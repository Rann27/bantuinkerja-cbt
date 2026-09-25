<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kartu extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->model('Users_model', 'users');
        $this->load->model('Master_model', 'master');
        // $this->load->library('Fpdf');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function is_admin()
    {
        if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
    }


    public function kartupeserta()
    {
        $this->load->library('Pdf');
        $id_kelas     = $this->input->post('id_kelas');
        $dataku     = $this->master->getMahasiswaByKelas($id_kelas);
        $namafile     = 'Kartu Peserta Ujian Kelas ' . $dataku[0]->nama_kelas . ' ' . $dataku[0]->nama_jurusan;

        $pdf = new TCPDF('P', 'mm', 'Letter', true);
        $pdf->SetCreator('SMK PGRI 2 KEDIRI');
        $pdf->SetAuthor('Admin SMK PGRI 2 KEDIRI');
        $pdf->SetTitle($namafile);
        $pdf->setPrintHeader(false);
        // Add a page
        $pdf->AddPage();
        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Set card dimensions and spacing
        $cardWidth = 90; // Adjust as needed
        $cardHeight = 40; // Adjust as needed
        $horizontalSpacing = 5; // Adjust as needed
        $verticalSpacing = 6; // Adjust as needed

        // Calculate number of rows and columns
        $rows   = 8;
        $columns = 2;

        // Calculate available width and height for cards
        $availableWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'] - ($columns - 1) * $horizontalSpacing;
        $availableHeight = $pdf->getPageHeight() - $pdf->getMargins()['top'] - $pdf->getMargins()['bottom'] - ($rows - 1) * $verticalSpacing;

        

        // Initialize variables for positioning
        $currentX = $pdf->getMargins()['left'];
        $currentY = $pdf->getMargins()['top'];

        // Generate cards
        for ($i = 0; $i < count($dataku); $i++) {

            // Check if adding the next card would exceed the available height
            if ($currentY + $cardHeight > $availableHeight + $pdf->getMargins()['top']) {
                // Add a new page
                $pdf->AddPage();
                // Reset position for the new page
                $currentX = $pdf->getMargins()['left'];
                $currentY = $pdf->getMargins()['top'];
            }

            $cardHtml = '<div style="width: 90mm; height:30mm; border: 1px solid black;">';
            $cardHtml .='<h4 style="border-bottom: 1px solid black;text-align:center;padding:10px 4px 10px 4px">SMK PGRI 2 Kediri</h4>';
            $cardHtml .= '<table style="border-spacing:3px 3px;">
            <tr>
              <td style="text-align:left">Nama Siswa</td>
              <td> : '.$dataku[$i]->nama.'</td>
            </tr>
            <tr>
              <td style="text-align:left">Username</td>
              <td> : '.$dataku[$i]->email.'</td>
            </tr>
            <tr>
              <td style="text-align:left">Password</td>
              <td> : '.$dataku[$i]->nim.'</td>
            </tr>
            <tr>
              <td style="text-align:left">Grup</td>
              <td> : '.$dataku[$i]->nama_kelas . ' ' . $dataku[$i]->nama_jurusan.'</td>
            </tr>
            </table>';
            $cardHtml .= '</div>';

            // Add card HTML
            $pdf->writeHTMLCell($cardWidth, $cardHeight, $currentX, $currentY, $cardHtml, 0, 0, false, true, 'L');

            // Update current position
            $currentX += $cardWidth + $horizontalSpacing;
            if ($i % $columns == $columns - 1) {
                $currentX = $pdf->getMargins()['left'];
                $currentY += $cardHeight + $verticalSpacing;
            }
        }
        $pdf->Output($namafile . '.pdf');
    }
}
