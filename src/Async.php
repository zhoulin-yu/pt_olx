<?php

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

require 'vendor/autoload.php';

$url = "http://httpbin.org/get";
#$url = "https://www.leboncoin.fr/";

$client = new Client(['base_uri' => $url]);

$promise = $client->requestAsync('GET', $url);


$promise->then(
    function (ResponseInterface $res) {
        echo "code = ".$res->getStatusCode() . "\n";
    },
    function (RequestException $e) {
        echo "message".$e->getMessage() . "\n";
        echo $e->getRequest()->getMethod();
    }
);
