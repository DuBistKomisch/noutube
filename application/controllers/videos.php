
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Videos extends MY_Controller
{
  public function index()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
      redirect('home', 'refresh');

    // fetch data
    $this->db->select('display, thumbnail, checked, new, later');
    $this->db->join('subscribed', 'username=subscription');
    $this->db->where('user', $this->session->userdata('username'));
    $this->db->where('new >', '0');
    $results_new = $this->db->get('subscriptions');
    $this->db->select('display, thumbnail, checked, new, later');
    $this->db->join('subscribed', 'username=subscription');
    $this->db->where('user', $this->session->userdata('username'));
    $this->db->where('later >', '0');
    $results_later = $this->db->get('subscriptions');

    // show subscriptions
    $this->load->view('header');
    $this->load->view('videos/videos');
    $this->load->view('videos/new');

    if ($results_new->num_rows() == 0)
      $this->load->view('videos/new_none');
    else foreach ($results_new->result_array() as $row)
      $this->load->view('videos/summary', $row);

    $this->load->view('videos/later');
    if ($results_later->num_rows() == 0)
      $this->load->view('videos/later_none');
    foreach ($results_later->result_array() as $row)
      $this->load->view('videos/summary', $row);

    $this->load->view('footer');
  }

  public function all()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
      redirect('home', 'refresh');

    // fetch data
    $this->db->select('display, thumbnail, checked, new, later');
    $this->db->join('subscribed', 'username=subscription');
    $this->db->where('user', $this->session->userdata('username'));
    $results = $this->db->get('subscriptions');

    // show subscriptions
    $this->load->view('header');
    $this->load->view('videos/videos');

    foreach ($results->result_array() as $row)
      $this->load->view('videos/summary', $row);

    $this->load->view('footer');
  }

  // fetches list of user's subscriptions and updates the database accordingly
  public function update()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
      redirect('home', 'refresh');

    // counters
    $added = 0;
    $removed = 0;

    // check what we think the user is subscribed to
    $this->db->select('subscription');
    $this->db->where('user', $this->session->userdata('username'));
    $results = $this->db->get('subscribed');
    $existing = array();
    foreach ($results->result() as $row)
      $existing[$row->subscription] = TRUE;

    // get feed
    $feed = $this->yt->getSubscriptionFeed('default');
    while ($feed !== NULL)
    {
      foreach ($feed as $entry)
      {
        // construct subscription
        $subscription = array(
          'username' => $entry->username->text,
          'display' => $entry->username->extensionAttributes['display']['value'],
          'thumbnail' => $entry->mediaThumbnail->url,
          'updated' => time()
        );
        // insert or update
        $this->db->insert('subscriptions', $subscription);
        if ($this->db->_error_number() !== 0)
        {
          $this->db->where('username', $subscription['username']);
          $this->db->update('subscriptions', $subscription);
        }
        // set as subscribed by user
        if (!isset($existing[$subscription['username']]))
        {
          $added++;
          $existing[$subscription['username']] = FALSE;
        }
        if ($existing[$subscription['username']] !== TRUE)
          $this->db->insert('subscribed', array('user' => $this->session->userdata('username'), 'subscription' => $subscription['username']));
        $existing[$subscription['username']] = FALSE;
      }
      // pagination
      $next = $feed->getNextLink();
      if ($next === NULL)
        $feed = NULL;
      else
        $feed = $this->yt->getSubscriptionFeed(NULL, $next->href);
    }

    // remove what the user is no longer subscribed to
    foreach ($existing as $subscription => $value)
      if ($value === TRUE)
      {
        $removed++;
        $this->db->where('user', $this->session->userdata('username'));
        $this->db->where('subscription', $subscription);
        $this->db->delete('subscribed');
      }

    // remove redundant subscriptions
    $results = $this->db->query('SELECT DISTINCT username FROM subscriptions LEFT JOIN subscribed ON username=subscription WHERE subscription IS NULL;');
    foreach ($results->result() as $row)
    {
      $this->db->where('username', $row->username);
      $this->db->delete('subscriptions');
    }

    // output
    $this->load->view('header');
    $this->load->view('videos/update', array('added' => $added, 'removed' => $removed));
    $this->load->view('footer');
  }
}
