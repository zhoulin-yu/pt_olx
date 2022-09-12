<?php

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

require 'vendor/autoload.php';

function writeContent($str)
{
    $open = fopen("petitesannonce.html", "w");
    fwrite($open, $str);
    fclose($open);
}

$url = "https://www.petitesannonces.ch/a/6217742";

$ch = curl_init();
curl_setopt_array(
    $ch,
    [
        CURLOPT_URL => $url,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true
    ]
);
$httpBody = curl_exec($ch);
//var_dump($httpBody);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "http code is " . $httpCode . PHP_EOL;
curl_close($ch);

//$httpBody = file_get_contents("petitesannonce.html");


// Téléphone
$matches = [];
$pattern = '/innerHTML=\'([+0-9]+)\'/s';
preg_match($pattern, $httpBody, $matches);
var_dump($matches);
$jsonAd['telephonepresent'] = 0;
if (isset($matches[1])) {
    $jsonAd['telephone'] = strrev($matches[1]);
    $jsonAd['telephonepresent'] = 2;
}

var_dump($jsonAd);
/*
<tr>
<td class="small">T�l�phone:</td>
<td><span id="pi83690">032... </span><a href="#" title="Voir le num�ro"
        onClick="pi83690.innerHTML='6117674230'.split('').reverse().join('');this.style.display='none';return false;">Voir
        le num�ro</a></td>
</tr>
*/

/* $doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($httpBody); // load httpString to create an DOMDocument
libxml_use_internal_errors(false);
$titre = $doc->getElementsByTagName("title")[0]->textContent;

$data = $doc->getElementById("__NEXT_DATA__")->nodeValue; //get json content
$data_array = json_decode($data, true); //return array
$res = array();
$res["titre"] = $titre;


// prix
if (isset($data_array["props"]["pageProps"]["listingDetails"]["prices"]["public"]["priceRaw"])) {
    $res["prix"] = $data_array["props"]["pageProps"]["listingDetails"]["prices"]["public"]["priceRaw"];
}

// info de voitre: marque, modele, km, carburant
if (isset($data_array["props"]["pageProps"]["listingDetails"]["vehicle"])) {
    $res["marque"] = $data_array["props"]["pageProps"]["listingDetails"]["vehicle"]["make"];
    $res["modele"] = $data_array["props"]["pageProps"]["listingDetails"]["vehicle"]["model"];
    $res["km"] = $data_array["props"]["pageProps"]["listingDetails"]["vehicle"]["mileageInKmRaw"];
    $res["carburant"] = $data_array["props"]["pageProps"]["listingDetails"]["vehicle"]["fuelCategory"]["formatted"];
}

//annee
if (isset($data_array["props"]["pageProps"]["listingDetails"]["trackingParams"]["classified_year"])) {
    $res["annee"] = $data_array["props"]["pageProps"]["listingDetails"]["trackingParams"]["classified_year"];
}

// description
if (isset($data_array["props"]["pageProps"]["listingDetails"]["description"])) {
    $res["description"] = $data_array["props"]["pageProps"]["listingDetails"]["description"];
}

// ville et codepostal
$res["pays"] = "BE";
if (isset($data_array["props"]["pageProps"]["listingDetails"]["location"])) {
    $res["ville"] = $data_array["props"]["pageProps"]["listingDetails"]["location"]["city"];
    $res["codepostal"] = $data_array["props"]["pageProps"]["listingDetails"]["location"]["zip"];
}

// telephone
if (isset($data_array["props"]["pageProps"]["listingDetails"]["seller"]["phones"][0]["callTo"])) {
    $res['telephonepresent'] = 2;
    $res["telephone"] = $data_array["props"]["pageProps"]["listingDetails"]["seller"]["phones"][0]["callTo"];
}

// statut vendeur

if ($data_array["props"]["pageProps"]["listingDetails"]["seller"]["isDealer"] = "false") {
    $res['statut_vendeur_particulier'] = 1;
}


//photo
if (isset($data_array["props"]["pageProps"]["listingDetails"]["images"][0])) {
    $res['photo_url'] = $data_array["props"]["pageProps"]["listingDetails"]["images"][0];
}

var_dump($res); */

/*

if (isset($matches[1])) {
            $jsonAd['photo_url'] = $matches[1];
        }

'titre'
'pays'///
'codepostal'///
'ville'///
'date_mise_ligne'    $jsonAd['date_mise_ligne'] = date('Y-m-d H:i:00');
'prix'///
'km'///
'marque'////
'modele'///
'carburant'///
'photo_url'///
'statut_vendeur_particulier'= 1 (if personal, else = 0)///
'telephonepresent'///
'telephone'///

$toReturn = json_encode($jsonAd, JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        if (json_last_error() > 0) {
            PrintDebug::display(json_last_error_msg(), $this->debug_mode);
            return false;
        }

        return $toReturn;
*/
