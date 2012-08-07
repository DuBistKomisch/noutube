<?php
class MY_Controller extends CI_Controller
{
  public function __construct ()
  {
    parent::__construct();

    // set include path
    set_include_path('.:./lib/');

    // take session data into global variables for views
    $vars = array();
    $username = $this->session->userdata('username');
    if ($username === FALSE)
    {
      $vars['signedin'] = false;
    }
    else
    {
      $vars['signedin'] = true;
      $vars['username'] = $username;
    }
    $this->load->vars($vars);
  }
}
?>
