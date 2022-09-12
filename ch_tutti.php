<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

require 'vendor/autoload.php';

function downloadAnnonce($idAnnonce)
{
    $uri = 'https://www.autoscout24.ch';

    $headers = [
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:90.0) Gecko/20100101 Firefox/90.0',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        'Accept-Encoding' => 'gzip, deflate, br',
        'DNT' => '1',
        'Cache-Control' => 'no-cache'
    ];


    $client = new Client(
        [
            'handler' => HandlerStack::create(new StreamHandler()),
            //'cookies' => new FileCookieJar('cookies_auto.json', true),
            'cookies' => new FileCookieJar('cookies_auto.json', true),
            'stream' => true,
            'headers' => $headers,
            'allow_redirects' => true,
            'base_uri' => $uri
        ]
    );

    try {
        //$response = $client->get("/{$idAnnonce}");
        $response = $client->request('GET', "/{$idAnnonce}");
        $annonce = json_decode($response->getBody()->getContents(), true);
        $httpCode = $response->getStatusCode();
    } catch (ConnectException $e) {
        PrintDebug::display("Network problem", true);
        throw $e;
    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            $httpCode = $e->getResponse()->getStatusCode();
            if ($httpCode == 410) {
                PrintDebug::display("Annonce désactivée", true);
                return false;
            }
            if ($httpCode == 403) {
                throw new Exception('Reconnection requise');
            }
            PrintDebug::display(
                'Code de return inconnu: ' . $httpCode,
                true
            );
        }
        throw new Exception('Unknow error');
    }
    var_dump($httpCode);
    echo PHP_EOL;
    var_dump((string)$response->getBody());
    echo PHP_EOL;
    var_dump($response->getBody()->getContents());
}




$url = "https://www.carforyou.ch/en/auto/suv/jeep/wrangler/20-turbo-80th-anniversary-2719914";
$idAnnonce = '9260697';
downloadAnnonce($idAnnonce);
