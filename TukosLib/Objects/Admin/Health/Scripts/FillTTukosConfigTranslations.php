<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class FillTukosConfigTranslations {

    function __construct($parameters){ 
        $configStore        = Tfk::$registry->get('configStore');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $translationsInfo = $objectsStore->objectModel('translations')->getAll(['cols' => ['id', 'name', 'setname', 'en_us', 'fr_fr', 'es_es']]);
            foreach ($translationsInfo as $translation){
            	if ($translation['setname'] === 'objects' || is_null($translation['setname']) || ($translation['id']>= 36030 && $translation['id'] <= 36278) || in_array($translation['id'], [23011, 23016, 23069, 23071, 23176, 23177, 23178, 23181, 23187])){
            		continue;
            	}else{
	            	if (empty($configStore->getOne(['table' => 'translations', 'where' => ['setname' => $translation['setname'], 'name' => $translation['name']], 'cols' => ['id']]))){
	            		$configStore->insert($translation, ['table' => 'translations']);
	            	}
            	}
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
