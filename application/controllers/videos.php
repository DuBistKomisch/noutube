<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Videos extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('videos_model');
  }

  public function index()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
      redirect('home', 'refresh');

    // fetch data
    $results_new = $this->videos_model->list_new_subscriptions();
    $results_later = $this->videos_model->list_later_subscriptions();

    // show subscriptions
    $this->load->view('header', array('pageName' => 'Video Manager'));
    $this->load->view('videos/videos');
    
    $this->load->view('videos/new');
    if ($results_new->num_rows() == 0)
      $this->load->view('videos/new_none');
    else foreach ($results_new->result_array() as $row)
      $this->load->view('videos/channel', $row);

    $this->load->view('videos/later');
    if ($results_later->num_rows() == 0)
      $this->load->view('videos/later_none');
    else foreach ($results_later->result_array() as $row)
      $this->load->view('videos/channel', $row);

    $this->load->view('footer');
  }

  public function all()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
      redirect('home', 'refresh');

    // fetch data
    $results = $this->videos_model->list_subscriptions();

    // show subscriptions
    $this->load->view('header', array('pageName' => 'All Subscriptions'));
    $this->load->view('videos/videos');

    $this->load->view('videos/all');
    if ($results->num_rows() == 0)
      $this->load->view('videos/all_none');
    foreach ($results->result_array() as $row)
      $this->load->view('videos/channel', $row);

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
    $results = $this->videos_model->get_subscriptions();
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
        $this->videos_model->put_channel($channel);
        // set as subscribed by user
        if (!isset($existing[$channel['username']]))
        {
          $added++;
          $existing[$channel['username']] = FALSE;
        }
        if ($existing[$channel['username']] !== TRUE)
          $this->videos_model->subscribe($channel['username']);
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
        $this->videos_model->unsubscribe($channel);
      }

    // remove redundant channels and videos
    $this->videos_model->cull_channels();

    // output
    $this->load->view('header', array('pageName' => 'Update Subscriptions'));
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

    // open log file
    $log = fopen('poll.log', 'a');
    if ($log !== FALSE)
      fwrite($log, 'poll started at ' . date('Y-m-d H:i:s P') . PHP_EOL);

    // get list of channels
    $channels = $this->videos_model->get_channels();
    if ($log !== NULL)
      fwrite($log, 'got ' . $channels->num_rows() . ' channels' . PHP_EOL);
    foreach ($channels->result() as $channel)
    {
      // fetch recent uploads
      $added = 0;
      $uploads = $this->yt->getUserUploads(NULL, 'https://gdata.youtube.com/feeds/mobile/users/' . $channel->username . '/uploads?max-results=5');
      $subscribers = $this->videos_model->get_subscribers($channel->username);
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
        if ($this->videos_model->put_video($video))
        {
          // inserted, give all subscribers an item for it
          $added++;
          $this->videos_model->push_video($channel->username, $subscribers, $video);
        }
      }
      // update last checked time for subscription
      $this->videos_model->touch_channel($channel->username);
      // update 'new' counts if any were added
      if ($log !== FALSE)
        fwrite($log, $added . ' new videos found for ' . $channel->username . ' for ' . $subscribers->num_rows() . ' users ' . PHP_EOL);
      if ($added == 0)
        continue;
      $this->videos_model->update_new($channel->username);
    }

    // remove redundant videos
    $this->videos_model->cull_videos();

    // done
    if ($log !== FALSE)
    {
      fwrite($log, 'poll finished at ' . date('Y-m-d H:i:s P') . PHP_EOL);
      fclose($log);
    }
  }
}
