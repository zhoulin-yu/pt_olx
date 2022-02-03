<?php

require 'vendor/autoload.php';

function writelog($str)
{
    $open = fopen("web_content_moto.json", "a");
    fwrite($open, $str);
    fclose($open);
}

$url = "https://www.autoscout24.be/fr/offres/yamaha-fjr-1300-fjr-1300-as-essence-gris-e96a7bac-cd2d-4e1d-8987-cbb4ae434d3d?source=list_searchresults&cldtidx=1&sort=age&lastSeenGuidPresent=true&cldtsrc=listPage";

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

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "http code is " . $httpCode . PHP_EOL;
curl_close($ch);

$doc = new DOMDocument();
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

var_dump($res);

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
