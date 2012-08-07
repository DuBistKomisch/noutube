<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller
{
  public function index()
  {
    $this->load->view('header');
    if ($this->session->userdata('token') !== FALSE)
    {
      require_once('Zend/Loader.php');
      Zend_Loader::loadClass('Zend_Gdata_AuthSub');
      Zend_Loader::loadClass('Zend_Gdata_YouTube');
      $yt = new Zend_Gdata_YouTube(Zend_Gdata_AuthSub::getHttpClient($this->session->userdata('token')), $this->config->item('applicationID'), '', $this->config->item('developerKey'));

      $subscriptionFeed = $yt->getSubscriptionFeed('default');
      foreach ($subscriptionFeed as $subscriptionEntry)
        $this->load->view('subscription', array('sub' => $subscriptionEntry));
    }
    else
    {
      $this->load->view('home');
    }
    $this->load->view('footer');
  }
}
?>
