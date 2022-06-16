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
    return $voices;

}

function getVoiceMailFromFile($reciver){
    $voiceInfos = array();
    exec("cat /var/www/html/mySweetVoices/default/");

}

function addVoiceMail($receiver, $voicemail){
    global $db;
    //get voicemail infos.


    $db->query("insert into voicemail (path, sender, reciver, date) values");
    //at the end $map_receiversVoices needs to be updated.
}
function getNewVoiceFromReceiver($reciver){

}
function getNewDirForRecivers(){
    global $map_receiversVoices;
    $receivers = array();
    exec("ls /var/www/html/mySweetVoices/default/", $receivers);
//    if(sizeof($receivers) != sizeof($map_receiversVoices))
//        foreach ($receivers as $receiver)
//            if(!array_key_exists($receiver, $map_receiversVoices))
//                foreach($map_receiversVoices[$receiver] as $voicemail)
//                    addVoiceMail($receiver,$voicemail);
    foreach ($receivers as $receiver)
        foreach (getVoicesForReceiver($receiver) as $voicemail)
            if(!array_key_exists($receiver, $map_receiversVoices) || !array_key_exists($voicemail, $map_receiversVoices[$receiver]))
                addVoiceMail($receiver, $voicemail);
}
getReceiversWithTheirVoicesMap();

while(true){
    sleep(3);
    getNewDirForRecivers();
//    foreach($map_receiversVoices as $voicesFromReceiver)
//        getNewVoiceFromReceiver($);

} 
