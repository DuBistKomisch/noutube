<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {
  public function does_user_exist ($username)
  {
    $this->db->where('username', $username);
    $query = $this->db->get('user');
    return $query->num_rows() > 0;
  }

  public function insert_user ($hash)
  {
    $this->db->insert('user', array(
      'username' => strtolower($this->input->post('username')),
      'hash' => $hash,
      'display' => $this->input->post('username')
    ));
  }

  public function get_user_display ()
  {
    $this->db->select('display');
    $this->db->where('username', strtolower($this->input->post('username')));
    $query = $this->db->get('user');
    return $query->row()->display;
  }

  public function get_user_token ()
  {
    $this->db->select('token');
    $this->db->where('username', strtolower($this->input->post('username')));
    $query = $this->db->get('user');
    return $query->row()->token;
  }

  public function get_user_hash ()
  {
    $this->db->select('hash');
    $this->db->where('username', strtolower($this->input->post('username')));
    $query = $this->db->get('user');
    return $query->num_rows() > 0 ? $query->row()->hash : NULL;
  }

  public function set_user_token ()
  {
    $this->db->where('username', $this->session->userdata('username'));
    $this->db->update('user', array('token' => $this->session->userdata('token')));
  }
}
