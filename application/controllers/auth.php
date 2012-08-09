<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {
  // allow a user to register an account
  public function register()
  {
    $this->load->helper(array('form'));
    $this->load->library(array('form_validation'));

    // set validation rules
    $this->form_validation->set_error_delimiters('', '');
    $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[16]|is_unique[users.username]|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
    $this->form_validation->set_rules('password_verify', 'Verify', 'trim|required|matches[password]');

    if ($this->session->userdata('username') !== FALSE)
    {
      // already signed in
      $this->load->view('header');
      $this->load->view('register_already');
      $this->load->view('footer');
    }
    else if ($this->form_validation->run() === FALSE)
    {
      // show form
      $this->load->view('header');
      $this->load->view('register_form');
      $this->load->view('footer');
    }
    else
    {
      // insert account into database
      require_once('PasswordHash.php');
      $hasher = new PasswordHash(8, FALSE);

      $record = array(
        'username' => $this->input->post('username'),
        'hash' => $hasher->HashPassword($this->input->post('password'))
      );

      $this->db->insert('users', $record);
      $this->session->set_userdata('username', $record['username']);

      redirect('home', 'refresh');
    }
  }

  // allow a registered user to sign in to a session
  public function signin()
  {
    $this->load->helper(array('form'));
    $this->load->library(array('form_validation'));

    // set validation rules
    $this->form_validation->set_error_delimiters('', '');
    $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|callback_check_credentials');

    if ($this->session->userdata('username') !== FALSE)
    {
      // already signed in
      $this->load->view('header');
      $this->load->view('signin_already');
      $this->load->view('footer');
    }
    else if ($this->form_validation->run() === FALSE)
    {
      // show form
      $this->load->view('header');
      $this->load->view('signin_form');
      $this->load->view('footer');
    }
    else
    {
      // success, store username and token in session data
      $username = $this->input->post('username');
      $this->session->set_userdata('username', $username);
      $this->db->select('token');
      $this->db->where('username', $username);
      $query = $this->db->get('users');
      $token = $query->row()->token;
      if ($token !== NULL)
        $this->session->set_userdata('token', $token);

      redirect('home', 'refresh');
    }
  }

  // validates username and password combination
  public function check_credentials($password)
  {
    require_once('PasswordHash.php');
    $hasher = new PasswordHash(8, FALSE);

    $this->db->select('hash');
    $this->db->where('username', $this->input->post('username'));
    $query = $this->db->get('users');
    if ($query->num_rows() > 0 && $hasher->CheckPassword($password, $query->row()->hash))
    {
      return TRUE;
    }
    else
    {
      $this->form_validation->set_message('check_credentials', 'Username and Password don\'t match.');
      return FALSE;
    }
  }

  // deletes the user's session
  public function signout()
  {
    $this->session->sess_destroy();
    redirect('home', 'refresh');
  }

  // for authenticating the user with google
  public function token()
  {
    // check user is signed in
    $username = $this->session->userdata('username');
    if ($username === FALSE)
    {
      redirect('auth/signin', 'refresh');
      return;
    }

    // load zend gdata library
    require_once('Zend/Loader.php');
    Zend_Loader::loadClass('Zend_Gdata_AuthSub');

    // check token
    $token = $this->input->get('token');
    if ($token === FALSE)
    {
      // redirect user to google authentication
      redirect(Zend_Gdata_AuthSub::getAuthSubTokenUri(site_url('auth/token'), 'http://gdata.youtube.com', false, true));
    }
    else
    {
      // convert one-use token to session token and store in database
      $this->session->set_userdata('token', Zend_Gdata_AuthSub::getAuthSubSessionToken($token));
      $this->db->where('username', $username);
      $this->db->update('users', array('token' => $this->session->userdata('token')));

      // redirect to home page
      redirect('home', 'refresh');
    }
  }
}
?>
