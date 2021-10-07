<?php
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;


require 'vendor/autoload.php';

$urlonline = "https://www.leboncoin.fr/voitures/1995193394.htm";
$url= "https://www.leboncoin.fr/voitures/2045541271.htm";


function downloadAnnonce($url)
{
    $uri = 'https://api.leboncoin.fr';

    $matches = array();
    $pattern = "/\\/([0-9]+)\\.htm/s";
    preg_match($pattern, $url, $matches);
    /* echo('matches>>>>');
    var_dump($matches); */
    $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36',
        'Accept' => 'application/json',
        'Accept-Language' => 'en-US,en;q=0.9,fr-FR;q=0.8,fr;q=0.7,zh;q=0.6',
        'Accept-Encoding' => 'gzip, deflate, br',
        'DNT' => '1',
        'Cache-Control' => 'no-cache'
    ];
    
    $client = new Client(
        [
            'handler' => HandlerStack::create(new StreamHandler()),
            'cookies' => new FileCookieJar('cookies_lbc.json', true),
            'stream' => true,
            'headers' => $headers,
            'allow_redirects' => false,
            'base_uri' => $uri
        ]
    );

    try{
        $response = $client->get("finder/classified/{$matches[1]}");
        $annonce = json_decode($response->getBody()->getContents(),true);
        $httpCode = $response->getStatusCode();
        if ($httpCode == 200) {
            echo('get annonce >>>> still online'.PHP_EOL);
            //print_r($annonce);
            return $annonce;
        }
    } catch (TransferException $e) {
        PrintDebug::display("Network problem",true);
        throw $e;
    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            $httpCode = $e->getResponse()->getStatusCode(); 
            if ($httpCode == 410) {
                PrintDebug::display("Annonce désactivée", true);
                echo('sucess check >>>> annonce isnot online'.PHP_EOL);
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
        throw new Exception('Unknown error');
    }            
}

$nb_blocked = 0;
$i = 0;
while (true){
    
    try{
        $ans = downloadAnnonce($url);
    } catch(Exception $e) {
        PrintDebug::display($e->getMessage(), true);
        if ($e->getMessage() == 'Reconnection requise') {
            $nb_blocked++;
            echo('>>>>'.$nb_blocked);
        }
    }
    if($nb_blocked > 3 || $i > 2) {
        break;
    }
    $i ++;

}











