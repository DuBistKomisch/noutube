<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home  extends CI_Controller {
  public function index()
  {
    $this->load->view('header');
    $this->load->view('home');
    $this->load->view('footer');

    /*require_once('Zend/Loader.php');
    Zend_Loader::loadClass('Zend_Gdata_YouTube');
    Zend_Loader::loadClass('Zend_Gdata_AuthSub');
    $yt = new Zend_Gdata_YouTube();*/

    /*$authsub = Zend_Gdata_AuthSub::getAuthSubTokenUri('http://dubistkomisch.co.cc/test', 'http://gdata.youtube.com', false, true);
    header('Location: ' . $authsub);*/
  }
}
