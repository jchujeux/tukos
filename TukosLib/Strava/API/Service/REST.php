<?php
namespace TukosLib\Strava\API\Service;

use Strava\API\Service\REST as BasvandorstREST;

class REST extends BasvandorstREST{

    public function getActivityStreams($id, $keys = null)
    {
        $path = 'activities/' . $id . '/streams';
        $parameters['query'] = [
            'keys' => $keys,
            'key_by_type' => true,
            'access_token' => $this->getToken(),
        ];
        
        return $this->getResponse('GET', $path, $parameters);
    }
}
?>