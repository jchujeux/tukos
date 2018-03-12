<?php
/**
 * Sets parentid of wineappellations to region.id from wineappellations.region
 */
namespace TukosLib\Objects\Wine\Regions\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\TukosFramework as Tfk;

class MigrateAppellations {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            $this->regionsObj       = $objectsStore->objectModel('wineregions');
            $this->appellationsObj  = $objectsStore->objectModel('wineappellations');

            $regions = $this->regionsObj->getAll(
                ['where' => [],
                 'cols'  => ['id', 'name', 'country'],
                ]
            );
            
            foreach ($regions as $region){
                $targetRegionAndCountry = ['region' => $region['name'], 'country' => $region['country']];
                $updateValue = ['parentid' => ['id' => $region['id'], 'object' => 'wineregions']];
                $regionAppellations = $this->appellationsObj->updateAllExtended($updateValue, ['where' => $targetRegionAndCountry]);
                Tfk::log_message('on', 'region: ' . $region['name'] . ' - Nb of appellations migrated: ' . count($regionAppellations));
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command aguments in Wine\Regions\Scripts\Populate: ', $e->getUsageMessage());
        }
    }
}
?>
