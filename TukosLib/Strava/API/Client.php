<?php
namespace TukosLib\Strava\API;

use Strava\API\Client as BasvandorstClient;
use Strava\API\Exception as ClientException;
use Strava\API\Service\Exception as ServiceException;

class Client extends BasvandorstClient{

    public function getActivityStreams($id, $keys = null)
    {
        try {
            return $this->service->getActivityStreams($id, $keys);
        } catch (ServiceException $e) {
            throw new ClientException('[SERVICE] ' . $e->getMessage());
        }
    }
}
?>