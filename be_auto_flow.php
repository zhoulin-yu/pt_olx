<?php

use DOMDocument;

function writelog($str)
{
    $open = fopen("content_moto.html", "a");
    fwrite($open, $str);
    fclose($open);
}
function downloadContent($url)
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    if (curl_errno($ch)) {
        PrintDebug::display(curl_error($ch), true);
    }
    curl_close($ch);

    echo ($httpCode . PHP_EOL);
    writelog($httpBody);
}

function parseContent()
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);

    $doc->loadhtml($httpBody); // load httpString to create an DOMDocument
    //$doc->loadHTMLFile("content.html");
    libxml_use_internal_errors(false);
    //$data = $doc->getElementById("__NEXT_DATA__"); //get json content
    $data = $doc->getElementById("__NEXT_DATA__")->nodeValue; //get json content


    $data_array = json_decode($data, true); //return array
    $jsonAds = array();


    if (isset($data_array["props"]["pageProps"]["listings"])) {
        foreach ($data_array["props"]["pageProps"]["listings"] as $key => $value) {
            $jsonAds[] = ['id' => $value["id"], 'url' => 'https://www.autoscout24.be' . $value["url"]];
        }
    }

    var_dump($jsonAds);
}
function downloadFlowMoto($url)
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
        PrintDebug::display(curl_error($ch), true);
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
        $jsonAds[] = ['id' => $submatch[0], 'url' => "https://www.autoscout24.be/" . $matches[1][$key]];
    }

    var_dump($jsonAds);

    return json_encode(
        $jsonAds,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
    );
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    if (curl_errno($ch)) {
        PrintDebug::display(curl_error($ch), true);
    }
    curl_close($ch);

    echo ($httpCode . PHP_EOL);
    writelog($httpBody);

    // check moto or car
    if (strpos($url, 'lst-moto') !== false) {
        echo ">>moto";
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
            $jsonAds[] = ['id' => $submatch[0], 'url' => 'https://www.autoscout24.be' . $matches[1][$key]];
        }
    } else {
        echo ">>>>car";
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadhtml($httpBody); // load httpString to create an DOMDocument
        //$doc->loadHTMLFile("content.html");
        libxml_use_internal_errors(false);
        //$data = $doc->getElementById("__NEXT_DATA__"); //get json content
        $data = $doc->getElementById("__NEXT_DATA__")->nodeValue; //get json content


        $data_array = json_decode($data, true); //return array
        $jsonAds = array();


        if (isset($data_array["props"]["pageProps"]["listings"])) {
            foreach ($data_array["props"]["pageProps"]["listings"] as $key => $value) {
                $jsonAds[] = ['id' => $value["id"], 'url' => 'https://www.autoscout24.be' . $value["url"]];
            }
        }
    }

    var_dump($jsonAds);

    return json_encode(
        $jsonAds,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
    );
}


$url_car = "https://www.autoscout24.be/fr/lst/?sort=age&desc=1&custtype=P&ustate=N%2CU&size=20&page=1&lon=4.75789&lat=50.43501&zip=5150+Floreffe&zipr=1&cy=B&pricefrom=2000&atype=C&fc=31&qry=";
$url_moto = "https://www.autoscout24.be/fr/lst-moto/?sort=age&desc=1&custtype=P&ustate=N%2CU&size=20&page=5&cy=B&atype=B&fc=1&qry=&recommended_sorting_based_id=79925404-bc82-4363-88be-30a4c2898bd9&";

downloadFlow($url_moto);
var_dump($res);
