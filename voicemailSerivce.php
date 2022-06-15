#!/usr/bin/php
<?php
$db = new mysqli("localhost", "root", "expecto-patronum1379", "app");
if($db->connect_error)
    die("we cannot connecto to database because: " . $db->connect_error);

function getExtensionDirectoryWhichHasVoicemail(){
    global $db;
    $db->query("");

}

while(true){
    sleep(3);
    exec("ls ");
} 
