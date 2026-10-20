<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper import soal dari Excel (xlsx/xls/csv), opsional dibungkus ZIP bersama file media.
 */

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Ekstensi media yang bisa ditampilkan oleh tampil_media()
const IMPORT_EKS_MEDIA = [
	'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'bmp' => 'image/bmp',
	'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'mp4' => 'video/mp4',
];
const IMPORT_EKS_EXCEL = ['xlsx', 'xls', 'csv'];
const IMPORT_MAKS_FILE_ZIP = 20 * 1024 * 1024;   // per file di dalam ZIP
const IMPORT_MAKS_TOTAL_ZIP = 200 * 1024 * 1024; // total isi ZIP setelah diekstrak

/**
 * Ubah isi sel Excel menjadi HTML aman: karakter < > & di-escape, baris baru (Alt+Enter)
 * menjadi <br>, dan format tebal/miring/garis bawah/coret/subscript/superscript dipertahankan.
 */
function sel_ke_html(?Cell $cell)
{
	if ($cell === null || $cell->getValue() === null || $cell->getValue() === '') {
		return null;
	}

	$nilai = $cell->getValue();
	$html = '';
	if ($nilai instanceof RichText) {
		foreach ($nilai->getRichTextElements() as $el) {
			$font = $el instanceof Run ? $el->getFont() : $cell->getStyle()->getFont();
			$html .= teks_berformat_html($el->getText(), $font);
		}
	} else {
		$html = teks_berformat_html(sel_ke_teks($cell), $cell->getStyle()->getFont());
	}

	return trim(strip_tags($html)) === '' ? null : trim($html);
}

/**
 * Nilai sel sebagai teks biasa (hasil rumus & format angka sudah diterapkan).
 */
function sel_ke_teks(?Cell $cell)
{
	if ($cell === null || $cell->getValue() === null) {
		return '';
	}
	try {
		return trim((string) $cell->getFormattedValue());
	} catch (Exception $e) {
		return trim((string) $cell->getValue());
	}
}

function teks_berformat_html($teks, $font = null)
{
	$html = nl2br(htmlspecialchars(str_replace("\r\n", "\n", $teks), ENT_QUOTES, 'UTF-8'), false);
	if ($font === null || trim($teks) === '') {
		return $html;
	}
	if ($font->getSuperscript()) $html = "<sup>$html</sup>";
	if ($font->getSubscript())   $html = "<sub>$html</sub>";
	if ($font->getBold())        $html = "<b>$html</b>";
	if ($font->getItalic())      $html = "<i>$html</i>";
	if ($font->getUnderline() && $font->getUnderline() !== 'none') $html = "<u>$html</u>";
	if ($font->getStrikethrough()) $html = "<s>$html</s>";
	return $html;
}

/**
 * Baca baris data (mulai baris 2) dari sheet pertama.
 * $kolom: [nama_field => [indeks_kolom (0 = kolom A), 'html'|'teks']]
 * Baris yang seluruh kolomnya kosong dilewati.
 */
function baca_baris_excel(Worksheet $sheet, array $kolom)
{
	$baris = [];
	$maks = $sheet->getHighestDataRow();
	for ($r = 2; $r <= $maks; $r++) {
		$row = [];
		$kosong = true;
		foreach ($kolom as $field => list($indeks, $jenis)) {
			$cell = $sheet->getCellByColumnAndRow($indeks + 1, $r, false);
			$row[$field] = $jenis === 'html' ? sel_ke_html($cell) : sel_ke_teks($cell);
			if ($row[$field] !== null && $row[$field] !== '') {
				$kosong = false;
			}
		}
		if (!$kosong) {
			$row['_baris'] = $r;
			$baris[] = $row;
		}
	}
	return $baris;
}

function buka_spreadsheet($path)
{
	$eks = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	switch ($eks) {
		case 'xlsx': $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); break;
		case 'xls':  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); break;
		case 'csv':  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv(); break;
		default: throw new RuntimeException('Format file tidak dikenal: .' . $eks);
	}
	// Selalu sheet pertama, walau file disimpan saat sheet "Petunjuk" sedang aktif
	return $reader->load($path)->getSheet(0);
}

