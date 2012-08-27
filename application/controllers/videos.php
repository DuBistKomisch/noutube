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
    $this->db->join('subscription', 'username=channel');
    $this->db->where('user', $this->session->userdata('username'));
    $this->db->where('new >', '0');
    $results_new = $this->db->get('channel');
    $this->db->select('display, thumbnail, checked, new, later');
    $this->db->join('subscription', 'username=channel');
    $this->db->where('user', $this->session->userdata('username'));
    $this->db->where('later >', '0');
    $results_later = $this->db->get('channel');

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
    else foreach ($results_later->result_array() as $row)
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
    $this->db->join('subscription', 'username=channel');
    $this->db->where('user', $this->session->userdata('username'));
    $results = $this->db->get('channel');

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
    $this->db->select('channel');
    $this->db->where('user', $this->session->userdata('username'));
    $results = $this->db->get('subscription');
    $existing = array();
    foreach ($results->result() as $row)
      $existing[$row->channel] = TRUE;

    // get feed
    $feed = $this->yt->getSubscriptionFeed('default');
    while ($feed !== NULL)
    {
      foreach ($feed as $entry)
      {
        // construct channel
        $channel = array(
          'username' => $entry->username->text,
          'display' => $entry->username->extensionAttributes['display']['value'],
          'thumbnail' => $entry->mediaThumbnail->url,
          'updated' => time()
        );
        // insert or update
        $this->db->insert('channel', $channel);
        if ($this->db->_error_number() !== 0)
        {
          $this->db->where('username', $channel['username']);
          $this->db->update('channel', $channel);
        }
        // set as subscribed by user
        if (!isset($existing[$channel['username']]))
        {
          $added++;
          $existing[$channel['username']] = FALSE;
        }
        if ($existing[$channel['username']] !== TRUE)
          $this->db->insert('subscription', array('user' => $this->session->userdata('username'), 'channel' => $channel['username']));
        $existing[$channel['username']] = FALSE;
      }
      // pagination
      $next = $feed->getNextLink();
      if ($next === NULL)
        $feed = NULL;
      else
        $feed = $this->yt->getSubscriptionFeed(NULL, $next->href);
    }

    // remove what the user is no longer subscribed to
    foreach ($existing as $channel => $value)
      if ($value === TRUE)
      {
        $removed++;
        $this->db->where('user', $this->session->userdata('username'));
        $this->db->where('channel', $channel);
        $this->db->delete('subscription');
      }

    // remove redundant subscriptions
    $results = $this->db->query('SELECT DISTINCT username FROM channel LEFT JOIN subscription ON username=channel WHERE channel IS NULL;');
    foreach ($results->result() as $row)
    {
      $this->db->where('username', $row->username);
      $this->db->delete('channel');
    }

    // output
    $this->load->view('header');
    $this->load->view('videos/update', array('added' => $added, 'removed' => $removed));
    $this->load->view('footer');
  }

  // poll for new videos from each channel
  public function poll()
  {
    // run via the CLI only!
    if (!$this->input->is_cli_request())
    {
      redirect('home/error404', 'redirect');
      return;
    }

    // get list of channels
    $this->db->select('username');
    $channels = $this->db->get('channel');
    foreach ($channels->result() as $channel)
    {
      // fetch recent uploads
      $added = 0;
      $uploads = $this->yt->getUserUploads(NULL, 'https://gdata.youtube.com/feeds/mobile/users/' . $channel->username . '/uploads?max-results=5');
      foreach ($uploads->entry as $entry)
      {
        // construct video
        $video = array(
          'video' => $entry->mediaGroup->videoid->text,
          'title' => $entry->mediaGroup->title->text,
          'published' => strtotime($entry->published->text),
          'duration' => (int)$entry->mediaGroup->duration->seconds,
          'description' => $entry->mediaGroup->description->text,
          'channel' => $channel->username
        );
        // insert or update
        $this->db->insert('video', $video);
        if ($this->db->_error_number() !== 0)
        {
          // already exists, just update data
          $this->db->where('video', $video['video']);
          $this->db->update('video', $video);
        }
        else
        {
          // inserted, give all subscribers an item for it
          $added++;
          $this->db->select('user');
          $this->db->where('channel', $channel->username);
          $users = $this->db->get('subscription');
          foreach ($users->result() as $user)
            $this->db->insert('item', array('video' => $video['video'], 'user' => $user->user, 'channel' => $channel->username));
        }
      }
      // update last checked time for subscription
      $this->db->where('username', $channel->username);
      $this->db->update('channel', array('checked' => time()));
      // update 'new' counts if any were added
      if ($added == 0)
        continue;
      $updates = $this->db->query('SELECT subscription.user, COUNT(*) AS count FROM subscription LEFT JOIN item ON subscription.user=item.user AND subscription.channel=item.channel WHERE subscription.channel=\'' . $channel->username . '\' AND item.state=0 GROUP BY subscription.user, subscription.new HAVING COUNT(*) <> subscription.new');
      foreach ($updates->result_array() as $row)
      {
        $this->db->where('user', $row['user']);
        $this->db->update('subscription', array('new' => $row['count']));
      }
    }

    // remove redundant videos
    $results = $this->db->query('SELECT DISTINCT video.video FROM video LEFT JOIN item ON video.video=item.video WHERE item.video IS NULL;');
    foreach ($results->result() as $row)
    {
      $this->db->where('video', $row->video);
      $this->db->delete('video');
    }
  }
}
