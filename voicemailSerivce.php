#!/usr/bin/php
<?php
$map_receiversVoices = array();
$availableVoice = array();
date_default_timezone_set("Asia/Tehran");


$db = new mysqli("localhost", "root", "expecto-patronum1379", "app");
if($db->connect_error)
    die("we cannot connecto to database because: " . $db->connect_error);

function sendTelVoicemail($extension, $voicemail, $date, $duration, $receiver, $sender){
    $ch = curl_init();
    curl_setopt_array(
        $ch, array(
        CURLOPT_URL => 'https://nkhpro.ir:88/voicemail.php?extension='.$extension.'&file='.$voicemail.'&date='.urlencode($date).'&duration='.$duration.'&receiver='.$receiver.'&sender='.$sender,
        CURLOPT_RETURNTRANSFER => true
    ));
    $output = curl_exec($ch);
}


function getReceiversWithTheirVoicesMap(){
    global $db;
    global $map_receiversVoices;
    $map_receiversVoices = array();
    $res = $db->query("select receiver  from voicemail  group by receiver");
    while($record = $res->fetch_assoc()) {
        $receiver = $record['receiver'];
        $voiceResult = $db->query("select * from voicemail where receiver ='$receiver' ");
        $voicesForSpecificReceiver = array();
        while ($voicesForSpecificReceiver[] = $voiceResult->fetch_assoc())
            continue;
        $map_receiversVoices[$receiver] = $voicesForSpecificReceiver;
    }
}

function getVoicesForReceiver($receiverCid){
    $voices = array();
    exec("ls /var/www/html/mySweetVoices/default/".$receiverCid."/INBOX/*.wav", $voices);
    foreach ($voices as &$voice)
        $voice = basename($voice);
//checked
    return $voices;

}

function getVoiceMailInfoFromFile(&$receiverCid, &$voicemail, &$voiceInfos){
    exec("cat /var/www/html/mySweetVoices/default/".$receiverCid."/INBOX/" .
        explode('.', $voicemail)[0].
        ".txt",
    $voiceInfos);
    return $voiceInfos;
}

function addVoiceMail($receiver, $voicemail){
    global $db;
    $voiceInfos = array();
    //get voicemail infos
    getVoiceMailInfoFromFile($receiver, $voicemail, $voiceInfos);
    $sender = explode("-",explode("/",$voiceInfos[10])[1])[0];
//    $date = explode("=", $voiceInfos[12])[1];
    $date = date("Y-m-d H:i:s");

    $duration = intval(explode('=', $voiceInfos[17])[1]);
    $db->query("insert into voicemail (path, sender, receiver, date, duration) values ('$voicemail', '$sender', '$receiver','$date', '$duration')");
    exec('sshpass -p "expecto-patronum1379" rsync -r /var/www/html/mySweetVoices/default/'.$receiver.'/INBOX/'.$voicemail.' root@51.77.106.237:/var/www/html/voipApp/public/voices/'.$receiver.'/');
    sendTelVoicemail($receiver, $voicemail, $date, $duration, $receiver, $sender);
//    sendTelVoicemail($sender, $voicemail);
    //at the end $map_receiversVoices needs to be updated.
    getReceiversWithTheirVoicesMap();
}

function checkExistedVoiceForSpecificReceiverInMap($reciver, $voicemail){
    global $map_receiversVoices;
    foreach($map_receiversVoices[$reciver] as $voice)
        if (in_array($voicemail, $voice))
            return true;
    return false;
}
function updateNewVoices(){
    global $map_receiversVoices;
    $receivers = array();
    exec("ls /var/www/html/mySweetVoices/default/", $receivers);
    foreach ($receivers as $receiver) {
        $t = getVoicesForReceiver($receiver);
        foreach ($t as $voicemail)
            if (!array_key_exists($receiver, $map_receiversVoices) || !checkExistedVoiceForSpecificReceiverInMap($receiver, $voicemail)) {
                echo "$voicemail is added\n";
                addVoiceMail($receiver, $voicemail);
            }
    }
    getReceiversWithTheirVoicesMap();
}
getReceiversWithTheirVoicesMap();

while(true){
    updateNewVoices();
    sleep(1);
}
