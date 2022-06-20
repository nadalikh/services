#!/usr/bin/php
<?php
$map_receiversVoices = array();
$availableVoice = array();

$db = new mysqli("localhost", "root", "expecto-patronum1379", "app");
if($db->connect_error)
    die("we cannot connecto to database because: " . $db->connect_error);

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
    global $map_receiversVoices;
    $voiceInfos = array();
    //get voicemail infos
    getVoiceMailInfoFromFile($receiver, $voicemail, $voiceInfos);
    $sender = explode("-",explode("/",$voiceInfos[10])[1])[0];
    $date = explode("=", $voiceInfos[12])[1];
    $duration = intval(explode('=', $voiceInfos[17])[1]);
    $db->query("insert into voicemail (path, sender, receiver, date, duration) values ('$voicemail', '$sender', '$receiver','$date', '$duration')");
    //at the end $map_receiversVoices needs to be updated.
    getReceiversWithTheirVoicesMap();
}
//function getNewVoiceFromReceiver($reciver){
//
//}
function updateNewVoices(){
    global $map_receiversVoices;
    echo "map is :\n";
    var_dump($map_receiversVoices);
    $receivers = array();
    exec("ls /var/www/html/mySweetVoices/default/", $receivers);
    foreach ($receivers as $receiver) {
        $t = getVoicesForReceiver($receiver);
        foreach ($t as $voicemail) {
            if (!array_key_exists($receiver, $map_receiversVoices) || !array_key_exists($voicemail, $map_receiversVoices[$receiver])) {
//                echo "$voicemail is added\n";
                addVoiceMail($receiver, $voicemail);
            }
        }
    }
}
getReceiversWithTheirVoicesMap();

while(true){
    updateNewVoices();
    sleep(30);
}
