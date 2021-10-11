<?php

use Exception;

require 'vendor/autoload.php';

function writelog($str)
{
    $open=fopen("web_NoExist.txt","a" );
    fwrite($open,$str);
    fclose($open);
}

function create_token()
{
    /**
     * Generation d'un UUID aléatoire
     */
    $data['device_id'] = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
    $device = base64_encode(json_encode(['id' => $data['device_id']]));
    $data['device_token'] = "{$device}." . hash_hmac('sha1', $device, 'device');
    $data['grant_type'] = 'device';
    $data['scope'] = 'i2 read write v2';
    $data['client_id'] = '100015';
    $data['client_secret'] = '40305e47de43919714d2583fc9320a9e8f6a8001f30cd288396e7ced6d666540';

    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => "https://www.olx.pt/api/open/oauth/token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ]
    );
    $tokenContent = curl_exec($ch);
    $token = json_decode($tokenContent, true);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return $token;
}

function readJson()
{
    $json_string = file_get_contents('list_idannonce.json');
    $data = json_decode($json_string, true);
    return($data);
}

function testAnnonce_api($id)
{
    $token = create_token();
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => "https://www.olx.pt/api/v2/offers/{$id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token['access_token']}"]
        ]
    );
        $annonceContent = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        print_r($annonceContent);
        echo PHP_EOL;
        print_r($httpCode);
        if($httpCode = 200){
            return true;
        }
        if($httpCode = 410){
            return false;
        }
        if($httpCode == 403){
            throw new Exception('Reconnection requise');
        }
        throw new Exception('Unknow httpcode');

}

