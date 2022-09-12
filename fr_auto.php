<?php

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;


function parseweb()
{
    #$httpBody = file_get_contents("fr_auto_web_content.html");
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    #$dom->loadHTML($httpBody); // load httpString to create an DOMDocument
    $dom->loadHTMLFile("fr_auto_flow.html.html");
    libxml_use_internal_errors(false);
    $links = $dom->getElementsByTagName('a');
    print(sizeof($links));
    echo PHP_EOL;
    $url_base = "https://www.autoscout24.fr";
    $urls = [];
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if (strpos($href, "offres")) {
            $urls[] = $url_base . $href;
        }
    }
    print(sizeof($urls));
    var_dump($urls);
}

function parseweb2()
{
    #$httpBody = file_get_contents("fr_auto_web_content.html");
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    #$dom->loadHTML($httpBody); // load httpString to create an DOMDocument
    $dom->loadHTMLFile("fr_auto_flow.html");
    libxml_use_internal_errors(false);
    $data = $dom->getElementById("__NEXT_DATA__")->nodeValue; //get json content
    $data_array = json_decode($data, true); //return array

    $jsonAds = array(); //results list

    if (isset($data_array["props"]["pageProps"]["listings"])) {
        foreach ($data_array["props"]["pageProps"]["listings"] as $value) {
            $jsonAds[] = ['id' => $value["id"], 'url' => 'https://www.autoscout24.fr' . $value["url"]];
        }
    }
    var_dump($jsonAds);
}


function downloadAnnonce($url)
{

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
    print($httpCode);
    echo PHP_EOL;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    #$dom->loadHTML($httpBody); // load httpString to create an DOMDocument
    $dom->loadHTMLFile("fr_auto_web.html");
    libxml_use_internal_errors(false);

    $data = $dom->getElementById("__NEXT_DATA__")->nodeValue; //get json content
    $data_array = json_decode($data, true); //return array
    $res = array();

    // titre
    $res["titre"] = $dom->getElementsByTagName("title")[0]->textContent;

    // Date de mise en ligne
    $res["date_mise_ligne"] = '';
    $createdTime = new DateTime("now");
    $res["date_mise_ligne"] = $createdTime->format('Y-m-d H:i:s');

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
    $res["pays"] = "FR";
    if (isset($data_array["props"]["pageProps"]["listingDetails"]["location"])) {
        $res["ville"] = $data_array["props"]["pageProps"]["listingDetails"]["location"]["city"];
        $res["codepostal"] = $data_array["props"]["pageProps"]["listingDetails"]["location"]["zip"];
    }
    // telephone
    /* $res['telephonepresent'] = 0;
    if (isset($data_array["props"]["pageProps"]["listingDetails"]["seller"]["phones"][0]["callTo"])) {
        try {
            $utils = PhoneNumberUtil::getInstance();
            $res['telephone'] = $utils->formatNumberForMobileDialing(
                $utils->parse(
                    $data_array["props"]["pageProps"]["listingDetails"]["seller"]["phones"][0]["callTo"],
                    'FR'
                ),
                'FR',
                false
            );
            $res['telephonepresent'] = 2;
        } catch (NumberParseException $e) {
        }
    } */
    $res["telephone"] = $data_array["props"]["pageProps"]["listingDetails"]["seller"]["phones"][0]["callTo"];

    //photo
    if (isset($data_array["props"]["pageProps"]["listingDetails"]["images"][0])) {
        $res['photo_url'] = $data_array["props"]["pageProps"]["listingDetails"]["images"][0];
    }

    // statut vendeur

    if ($data_array["props"]["pageProps"]["listingDetails"]["seller"]["isDealer"] = "false") {
        $res['statut_vendeur_particulier'] = 1;
    }


    var_dump($res);
}


$url = "https://www.autoscout24.fr/offres/bmw-118-serie-1-f40-05-2019-140-ch-m-sport-essence-blanc-e70e8085-c761-4fc0-8e4a-b89dc5da489f";

downloadAnnonce($url);
# parseweb2();
