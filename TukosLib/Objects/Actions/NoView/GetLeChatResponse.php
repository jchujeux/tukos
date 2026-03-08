<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use GuzzleHttp\Client;

class GetLeChatResponse extends AbstractAction{
    /*function response($query){
        $key = getenv('GEMINI_API_KEY');
        $client = \Gemini::factory()
        ->withApiKey($key)
        ->withBaseUrl('https://generativelanguage.googleapis.com/v1beta/') // default: https://generativelanguage.googleapis.com/v1/
        ->withHttpHeader('X-My-Header', 'foo')
        ->withQueryParam('my-param', 'bar')
        ->withHttpClient(new \GuzzleHttp\Client([]))
        ->make();
        $request = $this->dialogue->getValues()['request'];
        $result = $client->generativeModel(model: 'gemini-2.0-flash')->generateContent(mb_convert_encoding($request, 'UTF-8'));
        if ($result){
            $parseDown = new \Parsedown();
            $content = $result->text();
            return ['generatedContent' => $parseDown->text($content)];
        }else{
            return ['generatedContent' => 'error to be done'];
        }
    }*/
    function response($query){
        
        $apiKey = getenv('MISTRAL_API_KEY');
        $client = new Client();
        $request = $this->dialogue->getValues()['request'];
        
        $response = $client->post('https://api.mistral.ai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'mistral-tiny', // Remplacez par le modèle que vous souhaitez utiliser
                'messages' => [
                    ['role' => 'user', 'content' => $request]
                ],
            ],
        ]);
        
        $data = json_decode($response->getBody(), true);
        return ['generatedContent' =>  $data['choices'][0]['message']['content']];
    }
}
?>
