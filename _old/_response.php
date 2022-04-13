<?php

echo phpversion();
echo "<br>";

//fetch twitter API exchange and PHP export classes
require_once('class/TwitterAPIExchange.class.php');
require_once('class/PHPExportData.class.php');
require_once('class/ChromeLogger.class.php');

//initialize output var
$output = '';

//get POST parameters passed in request
$consumerKey = $_POST['key'];
$consumerKeySecret = $_POST['secret_key'];
$accessToken = $_POST['token'];
$accessTokenSecret = $_POST['secret_token'];
$account = $_POST['account'];
$outputType = $_POST['outputType'];
$requestType = $_POST['requestType'];
$requestName = ($requestType=='following') ? 'friends' : $requestType;

//store authentication parameters in COOKIE
$authentication = array('key' => $consumerKey, 'secret_key' => $consumerKeySecret, 'token' => $accessToken, 'secret_token' => $accessTokenSecret);
$timespan = 60 * 60 * 24; //24 hours
setcookie("watchdog", serialize($authentication), time()+$timespan);

//test values
if($testConfig==1){
  $consumerKey = 'NFPAldz7YN2m0Ofq6x4tSQ';
  $consumerKeySecret = 'nCRgZGxhMO1pNlHuNZHefghpz3Z2wLEM7vxdmiOcJUA';
  $accessToken = '974481878-4KJsKEenrTlhOAhckkjLuwsH8HW1FygYC1cA2BIX';
  $accessTokenSecret = 'ucLS46uEe1kzDlWLgP3a1GArdsVzp3soj0FI7DgAAK3Nz';
}
else{
  $consumerKey = '8Jb4fSdXsekSuADQYqQ2HA';
  $consumerKeySecret = 'Qz5rc6X0MnYM791R6Q7XwXq6QSpq46Ucn6IhDe9II8';
  $accessToken = '974481878-QA52Fsp5gb74y2N5V2NVWZaR76GaTwjWn6bDIxTs';
  $accessTokenSecret = 'TlI71SHQYM2bLKkVE0KD8vezK0K7IjqWDeX67d6WA';
}
$outputType = "html";
$account = "achabannes";
$requestName = "friends";

//initialize twitter oauth settings
$settings = array(
  'oauth_access_token' => $accessToken,
  'oauth_access_token_secret' => $accessTokenSecret,
  'consumer_key' => $consumerKey,
  'consumer_secret' => $consumerKeySecret
);

$i = 0; //id counter
$cursor = -1; //cursor start value
$request = 0; //request counter
$list = array(); //array to store fetched ids

//BEGIN ID REQUEST LOOP
do {
  $request++;
  
  $url = 'https://api.twitter.com/1.1/'.$requestName.'/ids.json';
  $getfield = '?cursor='.$cursor.'&screen_name='.$account;
  $requestMethod = 'GET';
  $twitter = new TwitterAPIExchange($settings);
  $response = $twitter->setGetfield($getfield)
											->buildOauth($url, $requestMethod)
											->performRequest();
	
  //decode response json to usable php variable
  $response = json_decode($response, true);
	ChromePhp::log($url);
  ChromePhp::log($response);
	
  //no error detected, add ids to $list array
  if (!array_key_exists('errors', $response)) {
    $ids = $response['ids'];
    foreach($ids as $id){
      $list[] = $id;
      $i++;
    }
    //set next page cursor
    $cursor = $response["next_cursor_str"];
  }
  //errors detected, echo them and break the loop
  else {
    $errors = $response["errors"];
    $output .= "Total IDs retrieved: " . $i . "<br>"
      . "Total requests performed: " . $request . "<br>";
		if($errors!=null) {
			foreach($errors as $error){
				$code = $error['code'];
				$msg = $error['message'];
				$output .="Error " . $code . ": " . $msg . "<br>";
			}
		}
    break;
  }
}
while ($cursor!=0);
//END request loop


//if there were any errors, echo them and stop script
if (array_key_exists('errors', $response)) {
  echo $output;
  exit;
}


