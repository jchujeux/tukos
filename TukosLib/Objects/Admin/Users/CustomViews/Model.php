<?php
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

class Model extends AbstractModel {

    protected $viewOptions = ['edit', 'overview', 'massedit'];
    protected $panemodeOptions = ['tab', 'accordion', 'mobile'];

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'vobject'       =>  'VARCHAR(50)  DEFAULT NULL',
            'view'          =>  "ENUM ('" . implode("','", $this->viewOptions) . "') DEFAULT NULL",
            'panemode'      =>  "ENUM ('" . implode("','", $this->panemodeOptions) . "') DEFAULT NULL",
        	'customization' =>  'longtext DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'customviews', ['parentid' => ['users']], ['customization'], $colsDefinition, [], ['vobject', 'view', 'panemode'], [], ['name', 'vobject', 'view', 'panemode']);
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
