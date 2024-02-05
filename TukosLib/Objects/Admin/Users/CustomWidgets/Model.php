<?php
namespace TukosLib\Objects\Admin\Users\CustomWidgets;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

class Model extends AbstractModel {

    //protected $viewOptions = ['edit', 'overview'];

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'vobject'       =>  'VARCHAR(50)  DEFAULT NULL',
            'widgettype'    =>  "VARCHAR(50)  DEFAULT NULL",
        	'customization' =>  'longtext DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'customwidgets', ['parentid' => ['users']], ['customization'], $colsDefinition, [], ['vobject', 'widgettype'], []);
        $this->vobjectOptions = $this->user->allowedModules();
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['parentid' => $this->user->id()], $init));
    }
}
?>
