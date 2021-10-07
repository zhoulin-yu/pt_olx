<?php
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

require 'vendor/autoload.php';

$client = new Client(['base_uri' => 'http://httpbin.org/']);

// Initiate each request but do not block
$promises = [
    'image' => $client->getAsync('/image'),
    'png'   => $client->getAsync('/image/png'),
    'jpeg'  => $client->getAsync('/image/jpeg'),
    'webp'  => $client->getAsync('/image/webp')
];

// Wait for the requests to complete; throws a ConnectException
// if any of the requests fail
$responses = Promise\Utils::unwrap($promises);

// You can access each response using the key of the promise
echo $responses['image']->getHeader('Content-Length')[0];
echo $responses['png']->getHeader('Content-Length')[0];

// Wait for the requests to complete, even if some of them fail
$responses = Promise\Utils::settle($promises)->wait();

// Values returned above are wrapped in an array with 2 keys: "state" (either fulfilled or rejected) and "value" (contains the response)
echo $responses['image']['state'].PHP_EOL; // returns "fulfilled"
echo $responses['image']['value']->getHeader('Content-Length')[0]."\n";
echo $responses['png']['value']->getHeader('Content-Length')[0].PHP_EOL;