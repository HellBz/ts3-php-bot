<?php
require_once("libraries/TeamSpeak3/TeamSpeak3.php");
goto main;
main:
// pripojeni na server
$ts3 = TeamSpeak3::factory("serverquery://serveradmin:Traktor1917@93.185.105.165:10011/?server_port=9987&blocking=0");
$ts3->request('clientupdate client_nickname=$killBot'); //Nastaveni jmena
if(!$chyba_spojeni) $ts3->message("Verze 1.6.2 nyní běží...");

$chyba_spojeni = false;

// registrace eventu na ts query
$ts3->notifyRegister("textserver");

// registrace callbacku
TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "onTextmessage");

// cekam na event
try {while(1) $ts3->getAdapter()->wait();}
catch(TeamSpeak3_Transport_Exception $error) {$chyba_spojeni = true;}

// volana callback funkce
function onTextmessage(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
  global $ts3;    
  $msg = $event["msg"];
  $invoker = $event["invokername"];
    
  if($invoker != '$killBot') { // ochrana proti problemum s volanim sam sebe
    
  $invoker_object = $ts3->clientGetByName($invoker);
  $invoker_db = $invoker_object->infoDb();	// fungovani pouze pro zvolene lidi

  if($invoker_db["client_unique_identifier"] == "UDe92xeUw1ukT46FylXjz6LbpUY=" OR $invoker_db["client_unique_identifier"] == "vvTQr1Rnf8T1vSZDMYLe56yvd8E=" OR $invoker_db["client_unique_identifier"] == "2qid9kGm+4JdPy/aLaOAisxLbsw=" OR $invoker_db["client_unique_identifier"] == "aDtB10Aj7L81TvijvrdCJNtLgzk=" OR $invoker_db["client_unique_identifier"] == "M19tKb6kTJguzYyn6pxkBrcREzc=") { 

 // priprava promennych a orezani zprav pro ify
  $uid = substr($msg,0, 4);
  $uid_user = substr($msg, 5);
  $stick = substr($msg, 0, 6);
  $stick_user = substr($msg, 7);
  $unstick = substr($msg, 0, 8);
  $unstick_user = substr($msg, 9);
  $mute = substr($msg, 0, 5);
  $mute_user = substr($msg, 6);
  $unmute = substr($msg, 0, 7);
  $unmute_user = substr($msg, 8);
  $mutechat = substr($msg, 0, 9);
  $mutechat_user = substr($msg, 10);
  $unmutechat = substr($msg, 0, 11);
  $unmutechat_user = substr($msg, 12);
    
    // ve switchi jsou jednoduche prikazy bez jmen a voleb (!ping)
  switch ($msg) {
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
  //"channel_icon_id"        => 1031730392,
));
      $chann2 = $ts3->channelCreate(array (
  "channel_name"           => "Additional public room #2",
  "channel_topic"          => "",
  "channel_codec"          => TeamSpeak3::CODEC_OPUS_VOICE,
  "channel_codec_quality"  => 6,
  "channel_flag_permanent" => TRUE,
  "channel_icon_id"        => 1031730392,
  //"channel_order"          => $chann1,   TODO: Fix this bullshit not working...
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
      $ts3->message("Shutting down...");
	  $ts3->request('clientupdate client_nickname=$killBot_shutting_down');
      die();
      break;
  case "!botrestart":
      $ts3->message("Restartuji se...");
	  $ts3->request('clientupdate client_nickname=$killBot_shutting_down_'.rand(0, 500));
      die(exec("php bot.php"));
      break;
      
  }
    
    //v ifech jsou slozitejsi prikazy s volbami (!stick jmeno)
  if($uid == "!uid") {
      if($uid_user != "") {
      $ts3->message("Zjišťuji UID uživatele ".$uid_user."...");
      try {$uid_db = $ts3->clientGetByName($uid_user)->InfoDb();}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("UID uživatele ".$uid_user." je ".$uid_db["client_unique_identifier"]); }
      else $ts3->message("Uživatel ".$uid_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !uid <jméno>");
  }
   if($stick == "!stick") {
      if($stick_user != "") {
      $ts3->message("Přilepuji uživatele ".$stick_user."...");
      try {$ts3->clientGetByName($stick_user)->permAssign("b_client_is_sticky", TRUE);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
      $ts3->message("Uživatel ".$stick_user." byl přilepen"); }
      else $ts3->message("Uživatel ".$stick_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !stick <jméno>");
  }
  if($unstick == "!unstick") {
      if($unstick_user != "") {
      $ts3->message("Odlepuji uživatele ".$unstick_user."...");
      try {$ts3->clientGetByName($unstick_user)->permRemove("b_client_is_sticky");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$unstick_user." byl odlepen"); }
      else $ts3->message("Uživatel ".$unstick_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !unstick <jméno>");
  }
   if($mute == "!mute") {
      if($mute_user != "") {
      $ts3->message("Umlčuji uživatele ".$mute_user."...");
      try {$ts3->clientGetByName($mute_user)->permAssign("i_client_talk_power", -10);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$mute_user." byl umlčen"); }
      else $ts3->message("Uživatel ".$mute_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !mute <jméno>");
  }
  if($unmute == "!unmute") {
      if($unmute_user != "") {
      $ts3->message("Odmlčuji uživatele ".$unmute_user."...");
      try {$ts3->clientGetByName($unmute_user)->permRemove("i_client_talk_power");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživatel ".$unmute_user." byl odmlčen"); }
      else $ts3->message("Uživatel ".$unmute_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !unmute <jméno>");
  }
   if($mutechat == "!chatmute") {
      if($mutechat_user != "") {
      $ts3->message("Zakazuji chat uživateli ".$mutechat_user."...");
      try {$ts3->clientGetByName($mutechat_user)->permAssign("b_client_server_textmessage_send", FALSE);$ts3->clientGetByName($mutechat_user)->permAssign("b_client_channel_textmessage_send", FALSE);}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživateli ".$mutechat_user." byl zakázán chat"); }
      else $ts3->message("Uživatel ".$mutechat_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !chatmute <jméno>");
  }
  if($unmutechat == "!chatunmute") {
      if($unmutechat_user != "") {
      $ts3->message("Povoluji chat uživateli ".$unmutechat_user."...");
      try {$ts3->clientGetByName($unmutechat_user)->permRemove("b_client_server_textmessage_send");$ts3->clientGetByName($unmutechat_user)->permRemove("b_client_channel_textmessage_send");}
      catch(TeamSpeak3_Exception $error) {$chyba = true;}
      if(!$chyba) {
          $ts3->message("Uživateli ".$unmutechat_user." byl povolen chat"); }
      else $ts3->message("Uživatel ".$unmutechat_user." nebyl nalezen");
      }
      else $ts3->message("Správné použití: !chatunmute <jméno>");
  }

}
}
}
if($chyba_spojeni) echo "spojeni preruseno, zkousim znovu   "; goto main;
?>