function testAnnonce($url)
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
            ]
        );
        $annonceContent = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $sign = 'swiper-container'; //container of car image
        $flag = strpos($annonceContent, $sign); //$annonceContent don't have this element if ad no longer available

        if($httpCode==200){
            if($flag != false) {
                return true;
            }
            return false; //web retiré par le vendeur
        } 
        if($httpCode == 403){
            throw new Exception('Reconnection requise');
        }
        if($httpCode == 410){
            return false;//web n'existe plus
        }
        PrintDebug::display(
            'Code de return inconnu: ' . $httpCode,
            true
        );
        return false;
    }
    function downloadAnnonce($url)
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
            ]
        );
        $annonceContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //writelog($annonceContent);
        /* echo(">>>>>>>>TESTcode".PHP_EOL);
        echo($httpCode);
        echo(">>>>>>>>TESTannonce".PHP_EOL);
        print_r($annonceContent); */
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $jsonAnnonce = ['telephonepresent' => 0];

        if ($httpCode === 404) {
            return false;
        }

        $data = [];

        $phone = '';
        if (boolval(preg_match('/"phone":true/s', $annonceContent))) {
            /**
             * Generation d'un UUID aléatoire
             */
            $data['device_id'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
            $device = base64_encode(json_encode(['id' => $data['device_id']]));
            $data['device_token'] = "{$device}." . hash_hmac('sha1', $device, 'device');
            $data['grant_type'] = 'device';
            $data['scope'] = 'i2 read write v2';
            $data['client_id'] = '100015';
            $data['client_secret'] = '40305e47de43919714d2583fc9320a9e8f6a8001f30cd288396e7ced6d666540';

            $ch = curl_init();
            curl_setopt_array(
                $ch,
                [
                    CURLOPT_URL => "https://www.olx.pt/api/open/oauth/token/",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($data)
                ]
            );
            $tokenContent = curl_exec($ch);
            $token = json_decode($tokenContent, true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            $pattern = "/ID: <!-- -->([0-9]+)/";
            $matches = array();
            preg_match($pattern, $annonceContent, $matches);
            $idannonce = $matches[1];

            $ch = curl_init();
            curl_setopt_array(
                $ch,
                [
                    CURLOPT_URL => "https://www.olx.pt/api/v1/offers/{$idannonce}/limited-phones/",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token['access_token']}"]
                ]
            );
            $phoneContent = curl_exec($ch);
            echo('Test>>>>phoneContent'.PHP_EOL);
            var_dump($phoneContent);
            $phone = json_decode($phoneContent, true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            try {
                $phone = substr(str_replace([' ', '-', '/', '(', ')'], '', $phone['data']['phones'][0]), -9);
                if (substr($phone, 0, 1) != "9") {
                    throw new Exception();
                }
            } catch (Throwable $e) {
                throw new Exception('Reconnection requise');
            }
        }

        $jsonAnnonce["telephonepresent"] = 0;
        if (strlen($phone) > 0) {
            $jsonAnnonce["telephonepresent"] = 2;
            $jsonAnnonce["telephone"] = $phone;
        }

        // Pays
        $jsonAnnonce["pays"] = "PT";

        // Titre
        $matches = array();
        $pattern = '/<h1[^>]*>([^<]+)<\/h1>/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["titre"] = trim($matches[1]);
        }

        // Code postal irrécupérable dans un premier temps, à récupérer via la ville

        // Ville
        $matches = array();
        $ville = null;
        $pattern = '/<address>[\n\s]+<p>([^<]*)/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $ville = trim($matches[1]);
        } else {
            $pattern = '/<div>(.+)<\/div><div class="qa-static-ad-map-container"><img/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $ville = trim(strip_tags($matches[1]));
            }
        }

        if (!$ville) {
            $matches = array();
            preg_match('/\"location\":{[^}]*\"pathName\":\"([^\"]+)\"}/', $annonceContent, $matches);
            if (isset($matches[1])) {
                $ville = trim($matches[1]);
            }
        }

        if ($ville) {
            // Il peut arriver qu'on ait des parenthèses dans notre résultat : on enlève
            $matches = array();
            $pattern = '/([^\(]+)\([^\)]+\)(.*)/s';
            preg_match($pattern, $ville, $matches);
            if (isset($matches[1])) {
                $ville = $matches[1] . $matches[2];
            }

            // Quartier, Ville, District
            $arr_ville = explode(", ", $ville);

            // Si taille 4, on prend les 3 derniers éléments
            if (sizeof($arr_ville) == 4) {
                $jsonAnnonce["ville3"] = trim($arr_ville[1]);
                $jsonAnnonce["ville2"] = trim($arr_ville[2]);
                $jsonAnnonce["ville1"] = trim($arr_ville[3]);
            } elseif (!isset($arr_ville[1])) {
                $jsonAnnonce["ville3"] = '';
                $jsonAnnonce["ville2"] = '';
                $jsonAnnonce["ville1"] = trim($arr_ville[0]);
            } elseif (!isset($arr_ville[2])) {
                $jsonAnnonce["ville3"] = '';
                $jsonAnnonce["ville2"] = trim($arr_ville[0]);
                $jsonAnnonce["ville1"] = trim($arr_ville[1]);
            } else {
                $jsonAnnonce["ville3"] = trim($arr_ville[0]);
                $jsonAnnonce["ville2"] = trim($arr_ville[1]);
                $jsonAnnonce["ville1"] = trim($arr_ville[2]);
            }
        }

        // Prix
        $matches = array();
        $pattern = '/pricelabel__value[^>]+>([^€]+)/s';
        preg_match($pattern, $annonceContent, $matches);
        if (!isset($matches[1])) {
            $matches = array();
            $pattern = '/"regularPrice":{"value":([0-9]+)/s';
            preg_match($pattern, $annonceContent, $matches);
        }
        if (!isset($matches[1])) {
            $matches = array();
            $pattern = '/name="description" content="([0-9]+) €:/s';
            preg_match($pattern, $annonceContent, $matches);
        }
        $jsonAnnonce["prix"] = str_replace(".", "", trim($matches[1]));

        // Année
        $matches = array();
        $pattern = '/<span class="offer-details__name">Ano<\/span>[\n\s]+<strong class="offer-details__value">([0-9]{4})/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["annee"] = $matches[1];
        } else {
            $pattern = '/Ano: ([0-9]{4})/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $jsonAnnonce["annee"] = trim($matches[1]);
            }
        }

        // Carburant
        $matches = array();
        $pattern = '/<span class="offer-details__name">Combustível<\/span>[\n\s]+<strong class="offer-details__value">([^<]+)/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["carburant"] = $matches[1];
        } else {
            $pattern = '/Combustível: ([^<]+)/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $jsonAnnonce["carburant"] = trim($matches[1]);
            }
        }

        // Marque
        $matches = array();
        $pattern = '/"cat_l2":"([ \-a-z]+)"/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["marque"] = $matches[1];
        } else {
            $pattern = '/<a[^>]+>([^-]+)- [^<]+<\/a><\/li><\/ol>/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $jsonAnnonce["marque"] = trim($matches[1]);
            }
        }

        // Modèle
        $matches = array();
        $pattern = '/<span class="offer-details__name">Modelo<\/span>[\n\s]+<strong class="offer-details__value">([^<]+)/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["modele"] = $matches[1];
        } else {
            $pattern = '/Modelo: ([^<]+)/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $jsonAnnonce["modele"] = trim($matches[1]);
            }
        }

        // Km
        $matches = array();
        $pattern = '/<span class="offer-details__name">Quilómetros<\/span>[\n\s]+<strong class="offer-details__value">([.0-9<]+)/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["km"] = str_replace(".", "", $matches[1]);
        } else {
            $pattern = '/Quilómetros: ([^ ]+) km/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[1])) {
                $jsonAnnonce["km"] = filter_var($matches[1], FILTER_SANITIZE_NUMBER_INT);
            }
        }

        // Date de mise en ligne
        $arr_portugese_months = array(
            "Jan" => "01",
            "Fev" => "02",
            "Mar" => "03",
            "Abr" => "04",
            "Mai" => "05",
            "Jun" => "06",
            "Jul" => "07",
            "Ago" => "08",
            "Set" => "09",
            "Out" => "10",
            "Nov" => "11",
            "Dez" => "12"
        );
        $matches = array();
        $pattern = '/às ([0-9]{2}):([0-9]{2}), ([0-9]{1,2}) ([^ ]+) ([0-9]{4})/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jour = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            $mois = substr($matches[4], 0, 3);
            $annee = $matches[5];
            $heure = $matches[1];
            $minute = $matches[2];

            $jsonAnnonce["date_mise_ligne"] = $annee . "-" . $arr_portugese_months[$mois] . "-" . $jour . " " .
                    $heure . ":" . $minute . ":00";
        } else {
            $pattern = '/<span data-cy="ad-posted-at"[^>]+>([^>]+)<\/span>/s';
            preg_match($pattern, $annonceContent, $matches);

            $submatches = [];
            $pattern = '/([0-9]{1,2}) de ([^ ]+) de ([0-9]{4})/s';
            preg_match($pattern, $matches[1], $submatches);
            if (isset($submatches[1])) {
                $month = $arr_portugese_months[ucfirst(substr($submatches[2], 0, 3))];
                $jsonAnnonce["date_mise_ligne"] = "{$submatches[3]}-{$month}-{$submatches[1]} 00:00:00";
            } else {
                $pattern = '/Hoje às ([0-9]{2}):([0-9]{2})/s';
                preg_match($pattern, $matches[1], $submatches);
                if (isset($submatches[1])) {
                    $jsonAnnonce["date_mise_ligne"] = date('Y-m-d') . " {$submatches[1]}:{$submatches[2]}:00";
                }
            }
        }

        // Statut pro
        $matches = array();
        $jsonAnnonce["statut_vendeur_particulier"] = 0;
        $pattern = '/<span class="offer-details__name">Anunciante<\/span>[\n\s]+<strong class="offer-details__value">Particular/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[0])) {
            $jsonAnnonce["statut_vendeur_particulier"] = 1;
        } else {
            $pattern = '/Particular<\/p>/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[0])) {
                $jsonAnnonce["statut_vendeur_particulier"] = 1;
            }
        }

        // User
        $matches = array();
        $pattern = '/<div class="quickcontact__user-name">([^<]+)<\/div>/s';
        preg_match($pattern, $annonceContent, $matches);
        if (!isset($matches[1])) {
            $matches = array();
            $pattern = '/"user":{"id":[0-9]+,"name":"([^"]+)"/s';
            preg_match($pattern, $annonceContent, $matches);
        }
        $jsonAnnonce["nom"] = substr(trim($matches[1]), 0, 32);

        // Image
        $matches = array();
        $pattern = '/"ad_img":"([^"]+)"/s';
        preg_match($pattern, $annonceContent, $matches);
        if (isset($matches[1])) {
            $jsonAnnonce["photo_url"] = $matches[1];
        } else {
            $pattern = '/https:\/\/ireland.apollo.olxcdn.com:443\/v1\/files\/[^\/]+\/image/s';
            preg_match($pattern, $annonceContent, $matches);
            if (isset($matches[0])) {
                $jsonAnnonce["photo_url"] = $matches[0];
            }
        }

        // Description
        $matches = array();
        $pattern = '/<div class="clr lheight20 large" id="textContent">((.|\n)*?)<\/div>/s';
        preg_match($pattern, $annonceContent, $matches);
        if (!isset($matches[1])) {
            $matches = array();
            $pattern = '/name="description" content="([^"]+)"/s';
            preg_match($pattern, $annonceContent, $matches);
        }
        $jsonAnnonce["description"] = trim($matches[1]);

        $toReturn = json_encode($jsonAnnonce, JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        if (json_last_error() > 0) {
            PrintDebug::display(json_last_error_msg(), true);
            return false;
        }

        return $toReturn;
    }
