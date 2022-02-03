<?php

require 'vendor/autoload.php';

function writelog($str)
{
    $open = fopen("web_content.html", "a");
    fwrite($open, $str);
    fclose($open);
}



function testAnnonce($annonce)
{
    $url = $annonce["url"];
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
    if (curl_errno($ch)) {
        PrintDebug::display(curl_error($ch), $this->debug_mode);
    }
    curl_close($ch);

    // Test code retour >400 sinon on analyse le code renvoyé pour voir si l'annonce est toujours dispo
    if ($httpCode > 400 && strpos($httpBody, "<a href=\"#car-details\"") === false) {
        return false;
    }

    return true;
}

function downloadFlow($url)
{
    // Récupération du code source
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


    //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        PrintDebug::display(curl_error($ch), $this->debug_mode);
    }
    curl_close($ch);

    $jsonAds = $matches = [];
    $pattern = '/<a data-item-name="detail-page-link" href="([^"]+)">/s';
    preg_match_all($pattern, $httpBody, $matches);
    if (!isset($matches[0]) || sizeof($matches[0]) < 0) {
        return false;
    }

    foreach ($matches[0] as $key => $value) {
        $submatch = array();
        preg_match(
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/s',
            $matches[1][$key],
            $submatch
        );
        $jsonAds[] = ['id' => $submatch[0], 'url' => baseUrl() . $matches[1][$key]];
    }

    return json_encode(
        $jsonAds,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
    );
}


