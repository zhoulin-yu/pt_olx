<?php

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

function testAnnonce_api($idAnnonce)
{
    $token = create_token();
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => "https://www.olx.pt/api/v2/offers/{$idAnnonce}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token['access_token']}"]
        ]
    );
        $annonceContent = curl_exec($ch);
        file_put_contents('annonce_content_private_seller.json',$annonceContent);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo PHP_EOL;
        print_r($httpCode);
        echo PHP_EOL;



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
function downloadPhoneNumber($idAnnonce,$token)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => "https://www.olx.pt/api/v1/offers/{$idAnnonce}/limited-phones/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token['access_token']}"]
        ]
    );
    $phoneContent = curl_exec($ch);
    $phone = json_decode($phoneContent, true);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpCode == 403){
        throw new Exception('Reconnection requise');
    }
    curl_close($ch);
    try {
        $phone = substr(str_replace([' ', '-', '/', '(', ')'], '', $phone['data']['phones'][0]), -9);
        return $phone;
        if (substr($phone, 0, 1) != "9") {
            throw new Exception();
        }
    } catch (Throwable $e) {
        throw new Exception('Reconnection requise',$e);    
    }


}
function downloadAnnonce($idAnnonce)
{
    $token = create_token();
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => "https://www.olx.pt/api/v2/offers/{$idAnnonce}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token['access_token']}"]
        ]
    );
    $annonceContent = curl_exec($ch);
    
    $annonce = json_decode($annonceContent,true);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    if ($httpCode == 410 || $httpCode == 404) {
        return false;
    }
    $jsonAnnonce['telephonepresent'] = 0;
    $jsonAnnonce["telephone"] = '';
    //check if having phone number
    if(isset($annonce['data']['contact']['phone']) && $annonce['data']['contact']['phone'] == 1)
    {
        //get phone number
        try{
            $phone = downloadPhoneNumber($idAnnonce, $token);
            if (strlen($phone) > 0) {
                $jsonAnnonce["telephonepresent"] = 2;
                $jsonAnnonce["telephone"] = $phone;
            }
        } catch(Exception $e) {
            throw $e;
        }   
    }
    
    

    // Pays
    $jsonAnnonce["pays"] = "PT";

    // Titre
    if(isset($annonce['data']['title']))
    {
        $jsonAnnonce["titre"] = $annonce['data']['title'];
    }

    // Code postal irrécupérable dans un premier temps, à récupérer via la ville

    // Ville
    if(isset($annonce['data']['location']['region']['normalized_name']))
    {
        $jsonAnnonce["ville"] = ucwords($annonce['data']['location']['region']['normalized_name']);
        
    }

    
    // Prix 
    $jsonAnnonce["prix"]= '';
    foreach($annonce['data']["params"] as $key){
        if($key['key'] == "price"){
            $jsonAnnonce["prix"]= $key['value']['value'];
        }
    }

    // Année
    $jsonAnnonce["annee"]= '';
    foreach($annonce['data']["params"] as $key){
        if($key['key'] == "year"){
            $jsonAnnonce["annee"]= $key['value']['key'];
        }
    }


    // Carburant
    $jsonAnnonce["carburant"]= '';
    foreach($annonce['data']["params"] as $key){
        if($key['key'] == "combustivel"){
            $jsonAnnonce["carburant"]= $key['value']['label'];
        }
    }


    //Modele
    $jsonAnnonce["modele"]='';

    foreach($annonce['data']["params"] as $key){
        if($key['key'] == "modelo"){
            $jsonAnnonce["modele"]= $key['value']['label'];
        }
    }

    //Km
    $jsonAnnonce["km"]='';

    foreach($annonce['data']["params"] as $key){
        if($key['key'] == "quilometros"){
            $jsonAnnonce["km"]= $key['value']['key'];
        }
    }

    // Marque
    $jsonAnnonce["marque"] = '';
    $list_marque = array
    (
        "4914" => "Abarth",
        "763" => "Alfa Romeo",
        "753" => "Aston Martin",
        "751" => "Audi",
        "749" => "Austin Morris",
        "743" => "Bentley",
        "741" => "BMW",
        "731" => "Chevrolet",
        "729" => "Chrysler",
        "727" => "Citroen",
        "721" => "Dacia",
        "719" => "Daewoo",
        "717" => "Daihatsu",
        "4922" => "Datsun",
        "707" => "Dodge",
        "4879" => "DS",
        "701" => "Ferrari",
        "699" => "Fiat",
        "697" => "Ford",
        "689" => "GMC",
        "683" => "Honda",
        "681" => "Hummer",
        "679" => "Hyundai",
        "673" => "Isuzu",
        "671" => "Jaguar",
        "669" => "Jeep",
        "665" => "Kia",
        "663" => "Lada",
        "661" => "Lamborghini",
        "659" => "Lancia",
        "657" => "Land Rover",
        "655" => "Lexus",
        "649" => "Lotus",
        "645" => "Maserati",
        "641" => "Mazda",
        "637" => "Mercedes-Benz",
        "633" => "MG",
        "631" => "MINI",
        "629" => "Mitsubishi",
        "621" => "Nissan",
        "617" => "Opel",
        "769" => "Other",
        "613" => "Peugeot",
        "607" => "Porsche",
        "603" => "Renault",
        "601" => "Rolls Royce",
        "817" => "Rover",
        "815" => "Saab",
        "809" => "Seat",
        "805" => "Skoda",
        "803" => "Smart",
        "801" => "SsangYong",
        "797" => "Subaru",
        "795" => "Suzuki",
        "791" => "Tata",
        "4885" => "Tesla",
        "789" => "Toyota",
        "781" => "Vauxhall",
        "777" => "VW",
        "775" => "Volvo",
        "819" => "UMM"
    );
    if(isset($annonce['data']['category']['id']))
    {
        $cat_index = $annonce['data']['category']['id'];
        if (array_key_exists($cat_index,$list_marque))
        {
            $jsonAnnonce["marque"] = $list_marque[$cat_index];
        }       
    }

    // Date de mise en ligne
    $jsonAnnonce["date_mise_ligne"] = '';
    if(isset($annonce['data']['created_time'])){
        $created_time = $annonce['data']['created_time'];
        $jsonAnnonce["date_mise_ligne"] = DateTime::createFromFormat(DateTime::ATOM, $created_time)->format('Y-m-d H:i:s');
    }

    // Statut pro
    $jsonAnnonce["statut_vendeur_particulier"] = 0;
    if (isset($annonce['data']['business'])) {
        $seller_type = $annonce['data']['business'];
        if ($seller_type == false) {
            $jsonAnnonce["statut_vendeur_particulier"] = 1;
        }
    }


    //User name
    $jsonAnnonce["nom"]  = "";
    if(isset($annonce['data']['user']['name'])){
        $jsonAnnonce["nom"] = $annonce['data']['user']['name'];
    }

    // Image
    $jsonAnnonce["photo_url"] = '';
    if(isset($annonce['data']['photos'][0]['link'])){
        $photo_link = $annonce['data']['photos'][0]['link'];
        $pattern = '/https:\/\/ireland.apollo.olxcdn.com:443\/v1\/files\/[^\/]+\/image/s';
        preg_match($pattern, $photo_link, $matches);
        if (isset($matches[0])) {
            $jsonAnnonce["photo_url"] = $matches[0];
        }
    }

    // Description
    $jsonAnnonce["description"] = '';
    if(isset($annonce['data']['description'])){
        $jsonAnnonce["description"] = str_replace('<br />','',$annonce['data']['description']);
    }

    $toReturn = json_encode($jsonAnnonce, JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    if (json_last_error() > 0) {
        PrintDebug::display(json_last_error_msg(), true);
        return false;
    }
    print_r($jsonAnnonce);
    return $toReturn;
}
$u0 = "https://www.olx.pt/d/anuncio/jeep-wrangler-2-8-crd-atx-sahara-IDGJOty.html";//web normal
$u1 = "https://www.olx.pt/d/anuncio/vende-se-golf-1999-IDGJ6nT.html#d447c7e096"; //hcode:200 => this ad is no longer available, the seller has already finished this ad
$u2 = "https://www.olx.pt/d/anuncio/vw-sharan-2-0-tdi-IDGJc91.html#2d0ff97ec2";//410 => the page doesn't exist
$u3 = "https://www.olx.pt/d/anuncio/ford-fiesta-pouqussimos-kilometros-IDGHT10.html#afe7453ff5;promoted";//web normal
$u4 = "https://www.olx.pt/d/anuncio/audi-a4-1-9-tdi-130-cv-IDGEBr4.html#2d0ff97ec2";//hcode:200 => this ad is no longer available, the seller has already finished this ad
$u5 = "https://www.olx.pt/d/anuncio/renault-megane-1-5dci-105cv-gps-07-IDGECfu.html#2d0ff97ec2";
$u6 = "https://www.olx.pt/d/anuncio/volkswagen-passat-IDGEDNr.html#2d0ff97ec2";
$u7 = "https://www.olx.pt/d/anuncio/renault-megane-1-5-IDGEw6p.html#820fe2e299;promoted";//normal
$tu = 'https://www.olx.pt/d/anuncio/fiat-punto-evo-1-2-2011-IDGA0s1.html#2d0ff97ec2';//在后台能正常下载，带tel。 实测无法下载号码。页面号码与后台不符。
$idFord = '626734436';
$idJeep = '631524904';
$idx1 = '631501824';
$idx2 = '631497768';
downloadAnnonce('535211627');


//print_r(create_token());
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