$u1 = "https://www.olx.pt/d/anuncio/vende-se-golf-1999-IDGJ6nT.html#d447c7e096"; //hcode:200 => this ad is no longer available, the seller has already finished this ad
$u2 = "https://www.olx.pt/d/anuncio/vw-sharan-2-0-tdi-IDGJc91.html#2d0ff97ec2";//410 => the page doesn't exist
$u3 = "https://www.olx.pt/d/anuncio/ford-fiesta-pouqussimos-kilometros-IDGHT10.html#afe7453ff5;promoted";//web normal
$u4 = "https://www.olx.pt/d/anuncio/audi-a4-1-9-tdi-130-cv-IDGEBr4.html#2d0ff97ec2";//hcode:200 => this ad is no longer available, the seller has already finished this ad
$u5 = "https://www.olx.pt/d/anuncio/renault-megane-1-5dci-105cv-gps-07-IDGECfu.html#2d0ff97ec2";
$u6 = "https://www.olx.pt/d/anuncio/volkswagen-passat-IDGEDNr.html#2d0ff97ec2";
$u7 = "https://www.olx.pt/d/anuncio/renault-megane-1-5-IDGEw6p.html#820fe2e299;promoted";//normal
$tu = 'https://www.olx.pt/d/anuncio/fiat-punto-evo-1-2-2011-IDGA0s1.html#2d0ff97ec2';//在后台能正常下载，带tel。 实测无法下载号码。页面号码与后台不符。


testAnnonce_api('629236125');

/* $list_id = readJson();
$i = 2;
$list_id_still_online = array();
array_push($list_id_still_online,'0','1');


foreach($list_id as $id)
{
    if(testAnnonce_api($id)){
        echo $i.' is still online'.PHP_EOL;
        echo $id.PHP_EOL;
        array_push($list_id_still_online,$url);
    }
    $i ++;
}
fputcsv('list_id_still_online.csv',$list_id_still_online); */

$tu1 = 'https://www.olx.pt/d/anuncio/fiat-punto-evo-1-2-2011-IDGA0s1.html#2d0ff97ec2'; //"telephonepresent":0' need to login to contact 
$tu2 = 'https://www.olx.pt/d/anuncio/skoda-fabia-break-IDGuZBq.html#7e43c34482';
//telephone available on web, srapper cann't get it
