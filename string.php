<?php
/* $str = 'abcdefg des ehij k';
$x = 'd';
$c_str = ucwords($str);
var_dump($c_str);

$json = file_get_contents('list_cat.json',false);
$array = json_decode($json, true);

foreach( $array as $key => $value)
 {
    echo '"'.$key.'"'.' => "'.$value.'",'.PHP_EOL;
 }   
print_r($array);

if (array_key_exists('7707',$array)) {
   echo 'yes';
} else {echo ' no';} */

$created_time = "2021-10-12T15:36:15+01:00";
$ts = strtotime($created_time);
echo($ts);