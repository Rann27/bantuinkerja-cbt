<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
	/**
	 * Kompatibilitas CI 3.1: variabel yang dikirim ke view di-cache sehingga
	 * tetap tersedia di view berikutnya. Controller di aplikasi ini hanya
	 * mengirim $data ke view header, lalu memanggil view konten & footer tanpa data.
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		$this->vars($vars);
		return parent::view($view, array(), $return);
	}
}
