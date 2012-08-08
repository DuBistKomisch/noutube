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
    $vars['applicationName'] = $this->config->item('applicationName');
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

    if ($this->session->userdata('token') !== FALSE)
    {
      require_once('Zend/Loader.php');
      Zend_Loader::loadClass('Zend_Gdata_AuthSub');
      Zend_Loader::loadClass('Zend_Gdata_YouTube');
      $this->yt = new Zend_Gdata_YouTube(Zend_Gdata_AuthSub::getHttpClient($this->session->userdata('token')), $this->config->item('applicationID'), '', $this->config->item('developerKey'));
      $this->yt->setMajorProtocolVersion(2);
    }
  }
}
?>
