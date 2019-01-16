<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Collab\Documents;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\Collab\Documents\Storage;

class Model extends AbstractModel {

    use Storage;

    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'mdate' =>  "timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
                            'type'  =>  "VARCHAR(60) default null",
                            'size'  =>  "BIGINT(20)  unsigned default null",
                            'sha1'  =>  "CHAR(40)",
                          'fileid'  =>  "mediumint(8) unsigned NOT NULL default '0'",
        ];
        parent::__construct(
            $objectName, $translator, 'documents',
            ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], 
            [], $colsDefinition, ' KEY (`sha1`)'
        );
        //$this->constructStorage();
    }
    
   /*
    *  Given a $filePath, returns its $descriptor:
    *   if $descriptor['id'] is set, then the assumption is that this is an existing file, and the full descriptor of the existing file is returned
    *   else, it is a new file, and $descriptor only returns the newly computed sha1 of the file
    */
    function descriptor($filePath){// if Storage::descriptor['id'] is set, then the file is already in storage and the full descriptor is returned, else the newly created sha1
        $sha1 = sha1_file($filePath);
        $descriptor = $this->getOne(['where' => ['sha1' => $sha1], 'cols' => ['*']]);
        $descriptor['sha1'] = $sha1;
        return $descriptor;
    }

}
?>
