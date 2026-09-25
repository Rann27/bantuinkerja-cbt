<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{

    public function getDatausers($id = null)
    {
        $this->datatables->select('users.id, username, first_name, last_name, email, FROM_UNIXTIME(created_on) as created_on, last_login, active, groups.name as level');
        $this->datatables->from('users_groups');
        $this->datatables->join('users', 'users_groups.user_id=users.id');
        $this->datatables->join('groups', 'users_groups.group_id=groups.id');
        if ($id !== null) {
            $this->datatables->where('users.id !=', $id);
        }
        return $this->datatables->generate();
    }

    public function getDataSpp($id = null, $kelas = null, $status = null,$jurusan)
    {
        $this->datatables->select('users.id, a.nim, a.nama,b.nama_kelas,c.nama_jurusan, users.email, users.last_login, users.active');
        $this->datatables->from('users_groups');
        $this->datatables->join('users', 'users_groups.user_id=users.id');
        $this->datatables->join('mahasiswa a', 'users.username=a.nim');
        $this->datatables->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->datatables->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        if ($id !== null) {
            $this->datatables->where('users.id !=', $id);
        }
        if ($kelas !== null) {
            $this->datatables->where('b.code_kelas', $kelas);
        }
        if ($status !== null) {
            $this->datatables->where('users.active', $status);
        }
        if ($jurusan !== null) {
            $this->datatables->where('b.jurusan_id', $jurusan);
        }

        return $this->datatables->generate();
    }
}
