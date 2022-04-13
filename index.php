<?php
  if(isset($_COOKIE["watchdog"])){
    $authentication = unserialize($_COOKIE["watchdog"]);
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Watchdog - Follower tracker</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <!--<link rel="stylesheet/less" type="text/css" href="styles.less">
    <script type="text/javascript" src="js/less-1.3.3.min.js"></script>-->
    <script type="text/javascript" src="js/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="js/ajax.js"></script>
  </head>
  <body>
    <header>
      <h1><img src="img/topbanner.gif" title="Watchdog - Follower Tracker" /></h1>
    </header>
    <h2>How to use Follower Tracker for Twitter?</h2>
    <ol>
      <li class="box">
        <div class="path">1.</div>
        <div class="content">Go on <a href="https://dev.twitter.com/">dev.twitter.com</a> to <strong>connect your account</strong> to the Twitter developers' interface.</div>
      </li>
      <li class="box">
        <div class="path">2.</div>
        <div class="content">Go on the top right corner where you could see your avatar and select <a href="https://dev.twitter.com/apps"><strong>My Applications</strong></a>.</div>
      </li>
      <li class="box">
        <div class="path">3.</div>
        <div class="content">Fill <strong>all informations</strong> Twitter wants. For the website field, you can use <a href="http://www.google.com/">www.google.com</a>.</div>
      </li>
      <li class="box">
        <div class="path">4.</div>
        <div class="content">Create an <strong>access token</strong> in the back of the <strong>detail section</strong> of your application.</div>
      </li>
      <li class="box">
        <div class="path">5.</div>
        <div class="content">Go on <strong>OAuth tool</strong> and copy/paste all information hereunder in the fields.</div>
      </li>
      <li class="box">
        <div class="path">6.</div>
        <div class="content">Finally, fill the <strong>account name</strong> hereunder you want to track without the arobase.</div>
      </li>
      <div class="clear"></div>
    </ol>
    <form method="post" id="followerTracker" action="response.php">
      <button name="button" id="changeCredentials">Change authentication credentials</button>
      <div id="credentials">
        <input type="text" name="key" placeholder="Consumer Key" required="required" <?php if(!isset($_COOKIE['watchdog'])){echo 'autofocus';}else{echo 'value="'.$authentication['key'].'"';}?>>
        <input type="text" name="secret_key" placeholder="Consumer Secret" required="required"<?php if(isset($_COOKIE['watchdog'])){echo ' value="'.$authentication['secret_key'].'"';}?>>
        <input type="text" name="token" placeholder="Access Token" required="required"<?php if(isset($_COOKIE['watchdog'])){echo ' value="'.$authentication['token'].'"';}?>>
        <input type="text" name="secret_token" placeholder="Access Token Secret" required="required"<?php if(isset($_COOKIE['watchdog'])){echo ' value="'.$authentication['secret_token'].'"';}?>>
      </div>
      <div id="twitterSymbol">
        <span>@</span>
        <input type="text" name="account" placeholder="Account Name" required="required"<?php if(isset($_COOKIE['watchdog'])){echo ' autofocus';}?>>
      </div>
      <div class="select">
        <select name="outputType">
          <option value="html" selected>HTML Preview</option>
          <option value="csv">CSV Export</option>
          <option value="xls">XLS Export</option>
        </select>
      </div>
      <div class="request">
        <div>Get this account's:</div>
        <input type="radio" name="requestType" id="followers" value="followers" checked><label for="followers">Followers</label>
        <input type="radio" name="requestType" id="following" value="following"><label for="following">Following</label>
      </div>
      <button name="submit" id="submit">Submit</button>
    </form>
    <div id="loading"><img src="img/ajax-loader.gif" alt="loading"></div>
    <div id="output"></div>
  </body>
</html>