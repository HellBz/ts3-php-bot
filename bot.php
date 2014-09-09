<?php

/////////////////////////////////////////////////
/* ==============[CONFIGURATION]============== */
$host = "ts.s-cdn.tk";	//IP of the server
$port = "10011";		//Query port
$server_port = "9987";	//Virtual server port
$user = "serveradmin";	//Query login username
$pass = "***";			//Query login password
$name = '$killBot';		//Bot's name
/////////////////////////////////////////////////

require_once("libraries/TeamSpeak3/TeamSpeak3.php");
goto main;
main:
// connecting to the server
$ts3 = TeamSpeak3::factory("serverquery://".$user.":".$pass."@".$host.":".$port."/?server_port=".$server_port."&blocking=0");
$ts3->request('clientupdate client_nickname='.$name); // setting the name
if(!$chyba_spojeni) $ts3->message("Verze 1.9 nyní lítá");

$chyba_spojeni = false;

// registering event listener
$ts3->notifyRegister("textserver");

// registering callback function
TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "onTextmessage");

// waiting for event
try {while(1) $ts3->getAdapter()->wait();}
catch(TeamSpeak3_Transport_Exception $error) {$chyba_spojeni = true;}

function fetchGroup($user)
{
  global $ts3;
  $id_request = $ts3->request("clientfind pattern=".$user)->toString();
  $id_result = explode(" ", $id_request);
  $id = substr($id_result['0'], 5);
  $group_request = $ts3->request("clientinfo clid=".$id)->toString();
  $group_result = explode(" ", $group_request);
  $servergroup = substr($group_result['21'], 20);
  return $servergroup;  
}

