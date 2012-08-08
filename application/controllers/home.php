<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller
{
  public function index()
  {
    $this->load->view('header');
    if ($this->session->userdata('token') !== FALSE)
    {
      $subscriptionFeed = $this->yt->getSubscriptionFeed('default');
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
