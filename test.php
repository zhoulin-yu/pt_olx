<?php

$a = array();
$b = $matches = [];

var_dump($a);
var_dump($b);

// Date de mise en ligne
$res["date_mise_ligne"] = '';
$createdTime = new DateTime("now");
$res["date_mise_ligne"] = $createdTime->format('Y-m-d H:i:s');


$url_car = "https://www.autoscout24.be/fr/lst/?sort=age&desc=1&custtype=P&ustate=N%2CU&size=20&page=1&lon=4.75789&lat=50.43501&zip=5150+Floreffe&zipr=1&cy=B&pricefrom=2000&atype=C&fc=31&qry=";
$url_moto = "https://www.autoscout24.be/fr/lst-moto/?sort=age&desc=1&custtype=P&ustate=N%2CU&size=20&page=5&cy=B&atype=B&fc=1&qry=&recommended_sorting_based_id=79925404-bc82-4363-88be-30a4c2898bd9&";
if (strpos($url_moto, 'lst-moto') !== false) {
    echo ">>moto";
} else {
    echo ">>>>car";
}
