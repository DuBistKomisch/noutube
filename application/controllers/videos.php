<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Videos extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('videos_model');
  }

  public function _plural($base, $n, $singular = '', $plural = 's')
  {
    return $base . ($n == 1 ? $singular : $plural);
  }

  public function _date($time)
  {
    return date('Y-m-d H:i:s \U\T\CP', $time);
  }

  public function _hms($time)
  {
    $result = '';
    if ($time > 3600)
      $result .= sprintf('%d:%02d:%02d', $time / 3600, $time / 60 % 60, $time % 60);
    else
      $result .= sprintf('%d:%02d', $time / 60 % 60, $time % 60);
    return $result;
  }

  public function _ago($time)
  {
    $span = time() - $time;
    $years   = floor($span / (365 * 24 * 60 * 60));    $span -= $years   * (365 * 24 * 60 * 60);
    $months  = floor($span / (365/12 * 24 * 60 * 60)); $span -= $months  * (365/12 * 24 * 60 * 60);
    $weeks   = floor($span / (7 * 24 * 60 * 60));      $span -= $weeks   * (7 * 24 * 60 * 60);
    $days    = floor($span / (24 * 60 * 60));          $span -= $days    * (24 * 60 * 60);
    $hours   = floor($span / (60 * 60));               $span -= $hours   * (60 * 60);
    $minutes = floor($span / (60));                    $span -= $minutes * (60);
    $seconds = $span;

    if      ($years > 0)   $data = array('v1' => $years, 's1' => 'year', 'v2' => $months, 's2' => 'month');
    else if ($months > 0)  $data = array('v1' => $months, 's1' => 'month', 'v2' => $weeks, 's2' => 'week');
    else if ($weeks > 0)   $data = array('v1' => $weeks, 's1' => 'week', 'v2' => $days, 's2' => 'day');
    else if ($days > 0)    $data = array('v1' => $days, 's1' => 'day', 'v2' => $hours, 's2' => 'hour');
    else if ($hours > 0)   $data = array('v1' => $hours, 's1' => 'hour', 'v2' => $minutes, 's2' => 'minute');
    else if ($minutes > 0) $data = array('v1' => $minutes, 's1' => 'minute', 'v2' => $seconds, 's2' => 'second');
    else                   $data = array('v1' => $seconds, 's1' => 'second', 'v2' => 0, 's2' => '');

    return $data['v1'] . ' ' . self::_plural($data['s1'], $data['v1']) . ($data['v2'] > 0 ? ' and ' . $data['v2'] . ' ' . self::_plural($data['s2'], $data['v2']) : '');
  }

  public function index()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
    {
      redirect('home', 'refresh');
      return;
    }

    // form helper
    $this->load->helper('form');

    // process form
    if ($this->input->post('new') !== FALSE)
    {
      foreach ($this->input->post() as $name => $value)
        if (strlen($name) == 14)
          $this->videos_model->watch_video_later(substr($name, 3));
      $this->videos_model->cull_new_videos();
      redirect('videos', 'refresh');
      return;
    }
    else if ($this->input->post('later') !== FALSE)
    {
      foreach ($this->input->post() as $name => $value)
        if (strlen($name) == 16)
          $this->videos_model->watched_video(substr($name, 5));
      $this->videos_model->cull_watched_videos();
      redirect('videos', 'refresh');
      return;
    }

    // show subscriptions
    $pageInfo = array('pageName' => 'Video Manager');
    $newCount = $this->videos_model->get_new_count();
    if ($newCount > 0)
      $pageInfo['status'] = $newCount;
    $this->load->view('header', $pageInfo);
    $this->load->view('videos/videos');
    
    // new
    $results_new = $this->videos_model->list_new_subscriptions();
    if ($results_new->num_rows() == 0)
      $this->load->view('videos/new_none');
    else
    {
      $this->load->view('videos/new_start', array('total' => $newCount));
      foreach ($results_new->result_array() as $row)
      {
        $row['checked_date'] = self::_date($row['checked']);
        $row['checked_ago'] = self::_ago($row['checked']);
        $this->load->view('videos/channel', $row);
        $videos = $this->videos_model->list_new_videos($row['username']);
        foreach ($videos->result_array() as $video)
        {
          $video['published_date'] = self::_date($video['published']);
          $video['published_ago'] = self::_ago($video['published']);
          $video['duration_hms'] = self::_hms($video['duration']);
          $video['form'] = 'new';
          $this->load->view('videos/video', $video);
        }
      }
      $this->load->view('videos/new_end');
    }

    // later
    $results_later = $this->videos_model->list_later_subscriptions();
    if ($results_later->num_rows() == 0)
      $this->load->view('videos/later_none');
    else
    {
      $this->load->view('videos/later_start', array('total' => $this->videos_model->get_later_count()));
      foreach ($results_later->result_array() as $row)
      {
        $row['checked_date'] = self::_date($row['checked']);
        $row['checked_ago'] = self::_ago($row['checked']);
        $this->load->view('videos/channel', $row);
        $videos = $this->videos_model->list_later_videos($row['username']);
        foreach ($videos->result_array() as $video)
        {
          $video['published_date'] = self::_date($video['published']);
          $video['published_ago'] = self::_ago($video['published']);
          $video['duration_hms'] = self::_hms($video['duration']);
          $video['form'] = 'later';
          $this->load->view('videos/video', $video);
        }
      }
      $this->load->view('videos/later_end');
    }

    // done
    $this->load->view('footer');
  }

  public function all()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
    {
      redirect('home', 'refresh');
      return;
    }

    // fetch data
    $results = $this->videos_model->list_subscriptions();

    // show subscriptions
    $this->load->view('header', array('pageName' => 'All Subscriptions'));
    $this->load->view('videos/videos');

    $this->load->view('videos/all');
    if ($results->num_rows() == 0)
      $this->load->view('videos/all_none');
    else foreach ($results->result_array() as $row)
    {
      $row['checked_date'] = self::_date($row['checked']);
      $row['checked_ago'] = self::_ago($row['checked']);
      $this->load->view('videos/channel', $row);
    }

    $this->load->view('footer');
  }

  // fetches list of user's subscriptions and updates the database accordingly
  public function update()
  {
    // redirect if not authenticated
    if ($this->session->userdata('token') === FALSE)
    {
      redirect('home', 'refresh');
      return;
    }

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
          'updated' => time(),
          'checked' => time()
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
    $this->load->view('videos/videos');
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
      fwrite($log, 'poll started at ' . self::_date(time()) . PHP_EOL);

    // get list of channels
    $channels = $this->videos_model->get_channels();
    if ($log !== NULL)
      fwrite($log, 'found ' . $channels->num_rows() . ' channels' . PHP_EOL);
    foreach ($channels->result() as $channel)
    {
      // fetch recent uploads
      try
      {
        $uploads = $this->yt->getUserUploads(NULL, 'https://gdata.youtube.com/feeds/mobile/users/' . $channel->username . '/uploads?max-results=2');
      }
      catch (Exception $e)
      {
        continue;
      }
      $subscribers = $this->videos_model->get_subscribers($channel->username);
      $added_total = 0;
      while ($uploads !== NULL)
      {
        $added = 0;
        foreach ($uploads->entry as $entry)
        {
          // construct video
          $video = array(
            'video' => $entry->mediaGroup->videoid->text,
            'title' => $entry->mediaGroup->title->text,
            'published' => strtotime($entry->published->text),
            'duration' => (int)$entry->mediaGroup->duration->seconds,
            'channel' => $channel->username
          );
          // don't add old videos
          if ($video['published'] < $channel->checked)
            continue;
          // insert or update
          if ($this->videos_model->put_video($video))
          {
            // inserted, give all subscribers an item for it
            $added++;
            $this->videos_model->push_video($channel->username, $subscribers, $video);
          }
        }
        // pagination
        $next = $uploads->getNextLink();
        if ($next === NULL || $added < 2 || $channel->videos == 0)
          $uploads = NULL;
        else
          try
          {
            $uploads = $this->yt->getUserUploads(NULL, $next->href);
          }
          catch (Exception $e)
          {
            break;
          }
        // update total added
        $added_total += $added;
      }
      // update last checked time for subscription
      $this->videos_model->touch_channel($channel->username);
      // update 'new' counts if any were added
      if ($log !== FALSE && $added_total > 0)
        fwrite($log, $added_total . ' new videos found for ' . $channel->username . ' for ' . $subscribers->num_rows() . ' users ' . PHP_EOL);
      if ($added_total > 0)
        $this->videos_model->update_new($channel->username);
    }

    // remove redundant videos
    if ($log !== NULL)
      fwrite($log, 'culling new items older than a week...' . PHP_EOL);
    $this->videos_model->cull_items();
    if ($log !== NULL)
      fwrite($log, 'culling videos without any items...' . PHP_EOL);
    $this->videos_model->cull_videos();
    if ($log !== NULL)
      fwrite($log, 'culling old sessions...' . PHP_EOL);
    $this->videos_model->cull_sessions();

    // done
    if ($log !== FALSE)
    {
      fwrite($log, 'poll finished at ' . self::_date(time()) . PHP_EOL);
      fclose($log);
    }
  }
}