//BEGIN GET FOLLOWER INFO
if ($i>0) {
  
  $request = 0; //reset request count
  
  if($outputType=='html'){
    $output .= "<strong>" . ucfirst($requestType) . " total:</strong> " . $i
              . "<br><br>";
  }
  
  if($outputType!='html') {
    if($outputType=='csv'){
      $fileExport = new ExportDataCSV('browser');
    }
    elseif($outputType=='xls'){
      $fileExport = new ExportDataExcel('browser');
    }
    $fileExport->filename = $account . "-" . $requestType . "." . $outputType;
    $fileExport->initialize();
    $fileExport->addRow(array("username","name","description","location","protected","followers_count"));
  }
  else {
    $htmlOutput = array();
  }
  
  //BEGIN request loop
  do {
    $request++;
    
    $nb = 100; //set stack limit
    $newlist = ''; //string var to hold stacked ids
    
    //get next 100 ids to process
    foreach($list as $key=>$item) {
      unset($list[$key]);
      $newlist .= $item . ',';
      $nb--;
      $i--;
      if($nb==0) {
        break;
      }
    }
    if (strlen($newlist)>0) {
      $newlist[strlen($newlist)-1] = '';
    }
    ChromePhp::log($newlist);
    $url = 'https://api.twitter.com/1.1/users/lookup.json';
		/*
		$getfield = '?include_entities=true&user_id='.$newlist;
    ChromePhp::log($getfield);
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($settings);
		$response = $twitter->setGetfield($getfield)
												->buildOauth($url, $requestMethod)
												->performRequest();
		*/
    $postfields = array(
      'user_id' => $newlist,
      'include_entities' => true
    );
    $requestMethod = 'POST';
    $twitter2 = new TwitterAPIExchange($settings);
    $response = $twitter2->buildOauth($url, $requestMethod)
												->setPostfields($postfields)
												->performRequest();
    
    //decode response json to usable php variable
    $response = json_decode($response, true);
		ChromePhp::log($postfields);
		ChromePhp::log($url);
		ChromePhp::log($response);
    
    //no error detected, get each $user info in $response and store it
    if (!array_key_exists('errors', $response)) {
      
      foreach($response as $user){
        $protected = $user['protected'];
        if($protected==1) $protected = "yes";
        else $protected = "no";
        
        $userRow = array(
          'thumb'=>$user['profile_image_url'],
          'username'=>$user['screen_name'],
          'name'=>$user['name'],
          'desc'=>$user['description'],
          'location'=>$user['location'],
          'protected'=>$protected,
          'followers_count'=>$user['followers_count']
        );
        
        if($outputType=='html') {
          $htmlOutput[] = $userRow;
        }
        else {
          unset($userRow['thumb']);
          $fileExport->addRow($userRow);
        }
        
      }
    }
    //errors detected, echo and treat them
    else {
      $errors = $response['errors'];
      foreach($errors as $error){
        $code = $error['code'];
        $msg = $error['message'];
        
        //make pause if error is 88(rate limit exceeded) or 131(internal error) or 130(over capacity)
        if ($code==88 or $code==131 or $code==130) {
          $time = 5 * 60; // 5 minutes
          sleep($time);
        }
//        elseif ($code==34){continue;}
        //otherwise, break the loop
        else {
          //$output is overriden by the error message
          $output .= "Error " . $code . ": " . $msg;
					$i=0;
          break;
        }
        
      }
    }
  }
  while ($i>0);
  //END request loop
  
  //no error detected, echo results
  if (!array_key_exists('errors', $response)) {
    if($outputType=='html') {
      
      //sort users according to follower_count
      $followerSort = array();
      foreach ($htmlOutput as $follower) {
        $followerSort[] = $follower['followers_count'];
      }
      array_multisort($followerSort, SORT_DESC, $htmlOutput);
      
      //render users in resulting order
      foreach($htmlOutput as $item){
        $output .= "<div class='user'>"
                  . "<a title='" . $item['name'] . " - " . $item['desc'] ."' href='http://www.twitter.com/" . $item['username'] . "'>"
                  . "<h3>" . "<img src='" . $item['thumb'] . "'>" . $item['name'] . "</h3>"
                  . "</a>";
        if($item['location']!=""){
          $output .= "<span class='icon'>" . "<img src='img/home.png' title='Location'>" . $item['location'] . "</span>";
        }
        $output .= "<span class='icon'>" . "<img src='img/TwFol.png' title='Number Of Followers'>" . number_format($item['followers_count']) . "</span>"
                  . "<span class='icon'>" . "<img src='img/lock.png' title='Private Account'>" . $item['protected'] . "</span>"
                  . "<p>" . $item['desc'] . "</p>"
                  . "</div>";
      }
      //echo $output;
    }
    else {
      $fileExport->finalize();
    }
  }
	
	echo $output;
  
}
//END GET FOLLOWER INFO

exit;
?>