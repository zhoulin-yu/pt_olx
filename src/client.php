<?php

require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$url = "http://httpbin.org/get";
$url1 = "https://www.leboncoin.fr/";
$url2 = "https://www.coursera.org/";
$client = new Client(['base_uri' => $url2]);
$response = $client->get($url2);


/* $request = new Request('PUT', 'http://httpbin.org/put');
$response2 = $client->send($request, ['timeout => 2']);
print_r($response2); */

$code = $response->getStatusCode(); 
$reason = $response->getReasonPhrase(); 

echo("code is ".$code);
echo("reason is ".$reason);