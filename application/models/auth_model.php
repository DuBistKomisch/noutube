<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {
  public function insert_user ($hash)
  {
    $this->db->insert('user', array(
      'username' => $this->input->post('username'),
      'hash' => $hash
    ));
  }

  public function get_user_token ()
  {
    $this->db->select('token');
    $this->db->where('username', $this->input->post('username'));
    $query = $this->db->get('user');
    return $query->row()->token;
  }

  public function get_user_hash ()
  {
    $this->db->select('hash');
    $this->db->where('username', $this->input->post('username'));
    $query = $this->db->get('user');
    return $query->row()->hash;
  }

  public function set_user_token ()
  {
    $this->db->where('username', $this->session->userdata('username'));
    $this->db->update('user', array('token' => $this->session->userdata('token')));
  }
}
