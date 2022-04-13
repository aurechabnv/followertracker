function getCookie(c_name) {
  var i, x, y, ARRcookies = document.cookie.split(";");
  for (i = 0; i < ARRcookies.length; i++) {
    x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
    x = x.replace(/^\s+|\s+$/g, "");
    if (x == c_name) {
      return unescape(y);
    }
  }
}

$(function(){
  
  console.log(getCookie("watchdog")!==undefined);
  
  if(getCookie("watchdog")!==undefined){
    $("#changeCredentials").show();
    $("#credentials").hide();
  }
  
  //set event to display credentials
  $("#changeCredentials").click(function(event){
    event.preventDefault();
    $(this).slideToggle();
    $("#credentials").slideToggle();
  });
  
  //set event on submit
  $('#submit').click(function(event) {
    var url = 'response.php';
    
    //store input values in params object
    var params = {};
    var missing = false;
    $('#followerTracker').find('input[type=text], input[type=radio]:checked, select').each(function(){
      params[$(this).attr('name')] = $(this).val();
      //check for missing values
      if ($(this).val()==="") {
        missing = true;
        return;
      }
    });
    
    if(!missing){
      //reinitialize output div and display 'loading'
      $('#loading').show();
      $('#output').empty();
      $('body,html').animate({ scrollTop: $('#output').offset().top }, "slow");
      
      //hide credentials and display change button
      if($("#credentials").css("display")!="none"){
        $("#changeCredentials,#credentials").slideToggle();
      }
      
      if(params.outputType=='html'){
        //prevent default behaviour from firing
        event.preventDefault();
      
        //fire ajax request
        $.post(
          url,
          params,
          function(data){
            $('#loading').hide();
            $('#output').append(data);
            $('body,html').animate({ scrollTop: $('#output').offset().top }, "slow");
          },
          'html'
        );
      }
      else {
        $('#followerTracker').submit();
        $('#loading').hide();
      }
    }
  });
  
});