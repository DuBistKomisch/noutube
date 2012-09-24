<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {
  public function __construct()
  {
    parent::__construct();

    $this->load->model('auth_model');
  }
  // allow a user to register an account
  public function register()
  {
    $this->load->helper(array('form'));
    $this->load->library(array('form_validation'));

    // set validation rules
    $this->form_validation->set_error_delimiters('', '');
    $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[16]|callback__check_unique_username|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
    $this->form_validation->set_rules('password_verify', 'Verify', 'trim|required|matches[password]');

    if ($this->session->userdata('username') !== FALSE)
    {
      // already signed in
      $this->load->view('header', array('pageName' => 'Register'));
      $this->load->view('register_already');
      $this->load->view('footer');
    }
    else if ($this->form_validation->run() === FALSE)
    {
      // show form
      $this->load->view('header', array('pageName' => 'Register'));
      $this->load->view('register_form');
      $this->load->view('footer');
    }
    else
    {
      // insert account into database
      require_once('PasswordHash.php');
      $hasher = new PasswordHash(8, FALSE);

      $this->auth_model->insert_user($hasher->HashPassword($this->input->post('password')));
      $this->session->set_userdata('username', strtolower($this->input->post('username')));
      $this->session->set_userdata('display', $this->input->post('username'));

      redirect('home', 'refresh');
    }
  }

  // validate that a username doesn't already exist
  function _check_unique_username($username)
  {
    $this->form_validation->set_message('_check_unique_username', 'Username is already taken.');
    return !($this->auth_model->does_user_exist($username));
  }

  // allow a registered user to sign in to a session
  public function signin()
  {
    $this->load->helper(array('form'));
    $this->load->library(array('form_validation'));

    // set validation rules
    $this->form_validation->set_error_delimiters('', '');
    $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|callback__check_credentials');

    if ($this->session->userdata('username') !== FALSE)
    {
      // already signed in
      $this->load->view('header', array('pageName' => 'Sign In'));
      $this->load->view('signin_already');
      $this->load->view('footer');
    }
    else if ($this->form_validation->run() === FALSE)
    {
      // show form
      $this->load->view('header', array('pageName' => 'Sign In'));
      $this->load->view('signin_form');
      $this->load->view('footer');
    }
    else
    {
      // success, store username and token in session data
      $this->session->set_userdata('username', strtolower($this->input->post('username')));
      $this->session->set_userdata('display', $this->auth_model->get_user_display());
      $token = $this->auth_model->get_user_token();
      if ($token !== NULL)
        $this->session->set_userdata('token', $token);

      redirect('home', 'refresh');
    }
  }

  // validates username and password combination
  function _check_credentials($password)
  {
    require_once('PasswordHash.php');
    $hasher = new PasswordHash(8, FALSE);

    $hash = $this->auth_model->get_user_hash();
    if ($hash !== NULL && $hasher->CheckPassword($password, $hash))
    {
      return TRUE;
    }
    else
    {
      $this->form_validation->set_message('_check_credentials', 'Username and Password don\'t match.');
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
      $this->auth_model->set_user_token();

      // redirect to update subscriptions
      redirect('videos/update', 'refresh');
    }
  }
}
?>
