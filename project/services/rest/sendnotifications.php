<?php

/**
* Code example to send notifications.
* @author KikApp PHP Generator
* @version Beta 1 build 0.1.41
*/

header('Content-Type: application/json;');

define( 'API_KEY', 'YOUR VALUE API KEY' ); //Your  Sender API Key.

//Token devices.
$registrationIds = array("APA91bGswEoDfcofepos_j9m-jZH4ZyjxWufGA13cd_2UdiXYCgsxO1MqaKP3THJreSWhA1ZHNy6RohReOc6DpkvS1vIZkdqoo32GS5uOVdA9avcnT9GAUcFaPU4XImGPsHpbxnrVvVn");

$msg = array
(
    'payload'     => 'This is your message', //Your message
    'action'    => ''
);

$fields = array
(
    'registration_ids'     => $registrationIds,
    'data'            => $msg
);

$headers = array
(
    'Authorization: key=' . API_KEY,
    'Content-Type: application/json'
);

$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );

echo $result;

?>
