<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;

class GetGeminiResponse extends AbstractAction{
    function response($query){
        $key = getenv('GEMINI_API_KEY');
        $client = \Gemini::factory()
        ->withApiKey($key)
        ->withBaseUrl('https://generativelanguage.googleapis.com/v1/') // default: https://generativelanguage.googleapis.com/v1/
        ->withHttpHeader('X-My-Header', 'foo')
        ->withQueryParam('my-param', 'bar')
        ->withHttpClient(new \GuzzleHttp\Client([]))
        ->make();
        $request = $this->dialogue->getValues()['request'];
        $result = $client->geminiPro()->generateContent(mb_convert_encoding($request, 'UTF-8'));
        if ($result){
            $parseDown = new \Parsedown();
            $content = $result->text();
            return ['generatedContent' => $parseDown->text($content)];
        }else{
            return ['generatedContent' => 'error to be done'];
        }
    }
}
?>