// called function on event
function onTextmessage(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
  global $ts3;    
  $msg = $event["msg"];
  $invoker = $event["invokername"];
  
  echo($invoker.": ".$msg."\n");
  
      
  if(fetchGroup($invoker) == "6" OR fetchGroup($invoker) == "7" OR fetchGroup($invoker) == "9") { // here I check if the user has access to bot's commands
  
  //if($invoker_db["client_unique_identifier"] == "UDe92xeUw1ukT46FylXjz6LbpUY=" OR $invoker_db["client_unique_identifier"] == "vvTQr1Rnf8T1vSZDMYLe56yvd8E=" OR $invoker_db["client_unique_identifier"] == "2qid9kGm+4JdPy/aLaOAisxLbsw=" OR $invoker_db["client_unique_identifier"] == "aDtB10Aj7L81TvijvrdCJNtLgzk=" OR $invoker_db["client_unique_identifier"] == "M19tKb6kTJguzYyn6pxkBrcREzc=") { 

 // preparing the command arguments
 
  $arguments = explode(" ", $msg);
    
    // commands start here
  switch ($arguments[0]) {
  case "!ping": 
      $ts3->message("Pong!");
      break;
  case "!botinfo":
      $ts3->message('$killBot je $killův home-made bot, který umí pár užitečných příkazů, které TeamSpeak postrádá. Bota naprogramoval $kill v PHP na Frameworku od PlanetTeamspeak. Zdrojový kód bota je k dispozici zde https://github.com/sisa1917/ts3-php-bot');
      break;
  case "!bothelp":
      $ts3->message('
Příkazy $killBota:
!ping - Test bota
!botinfo - Zobrazí informace o botovi
!bothelp - Zobrazí informace o dostupných příkazech
!botoff - Vypne bota
!botrestart - Restartuje bota
!addrooms - Přidá další místnosti v případě nutnosti
!removerooms - Smaže přidané místnosti pomocí !addrooms
!uid - Zobrazí unikátní identifikátor uživatele
!stick - Přilepí uživatele k místnosti
!unstick - Odlepí uživatele od místnosti
!mute - Umlčí uživatele
!unmute - Odmlčí uživatele
!chatmute - Zakáže chat uživateli
!chatunmute - Povolí chat uživateli');
      break;
  case "!addrooms":
      $ts3->message("Přidávám dodatečné místnosti...");
      global $chann1, $chann2, $chann3;
      try {
      $chann1 = $ts3->channelCreate(array (
  "channel_name"           => "Additional public room #1",
  "channel_topic"          => "",
  "channel_codec"          => TeamSpeak3::CODEC_OPUS_VOICE,
  "channel_codec_quality"  => 6,
  "channel_flag_permanent" => TRUE,
  "channel_order"          => 370,
  //"channel_icon_id"        => 1513344601,
));
      $chann2 = $ts3->channelCreate(array (
  "channel_name"           => "Additional public room #2",
  "channel_topic"          => "",
  "channel_codec"          => TeamSpeak3::CODEC_OPUS_VOICE,
  "channel_codec_quality"  => 6,
  "channel_flag_permanent" => TRUE,
  //"channel_icon_id"        => 1031730392, //NOT working ATM, need to fix
  "channel_order"          => $chann1,
));}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("Dodatečné místnosti přidány"); }
      else $ts3->message("Dodatečné místnosti se nepodařilo přidat"); echo $error;
      break;
  case "!removerooms":
      $ts3->message("Mažu dodatečné místnosti...");
      global $chann1, $chann2, $chann3;
      echo $chann1;
      try{
      $ts3->channelDelete($chann1);
      $ts3->channelDelete($chann2);}
      //$ts3->channelDelete($chann3);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("Dodatečné místnosti byly smazány"); }
      else $ts3->message("Dodatečné místnosti se nepodařilo smazat");
      break;
  case "!botoff":
      $ts3->message("Vypínám se...");
	  $ts3->request('clientupdate client_nickname=$killBot_shutting_down_'.rand(-1000, 1000));
      die();
      break;
  case "!botrestart":
      $ts3->message("Restartuji se...");
	  $ts3->request('clientupdate client_nickname=$killBot_shutting_down_'.rand(-1000, 1000));
      die(exec("php bot.php"));
      break;
  case "!uid":
      if($arguments[1] != "") {
      $ts3->message("Zjišťuji UID uživatele ".$arguments[1]."...");
      try {$uid_db = $ts3->clientGetByName($arguments[1])->InfoDb();}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("UID uživatele ".$arguments[1]." je ".$uid_db["client_unique_identifier"]); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !uid <jméno>");
      break;
  case "!stick":
      if($arguments[1] != "") {
      $ts3->message("Přilepuji uživatele ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permAssign("b_client_is_sticky", TRUE);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("Uživatel ".$arguments[1]." byl přilepen"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !stick <jméno>");
      break;
  case "!unstick":
      if($arguments[1] != "") {
      $ts3->message("Odlepuji uživatele ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permRemove("b_client_is_sticky");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$arguments[1]." byl odlepen"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !unstick <jméno>");
      break;
  case "!mute":
      if($arguments[1] != "") {
      $ts3->message("Umlčuji uživatele ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permAssign("i_client_talk_power", -10);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$arguments[1]." byl umlčen"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !mute <jméno>");
      break;
  case "!unmute":
      if($arguments[1] != "") {
      $ts3->message("Odmlčuji uživatele ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permRemove("i_client_talk_power");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$arguments[1]." byl odmlčen"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !unmute <jméno>");
      break;
  case "!chatmute":
      if($arguments[1] != "") {
      $ts3->message("Zakazuji chat uživateli ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permAssign("b_client_server_textmessage_send", FALSE);$ts3->clientGetByName($arguments[1])->permAssign("b_client_channel_textmessage_send", FALSE);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživateli ".$arguments[1]." byl zakázán chat"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !chatmute <jméno>");
      break;
  case "!chatunmute":
      if($arguments[1] != "") {
      $ts3->message("Povoluji chat uživateli ".$arguments[1]."...");
      try {$ts3->clientGetByName($arguments[1])->permRemove("b_client_server_textmessage_send");$ts3->clientGetByName($arguments[1])->permRemove("b_client_channel_textmessage_send");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživateli ".$arguments[1]." byl povolen chat"); }
      else $ts3->message("Uživatel ".$arguments[1]." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !chatunmute <jméno>");
      
  }

}
}
if($chyba_spojeni) echo "spojeni preruseno, zkousim znovu   "; goto main;
?>
