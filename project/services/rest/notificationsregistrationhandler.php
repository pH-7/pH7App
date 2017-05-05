<?php

/**
* Code example, receives data from devices.
* @author KikApp PHP Generator
* @version Beta 1 build 0.1.41
*/

header('Content-Type: application/json;');

$inputJSON = file_get_contents('php://input');
$input= json_decode( $inputJSON, TRUE ); //convert JSON into array

register($inputJSON);

function register($text)
{
    $filename = "../PublicStorage/registrationHandler.log";
    $fh = fopen($filename, "a") or die("Could not open log file.");
    fwrite($fh, date("d-m-Y H:i")." - $text\n") or die("Could not write file!");
    fclose($fh);
}

?>
