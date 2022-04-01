<?php
namespace TukosLib\Strava\API\Service;

use Strava\API\Service\REST as BasvandorstREST;

class REST extends BasvandorstREST{

    public function getActivityStreams($id, $keys = null, $resolution='all', $series_type='distance')
    {
        $path = 'activities/' . $id . '/streams';
        $parameters['query'] = [
            'keys' => $keys,
            'key_by_type' => true,
            'access_token' => $this->getToken(),
        ];
        if ($resolution !== 'all'){
            $parameters['query']['resolution'] = $resolution;
            $parameters['query']['series_type'] = $series_type;
        }
        
        return $this->getResponse('GET', $path, $parameters);
    }
}
?>