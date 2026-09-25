<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
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
		$this->form_validation->set_error_delimiters('', '');
	}

	public function is_admin()
	{
		if (!$this->ion_auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
	}

	public function output_json($data, $encode = true)
	{
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}


	public function data($id = null)
	{
		$this->is_admin();
		$this->output_json($this->users->getDataUsers($id), false);
	}

	public function index()
	{
		$this->is_admin();
		$data = [
			'user' => $this->ion_auth->user()->row(),
			'judul'	=> 'User Management',
			'subjudul' => 'Data User',
			'datakelas' => $this->master->getAllKelas()
		];

		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('users/data');
		$this->load->view('_templates/dashboard/_footer.php');
	}

	public function edit($id)
	{
		$level = $this->ion_auth->get_users_groups($id)->result();
		$data = [
			'user' 		=> $this->ion_auth->user()->row(),
			'judul'		=> 'User Management',
			'subjudul'	=> 'Edit Data User',
			'users' 	=> $this->ion_auth->user($id)->row(),
			'groups'	=> $this->ion_auth->groups()->result(),
			'level'		=> $level[0]
		];
		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('users/edit');
		$this->load->view('_templates/dashboard/_footer.php');
	}

	public function edit_info()
	{
		$this->is_admin();
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');

		if ($this->form_validation->run() === FALSE) {
			$data['status'] = false;
			$data['errors'] = [
				'username' => form_error('username'),
				'first_name' => form_error('first_name'),
				'last_name' => form_error('last_name'),
				'email' => form_error('email'),
			];
		} else {
			$id = $this->input->post('id', true);
			$input = [
				'username' 		=> $this->input->post('username', true),
				'first_name'	=> $this->input->post('first_name', true),
				'last_name'		=> $this->input->post('last_name', true),
				'email'			=> $this->input->post('email', true)
			];
			$update = $this->master->update('users', $input, 'id', $id);
			$data['status'] = $update ? true : false;
		}
		$this->output_json($data);
	}

	public function edit_status()
	{
		$this->is_admin();
		$this->form_validation->set_rules('status', 'Status', 'required');

		if ($this->form_validation->run() === FALSE) {
			$data['status'] = false;
			$data['errors'] = [
				'status' => form_error('status'),
			];
		} else {
			$id = $this->input->post('id', true);
			$input = [
				'active' 		=> $this->input->post('status', true),
			];
			$update = $this->master->update('users', $input, 'id', $id);
			$data['status'] = $update ? true : false;
		}
		$this->output_json($data);
	}

	public function edit_level()
	{
		$this->is_admin();
		$this->form_validation->set_rules('level', 'Level', 'required');

		if ($this->form_validation->run() === FALSE) {
			$data['status'] = false;
			$data['errors'] = [
				'level' => form_error('level'),
			];
		} else {
			$id = $this->input->post('id', true);
			$input = [
				'group_id' 		=> $this->input->post('level', true),
			];
			$update = $this->master->update('users_groups', $input, 'user_id', $id);
			$data['status'] = $update ? true : false;
		}
		$this->output_json($data);
	}

	public function change_password()
	{
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if ($this->form_validation->run() === FALSE) {
			$data = [
				'status' => false,
				'errors' => [
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm')
				]
			];
		} else {
			$identity = $this->session->userdata('identity');
			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));
			if ($change) {
				$data['status'] = true;
			} else {
				$data = [
					'status' 	=> false,
					'msg'		=> $this->ion_auth->errors()
				];
			}
		}
		$this->output_json($data);
	}

	public function delete($id)
	{
		$this->is_admin();
		$data['status'] = $this->ion_auth->delete_user($id) ? true : false;
		$this->output_json($data);
	}

	public function is_spp()
	{
		if (!$this->ion_auth->in_group('spp')) {
			show_error('Hanya Admin SPP yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
	}


	public function datasiswa($id = null)
	{
		$this->is_spp();
		$kelas 	= $this->input->post('idkelas');
		$status = $this->input->post('status');
		$jurusan = $this->input->post('jurusan');
		$this->output_json($this->users->getDataSpp($id,$kelas,$status,$jurusan), false);
	}

	public function listsiswa()
	{
		$this->is_spp();
		$data = [
			'user' => $this->ion_auth->user()->row(),
			'judul'	=> 'User Management',
			'subjudul' => 'Data Siswa'
		];

		$this->load->view('_templates/dashboard/_header.php', $data);
		$this->load->view('users/listsiswa');
		$this->load->view('_templates/dashboard/_footer.php');
	}

	public function ubahstatus()
	{
		$id 	= $this->input->get('id', true);
		$status = $this->input->get('status', true);

		if ($status == 1) {
			$input = [
				'active' 		=> 0,
			];
		} else {
			$input = [
				'active' 		=> 1,
			];
		}
		$update = $this->master->update('users', $input, 'id', $id);
		if ($update == true) {
			$data = [
				'status' => true,
				'msg'	 => 'Berhasil Mengapdute'
			];
		} else {
			$data = [
				'status' => false,
				'msg'	 => 'Gagal Update Status'
			];
		}

		$this->output_json($data);
	}
}
