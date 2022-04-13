<?php

require_once('TwitterAPIExchange.php');

$result = '';

$consumerKey = $_POST['key'];
$consumerKeySecret = $_POST['secret_key'];
$accessToken = $_POST['token'];
$accessTokenSecret = $_POST['secret_token'];
$account = $_POST['account'];

if(empty($consumerKey) or empty($consumerKeySecret) or empty($accessToken) or empty($accessTokenSecret) or empty($account)) {
  //check for missing information
  $result .= "<span class='mdt'>All fields must be filled.</span>";
}
else {
  //execute code
  
  /*
  $consumerKey = 'zhVAzJBS5pBzWjR8FwXwQ';
  $consumerKeySecret = 'HKdfwuOgA7q4s16K32w1SdWg1V6ZgRnPbLMPfqeI';
  $accessToken = '974481878-kOiQqINxwKnGURHrIZm42PnspYSHXCdTDctCAVlC';
  $accessTokenSecret = '7I33UV7x0h8HcwkbHEpYTgoy4bo0egt3rX8V2kMo2Cw';
  */
  
  $settings = array(
    'oauth_access_token' => $accessToken,
    'oauth_access_token_secret' => $accessTokenSecret,
    'consumer_key' => $consumerKey,
    'consumer_secret' => $consumerKeySecret
  );
  
  $result .= '<h2>Get followers IDs</h2>';
  
  $i = 0;
  $cursor = -1;
  $request = 0;
  $list = array();
  
  //BEGIN request loop
  do {
    $url = 'https://api.twitter.com/1.1/followers/ids.json';
    $getfield = '?cursor='.$cursor.'&screen_name='.$account;
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($settings);
    $response = $twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest();
    
    //decode response json to usable php variable
    $response = json_decode($response, true);
    
    //no error detected, add ids to $list array
    if (!array_key_exists('errors', $response)) {
      $ids = $response['ids'];
      foreach($ids as $id){
        $list[] = $id;
        $i++;
      }
      //set next page cursor
      $cursor = $response["next_cursor"];
    }
    //errors detected, echo them and break the loop
    else {
      $errors = $response["errors"];
      foreach($errors as $error){
        $code = $error['code'];
        $msg = $error['message'];
        $result .= "Error " . $code . ": " . $msg;
      }
      break;
    }
    $request++;
  }
  while ($cursor!=0);
  //END request loop
  
  //var_dump($list);
  $total = $i;
  if (array_key_exists('errors', $response)) {
    $result .= '<br><br>';
  }
  $result .= 'Total IDs: ' . $total;
  $result .= '<br><br>';
  $result .= 'Total requests: ' . $request;
  
  
  //BEGIN GET FOLLOWER INFO
  if ($total!=0) {
    $result .= '<hr>'
      . '<h2>Get followers info from ID</h2>'
      . 'beginning: ' . gmdate('Y-m-d H:i:s') . '<br>';
    
    $request = 0;
    
    //BEGIN request loop
    do {
      $request++;
      
      $nb = 0; //set counter to limit stacked ids to 100
      $newlist = ''; //string var to hold stacked ids
      
      //get next 100 ids to process
      foreach($list as $key=>$item) {
        unset($list[$key]);
        $newlist .= $item . ',';
        $nb++;
        $i--;
        if($nb==100) {
          break;
        }
      }
      if (strlen($newlist)>0) {
        $newlist[strlen($newlist)-1] = '';
      }
      
      $result .= '<br>'.$request.'<br>';
      
      $url = 'https://api.twitter.com/1.1/users/lookup.json';
      $postfields = array(
        'user_id' => $newlist,
        'include_entities' => true
      );
      $requestMethod = 'POST';
      $twitter = new TwitterAPIExchange($settings);
      $response = $twitter->buildOauth($url, $requestMethod)
                          ->setPostfields($postfields)
                          ->performRequest();
      
      //decode response json to usable php variable
      $response = json_decode($response, true);
      
      //no error detected, get each $user info in $response and echo it
      if (!array_key_exists('errors', $response)) {
        foreach($response as $user){
          $thumb = $user['profile_image_url'];
          $url = $user['screen_name'];   
          $name = $user['name'];
          $desc = $user['description'];
          $result .= "<div class='css'>"
            . "<a title='" . $name . " - " . $desc ."' href='http://www.twitter.com/" . $url . "'>"
            . "<img src='" . $thumb . "' />"
            . "<h3>" . $name . "</h3><br><br>"
            . "</a>"
            . "<p>" . $desc . "</p>"
            . "</div><br>";
        }
        $result .= '<br>IDs left: '.$i.'<br>';
      }
      //errors detected, echo and treat them
      else {
        $errors = $response['errors'];
        foreach($errors as $error){
          $code = $error['code'];
          $msg = $error['message'];
          $result .= "Error " . $code . ": " . $msg;
        }
        //make pause if error is 88(rate limit exceeded) or 131(internal error) or 130(over capacity)
        if ($code==88 or $code==131 or $code==130) {
          $time = 5 * 60; // 5 minutes
          sleep($time);
        }
        //otherwise, break the loop
        else {
          break;
        }
      }
    }
    while ($i>0);
    //END request loop
    
    //no error detected, get processed ids total
    if (!array_key_exists('errors', $response)) {
      $processed = $total - $i;
      $result .= '<br><br>Total: ' . $processed;
    }
    
    $result .= '<br><br>'
      . 'Total requests: ' . $request . '<br>';
    //$result .= 'Time: ' . $timelapse;
  }
  //END GET FOLLOWER INFO
  
}

echo $result;

?>