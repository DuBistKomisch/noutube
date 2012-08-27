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

    require_once('Zend/Loader.php');
    Zend_Loader::loadClass('Zend_Gdata_AuthSub');
    Zend_Loader::loadClass('Zend_Gdata_YouTube');
    if ($this->session->userdata('token') !== FALSE)
      $this->yt = new Zend_Gdata_YouTube(Zend_Gdata_AuthSub::getHttpClient($this->session->userdata('token')), $this->config->item('applicationID'), NULL, $this->config->item('developerKey'));
    else
      $this->yt = new Zend_Gdata_YouTube();
    $this->yt->setMajorProtocolVersion(2);
  }
}
?>
