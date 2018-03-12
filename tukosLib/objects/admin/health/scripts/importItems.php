<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;

use Zend\Console\Getopt;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;
use Vendor\parseCSV;

class ImportItems {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');

        try{
            $options = new Getopt(
                ['class=s'          => 'this class name',
                 'filename=s'       => 'the full filename for the file to import',
                 'object=s'         => 'the object into which file items need to be imported',
                 'parentid-s'       => 'parent id (optional, default is user->id())',
                 'parentTable-s'    => 'parent  table (optional, required if parentid is not a users)',
                 'uniqueValueCols-s'=> 'Optional - If set, will update existing record when it exists with same value for these cols ',
                ]);
            $tableObj  = $objectsStore->objectModel('healthtables');
            $object = $options->object;
            $filename = $options->filename;
            $importObj = $objectsStore->objectModel($options->object);

            $csv = new parseCSV($options->filename);
            $itemsImported = 0;
            foreach ($csv->data as $row){
                foreach ($row as $col => &$item){
                    if (empty($item)){
                        unset($row[$col]);
                    }
                }
                if ($options->uniqueValueCols){
                    $uniqueValueCols = json_decode($options->uniqueValueCols);
                    foreach ($uniqueValueCols as $col){
                        $where[$col] = $row[$col];
                    }
                    $existing = $importObj->getOne(['where' => $where, 'cols' => ['id']]);
                    if (empty($existing)){
                        $importObj->insert($row);
                        $itemsImported += 1;
                    }else{
                        if ($importObj->updateOne($row, ['where' => ['id' => $existing['id']]])){
                            $itemsImported += 1;
                        }
                    }   
                }else if (empty($importObj->getOne(['where' => $row, 'cols' => ['id']]))){// insert only if that translation does not yet exists
                    $importObj->insert($row);
                    $itemsImported += 1;
                }
            }
            $extendedParentId = [
                'id'    => ($options->parentid    ? $options->parentid    : $user->id()),
                'object' => ($options->parentTable ? $options->parentTable : 'users'),
            ];
            $objValue = ['name'         => $object, 
                         'parentid'     => $extendedParentId,
                      'datehealthcheck' => date('Y-m-d H:i:s'),
                         'comments'     => 'Import - imported ' . $itemsImported . ' items from file: ' . $filename,
                        ];
            echo $objValue['comments'];
            $tableObj->insertExtended($objValue, true); 

        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command aguments in ImportItems: ', $e->getUsageMessage());
        }
    }
}
?>
