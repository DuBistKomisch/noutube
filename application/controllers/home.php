<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller
{
  // direct user
  public function index()
  {
    $this->load->view('header');
    if ($this->session->userdata('token') !== FALSE)
    {
      // fully authenticated
      redirect('videos', 'refresh');
    }
    else if ($this->session->userdata('username') !== FALSE)
    {
      // need token
      redirect('auth/token', 'refresh');
    }
    else
    {
      // not signed in
      $this->load->view('welcome');
    }
    $this->load->view('footer');
  }

  // displays privacy statement
  public function privacy()
  {
    $this->load->view('header');
    $this->load->view('privacy');
    $this->load->view('footer');
  }

  public function error404()
  {
    $this->load->view('header');
    $this->load->view('404');
    $this->load->view('footer');
  }
}
?>
