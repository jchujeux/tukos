<?php
namespace TukosLib\Strava\API;

use Strava\API\Client as BasvandorstClient;
use Strava\API\Service\Exception as ServiceException;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Client extends BasvandorstClient{

    public function getActivityStreams($id, $keys = null, $resolution = 'all', $series_type = 'distance')
    {
        try {
            return $this->service->getActivityStreams($id, $keys, $resolution,  $series_type);
        } catch (ServiceException $e) {
            Feedback::add(Tfk::tr('nostreamfoundforstravaid') . ': ' .  $id);
            return [];
        }
    }
}
?>