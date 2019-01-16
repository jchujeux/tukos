<?php
/**
 * Populates the regions table from wineappellations region and country properties
 */
namespace TukosLib\Objects\Wine\Regions\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\TukosFramework as Tfk;

class Populate {

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

            $results = $this->appellationsObj->getAll(
                ['where' => [],
                 'cols'  => ['count(*)', 'region', 'country'],
               'groupBy' => ['region', 'country'],
                ]
            );
            Tfk::log_message('on', 'Wine\Regions\Scripts\Populate - query results: ',  $results);
            
            foreach ($results as $result){
                $targetRegionAndCountry = ['name' => $result['region'], 'country' => $result['country']];
                $existingRegionEntries = $this->regionsObj->getAll(['where' => $targetRegionAndCountry, 'cols' => ['id']]);
                if ($existingRegionEntries){
                    Tfk::error_message('on', 'entries already exists for that region and country: ', $existingRegionEntries);
                }else{
                    $this->regionsObj->insertExtended($targetRegionAndCountry, true);
                }
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command aguments in Wine\Regions\Scripts\Populate: ', $e->getUsageMessage());
        }
    }
}
?>
