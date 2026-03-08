<?php
$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/TukosFramework.php';

use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;

try {
    Tfk::initialize('commandLine', 'tukosApp', $phpDir);
    $key = getenv('GEMINI_API_KEY');
    $client = Gemini::factory()
     ->withApiKey($key)
     ->withBaseUrl('https://generativelanguage.googleapis.com/v1beta/') // default: https://generativelanguage.googleapis.com/v1/
     ->withHttpHeader('X-My-Header', 'foo')
     ->withQueryParam('my-param', 'bar')
     ->withHttpClient(new \GuzzleHttp\Client([])) 
     ->make();
     $result = $client->generativeModel(model: 'gemini-2.0-flash')->generateContent('Hello');
     $textResult = $result->text();
     print iconv('UTF-8', 'ISO-8859-1', $textResult);
     var_dump(Utl::utf8($result->text())); // Hello! How can I assist you today?
     
} catch(Exception $e) {
    print $e->getMessage();
}