<?php
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    protected $viewOptions = ['edit', 'overview', 'massedit'];

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'vobject'        =>  'VARCHAR(50)  DEFAULT NULL ',
            'view'          =>  "ENUM ('" . implode("','", $this->viewOptions) . "') ",
            'customization' =>  'longtext DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'customviews', ['parentid' => ['users']], ['customization'], $colsDefinition, '', ['vobject', 'view'], [], ['name', 'vobject', 'view']);
        $this->vobjectOptions = $this->user->allowedModules();
    }
    public function deleteCustomization($where, $valuesToDelete){
        $customization =  $this->getOne(['where' => $where, 'cols' => ['customization']], ['customization' => []])['customization'];
        if (!empty($customization)){
            Utl::drillDownDelete($customization, $valuesToDelete);
            $this->updateOne(['customization' => json_encode($customization)], ['where' => $where]);
            return $customization;
        }else{
            Feedback::add('customviewnotfound');
        }
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['parentid' => $this->user->id()], $init));
    }
}
?>
