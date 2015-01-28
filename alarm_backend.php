<?php
  function wakeup(){
    file_get_contents("http://192.168.1.10:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=14");
    sleep(3);
    file_get_contents("http://127.0.0.1/squeezeControl/playmusic.php?scene=wakeup&player=bedroom&volume=30");
   }

   function wakeup_again(){
     file_get_contents("http://192.168.1.10:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=15"); 
     file_get_contents("http://127.0.0.1/squeezeControl/playmusic.php?scene=wakeup_again&player=bedroom&volume=45");
   }

  date_default_timezone_set("America/New_York");
  $key = trim(file_get_contents("/var/www/html/wakeup/backend_key"));
  $settings_file = "/var/www/html/wakeup/settings.json";
  $fields = array("alarm_time","weekends","enabled","secondary_alarm_delay");

  $settings_json = file_get_contents($settings_file);
  $settings = json_decode($settings_json, true);


  if(isset($_GET["func"]) and $_GET["func"] == "get") {
      print $settings_json;
  }

  if(isset($_GET["func"]) and $_GET["func"] == "set") {
      if(!isset($_GET["key"]) or $_GET["key"] != $key) { exit;}   
      foreach($fields as $field){
        if(isset($_GET[$field])) {
          $settings[$field] = $_GET[$field];
        }
      }
   file_put_contents($settings_file, json_encode($settings));
   print "";
  }


  #Logic that is run by cron regularly.
  if(isset($argv[1]) and $argv[1] == "tick"){
    $date= date("Y-m-d H:i:s");
  
     $curtime = date("Hi");
     if(isset($argv[2])){
       $curtime = $argv[2];
     }

     $alarm_time = $settings["alarm_time"];
     print "$date - tock(Time: $curtime) Settings( ";

     foreach($fields as $field){
      $setting = $settings[$field];
      print "$field:$setting ";
     }    

    print ")";

     if(strcmp($settings["enabled"],"false") == 0){
       print " - Alarm Disabled\n";
       exit;
     }

     $weekday =  date("w");
     if (strcmp($settings["weekends"],"false") == 0 and ($weekday == 0 or $weekday == 6)){
        print " - Disabled on Weekends\n";
        exit;
     }

     if(strcmp($curtime,$alarm_time) == 0) {
        print "  WAKEUP!";
        if(isset($argv[2])) { print "\n"; exit;}
        wakeup();
     }
     
     $alarm2_time =  date("Hi",strtotime($alarm_time) + 60*$settings["secondary_alarm_delay"]); 
     if(strcmp($curtime,$alarm2_time) == 0) {
        print "  WAKEUP AGAIN!";
        if(isset($argv[2])) { print "\n"; exit;}
        wakeup_again();        
     }

     print "\n";
  }  
?>
