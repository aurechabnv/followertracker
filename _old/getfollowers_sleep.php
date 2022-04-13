<?php

require_once('TwitterAPIExchange.php');

$consumerKey = '8Jb4fSdXsekSuADQYqQ2HA';
$consumerKeySecret = 'Qz5rc6X0MnYM791R6Q7XwXq6QSpq46Ucn6IhDe9II8';
$accessToken = '974481878-QA52Fsp5gb74y2N5V2NVWZaR76GaTwjWn6bDIxTs';
$accessTokenSecret = 'TlI71SHQYM2bLKkVE0KD8vezK0K7IjqWDeX67d6WA';

$settings = array(
  'oauth_access_token' => $accessToken,
  'oauth_access_token_secret' => $accessTokenSecret,
  'consumer_key' => $consumerKey,
  'consumer_secret' => $consumerKeySecret
);

$i = 0;
$cursor = -1;

do {
  $url = 'https://api.twitter.com/1.1/followers/list.json';
  $getfield = '?cursor='.$cursor.'&screen_name=peugeot&skip_status=true&include_user_entities=false';
  $requestMethod = 'GET';
  $twitter = new TwitterAPIExchange($settings);
  $response = $twitter->setGetfield($getfield)
                      ->buildOauth($url, $requestMethod)
                      ->performRequest();
  
  $response = json_decode($response, true);
  $errors = $response["errors"];
  
  if (empty($errors)) {
    $users = $response['users'];
    foreach($users as $user){
      $thumb = $user['profile_image_url'];
      $url = $user['screen_name'];   
      $name = $user['name'];
      echo "<a title='" . $name . "' href='http://www.twitter.com/" . $url . "'>" . "<img src='" . $thumb . "' /></a>";
      $i++;
    }
    $cursor = $response["next_cursor"];
    
    $time = 15 * 60;
    if($i==300) sleep($time);
  }
  else {
    foreach($errors as $error){
      $code = $error['code'];
      $msg = $error['message'];
      echo "<br><br>Error " . $code . ": " . $msg;
    }
    $cursor = 0;
  }
}
while ( $cursor != 0 );

if (!empty($users)) {
  echo '<br><br>Total: ' . $i;
}

?>