/**
 * Ekstrak ZIP import ke folder staging. File media diberi nama acak baru agar tidak
 * bentrok dengan file lain di server. Nama folder di dalam ZIP diabaikan (hanya nama file).
 *
 * @return array ['excel' => path|null, 'media' => [nama_asli_lowercase => nama_baru], 'error' => [...]]
 */
function ekstrak_zip_import($zip_path, $folder_staging)
{
	$hasil = ['excel' => null, 'media' => [], 'error' => []];
	$zip = new ZipArchive();
	if ($zip->open($zip_path) !== true) {
		$hasil['error'][] = 'File ZIP tidak bisa dibuka / rusak.';
		return $hasil;
	}

	$total = 0;
	$asal = [];
	for ($i = 0; $i < $zip->numFiles; $i++) {
		$stat = $zip->statIndex($i);
		$nama_lengkap = str_replace('\\', '/', $stat['name']);
		$nama = basename($nama_lengkap);
		// Lewati folder & file sistem (macOS/Windows)
		if (substr($nama_lengkap, -1) === '/' || $nama === '' || $nama[0] === '.'
			|| strpos($nama_lengkap, '__MACOSX/') !== false || strcasecmp($nama, 'Thumbs.db') === 0) {
			continue;
		}
		$eks = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
		$is_excel = in_array($eks, IMPORT_EKS_EXCEL, true);
		if (!$is_excel && !isset(IMPORT_EKS_MEDIA[$eks])) {
			$hasil['error'][] = "File \"$nama_lengkap\" dilewati: format tidak didukung.";
			continue;
		}
		if ($stat['size'] > IMPORT_MAKS_FILE_ZIP) {
			$hasil['error'][] = "File \"$nama_lengkap\" terlalu besar (maks " . (IMPORT_MAKS_FILE_ZIP / 1048576) . ' MB).';
			continue;
		}
		$total += $stat['size'];
		if ($total > IMPORT_MAKS_TOTAL_ZIP) {
			$hasil['error'][] = 'Isi ZIP terlalu besar (maks ' . (IMPORT_MAKS_TOTAL_ZIP / 1048576) . ' MB).';
			break;
		}

		if ($is_excel) {
			if ($hasil['excel'] !== null) {
				$hasil['error'][] = 'ZIP berisi lebih dari satu file Excel. Sisakan satu saja.';
				continue;
			}
			$tujuan = $folder_staging . 'soal.' . $eks;
			file_put_contents($tujuan, $zip->getFromIndex($i));
			$hasil['excel'] = $tujuan;
			continue;
		}

		$kunci = strtolower($nama);
		if (isset($hasil['media'][$kunci])) {
			$hasil['error'][] = "Nama file \"$nama\" ganda (\"{$asal[$kunci]}\" dan \"$nama_lengkap\"). Ganti salah satu namanya.";
			continue;
		}
		$nama_baru = bin2hex(random_bytes(16)) . '.' . $eks;
		file_put_contents($folder_staging . $nama_baru, $zip->getFromIndex($i));
		$hasil['media'][$kunci] = $nama_baru;
		$asal[$kunci] = $nama_lengkap;
	}
	$zip->close();

	if ($hasil['excel'] === null) {
		$hasil['error'][] = 'Tidak ada file Excel (.xlsx/.xls/.csv) di dalam ZIP.';
	}
	return $hasil;
}

function tipe_media($nama_file)
{
	$eks = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
	return isset(IMPORT_EKS_MEDIA[$eks]) ? IMPORT_EKS_MEDIA[$eks] : '';
}

/**
 * Hapus folder staging import yang lebih tua dari $umur detik (preview yang tidak dilanjutkan).
 */
function bersihkan_staging_import($folder_induk, $umur = 86400)
{
	foreach (glob($folder_induk . '*', GLOB_ONLYDIR) ?: [] as $dir) {
		if (filemtime($dir) < time() - $umur) {
			hapus_folder($dir);
		}
	}
}

function hapus_folder($dir)
{
	if (!is_dir($dir)) {
		return;
	}
	foreach (glob(rtrim($dir, '/\\') . '/*') ?: [] as $f) {
		is_dir($f) ? hapus_folder($f) : unlink($f);
	}
	rmdir($dir);
}