function downloadAnnonce($url)
{
    /* $ch = curl_init();
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
    if (curl_errno($ch)) {
        PrintDebug::display(curl_error($ch), $this->debug_mode);
    }

    writelog($httpBody);
    curl_close($ch);

    if ($httpCode == 404 && strpos($httpBody, '<a href=\"#car-details\"') === false) {
        return false;
    }

    // Si on est bloqué : reconnexion requise
    if (strpos($httpBody, 'No hay conexi') !== false) {
        throw new \Exception('Reconnection requise');
    } */

    $httpBody = file_get_contents("web_content.html");
    $doc = new DOMDocument();
    $doc->loadHTMLFile("web_content.html");

    #__next > div > main > div.css-o5c6q6 > div.css-11y8sgd > div.css-12ipdh9 > div.css-1m1xare > h1 > div.css-11siofd.errr7t01 > span.css-1mhe8d.errr7t00
    //*[@id="__next"]/div/main/div[5]/div[2]/div[1]/div[2]/h1/div[1]/span[2]



    // Price evaluation
    $priceEvaluation = '';
    $jsonAd = $matches = [];
    $pattern = '/<div id="peTrackingParams" data-error="false" data-asking-price="([^"]+)" data-category="([^"]+)">/s';
    preg_match($pattern, $httpBody, $matches);
    if (!empty($matches)) {
        $priceEvaluation = priceEvaluation($matches[2]);
    }
    //<div data-type="title">

    // Titre
    $matches = [];
    $pattern = '/<div data-type=\"title\">([^<]+)<\/div>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['titre'] = $priceEvaluation . $matches[1];
    }

    // Pays
    $jsonAd['pays'] = country();

    // Code postal + ville
    $matches = [];
    $pattern = '/<div data-item-name=\"vendor-contact-city\" class=\"sc-grid-col-12\">([0-9]{0,6})\s?([^<]+)<\/div>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['codepostal'] = contactPostalCode($matches[1]);
        $jsonAd['ville'] = $matches[2];
    }

    // Date de mise en ligne
    $matches = [];
    $pattern = '/<dt>Available from<\/dt>\n<dd>([0-9]{2})\/([0-9]{2})\/([0-9]{2})<\/dd>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['date_mise_ligne'] = "20{$matches[3]}-{$matches[2]}-{$matches[1]} 00:00:00";
    }
    if (!isset($jsonAd['date_mise_ligne'])) {
        $jsonAd['date_mise_ligne'] = date('Y-m-d H:i:00');
    }

    // Prix
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_price": "([0-9]+)"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd["prix"] = $matches[1];
    }




    // Kilométrage
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_mileage": "([0-9]+)"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['km'] = $matches[1];
    }

    // Année
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_year": "([0-9]{4})"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['annee'] = $matches[1];
    }

    // Marque
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_makeTxt": "([^"]+)"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['marque'] = $matches[1];
    }

    // Modèle
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_modelTxt": "([^"]+)"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['modele'] = $matches[1];
    }

    // Carburant
    $matches = [];
    $pattern = '/<as24-tracking type="gtm" action="set" as24-tracking-value=\'{"classified_fueltype": "([A-Z0-9])"}\'><\/as24-tracking>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['carburant'] = fuelType($matches[1]);
    }

    // Image
    $matches = [];
    $pattern = '/<div class="as24-carousel__container" role="container">\n<div class="as24-carousel__item">\n<div class="gallery-picture sc-lazy-image">\n<img class="gallery-picture__image" src="([^\"]+)" (data-fullscreen-src="[^\"]+"\s)?alt="[^\"]+" title="[^\"]+">\n<\/div>/s';
    preg_match($pattern, $httpBody, $matches);
    if (isset($matches[1])) {
        $jsonAd['photo_url'] = $matches[1];
    } else {
        $matches = [];
        $pattern = '/<meta property="og:image" content="([^"]+)">/s';
        preg_match($pattern, $httpBody, $matches);
        if (isset($matches[1])) {
            $jsonAd['photo_url'] = $matches[1];
        }
    }

    // Récupération du statut pro
    if (strpos($httpBody, '"classified_customer_type": "P"') !== false) {
        $jsonAd['statut_vendeur_particulier'] = 1;
    } else {
        $jsonAd['statut_vendeur_particulier'] = 0;
    }

    // Téléphone
    $matches = [];
    $pattern = '/<a href=\"tel:([+0-9]+)\" data-type=\"callLink\"/s';
    preg_match($pattern, $httpBody, $matches);
    $jsonAd['telephonepresent'] = 0;
    if (isset($matches[1])) {
        try {
            echo ">>>tel is " . $matches[1];
            $utils = PhoneNumberUtil::getInstance();
            $jsonAd['telephone'] = $utils->formatNumberForMobileDialing(
                $utils->parse($matches[1], country()),
                country(),
                false
            );
            $jsonAd['telephonepresent'] = 2;
        } catch (NumberParseException $e) {
        }
    }

    // Description (qui peut être vide)
    $httpBody = '';
    if (strpos($httpBody, '<div data-type=\"description\">') !== false) {
        $httpBody = substr(
            $httpBody,
            strpos($httpBody, '<div data-type=\"description\">') + strlen('<div data-type=\"description\">')
        );
        $httpBody = substr($httpBody, 0, strpos($httpBody, '</div>'));
    }

    $toReturn = json_encode($jsonAd, JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    if (json_last_error() > 0) {
        PrintDebug::display(json_last_error_msg(), $this->debug_mode);
        return false;
    }

    return $toReturn;
}


function baseUrl()
{
    return 'https://www.autoscout24.be';
}

function country()
{
    return 'BE';
}

function priceEvaluation($priceEvaluation)
{
    switch ($priceEvaluation) {
        case 'TopPrice':
            return '[EXCELLENTE OFFRE] - ';
        case 'GoodPrice':
            return '[BONNE OFFRE] - ';
        case 'FairPrice':
            return '[OFFRE CORRECTE] - ';
    };
}

function fuelType($fuelType)
{
    switch ($fuelType) {
        case 'B':
            return 'Essence';
        case 'D':
            return 'Diesel';
        case 'E':
            return 'Electrique';
        case 'L':
            return 'GPL';
        case 'C':
            return 'GNV';
        case 'H':
            return 'Hydrogène';
        case 'M':
            return 'Ethanol';
        case '2':
            return 'Electrique/Essence';
        case '3':
            return 'Electrique/Diesel';
        default:
            return 'Autre';
    }
}

function contactPostalCode($postalCode)
{
    return "B{$postalCode}";
}


$u0 = "https://www.autoscout24.fr/offres/mercedes-benz-b-200-classe-cdi-blueefficiency-sport-diesel-blanc-cd2eedb6-1b24-4e5e-b0f6-c58aeadc90f0?source=list_searchresults"; //web normal

$res = downloadAnnonce($u0);
print_r($res);
