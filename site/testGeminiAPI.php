<?php
$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/TukosFramework.php';

use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
//use Aura\Http\Manager\Factory as HttpFactory;

/*
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$YOUR_API_KEY

" \
-H 'Content-Type: application/json' \
-X POST \
-d '{
  "contents": [{
    "parts":[{"text": "Explain how AI works"}]
    }]
   }'
 */
try {
    Tfk::initialize('commandLine', 'tukosApp', $phpDir);
    //$client = Gemini::client(getenv('GEMINI_API_KEY'));
    $key = getenv('GEMINI_API_KEY');
    $client = Gemini::factory()
    ->withApiKey($key)
     ->withBaseUrl('https://generativelanguage.googleapis.com/v1/') // default: https://generativelanguage.googleapis.com/v1/
     ->withHttpHeader('X-My-Header', 'foo')
     ->withQueryParam('my-param', 'bar')
     ->withHttpClient(new \GuzzleHttp\Client([])) 
     ->make();
     $result = $client->geminiPro()->generateContent(mb_convert_encoding("Ecris-moi une introduction pour un rapport d'inspection électrique pour un spectacle.", 'UTF-8'));
     $textResult = $result->text();
     print iconv('UTF-8', 'ISO-8859-1', $textResult);
     var_dump(Utl::utf8($result->text())); // Hello! How can I assist you today?
     
} catch(Exception $e) {
    print $e->getMessage();
}