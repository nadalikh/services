<?php
$output = array();
exec("scp /var/www/html/mySweetVoices/default/620/INBOX root@51.77.106.237:/var/www/html/voipApp/public/voices", $output);
exec('echo expecto-patronum1379');
var_dump($output);

