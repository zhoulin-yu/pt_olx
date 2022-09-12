<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\WebDriverExpectedCondition as EC;

require 'vendor/autoload.php';

/* $serverUrl = 'http://localhost:4444';
$driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());
 */


putenv('WEBDRIVER_CHROME_DRIVER=C:\Program Files\Google\Chrome\Application\chromedriver.exe');

// Create an instance of ChromeOptions:
$chromeOptions = new ChromeOptions();
$chromeOptions->setExperimentalOption("excludeSwitches", ["enable-automation"]); //y
$chromeOptions->setExperimentalOption('useAutomationExtension', false);
$chromeOptions->addArguments(['userAgent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.82 Safari/537.36']);
$chromeOptions->addArguments(["disable-blink-features=AutomationControlled"]);




$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

$serverUrl = 'http://localhost:4444';

$driver = ChromeDriver::start($capabilities);
$url0 = "https://www.google.fr";
$url1 = "https://www.autoscout24.ch";
$url2 = "https://www.autoscout24.ch/de/d/bmw-m5-limousine-1999-occasion?backurl=%2F&topcar=true&vehid=9324196";
//$url = "https://www.google.fr";
//$url = "https://www.autoscout24.ch";


// ********************** AUTO FLOW *************************
// *** auto ***
$url_flow_json = "https://www.autoscout24.ch/webapp/v13/vehicles?yearfrom=2000&pricefrom=2000&loc=Genève&vehtyp=10";
$url_flow_json_1 = "https://www.autoscout24.ch/webapp/v13/vehicles?yearfrom=2000&pricefrom=2000&loc=Genève&vehtyp=10page=1";
$url_flow_json_2 = "https://www.autoscout24.ch/webapp/v13/vehicles?yearfrom=2000&pricefrom=2000&loc=Genève&vehtyp=10&page=2";

// *** utile ***
$url_flow_utile_json = "https://www.autoscout24.ch/webapp/v13/vehicles?yearfrom=2000&pricefrom=2000&loc=Genève&vehtyp=20";



// ***********"***********AUTO ANNONCE ************************
$url_simple = "https://www.autoscout24.ch/fr/9388216";
$url_annonce_complet = "https://www.autoscout24.ch/de/d/mini-mini-kleinwagen-2022-neues-fahrzeug?vehid=9390021";
$url_prive = "https://www.autoscout24.ch/fr/9342156";

$idAnnonce = '9260697';
$driver->get($url_simple);
$driver->wait()->until(
    EC::titleContains('TOYOTA')
);
$html = $driver->getPageSource();
$open = fopen("webdrive_content_auto_annonces.html", "a");
fwrite($open, $str);
fclose($open);


sleep(200);
$driver->close();
