<?php

namespace TukosLib\Objects\Admin\JsTesting\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => ['id', 'parentid', 'name']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => ['tukosmodulepath', 'function', 'parameters']
            ],
            'row3' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                'widgets' => ['comments', 'outcome']
            ],
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['runtest'] =  ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('runtest'), 'onClickAction' => $this->runTestAction()]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'runtest';
    }
    function runTestAction(){
        return  <<<EOT
const self = this, form = this.form, modulePath = form.valueOf('tukosmodulepath'), functionToExecute = form.valueOf('function'), parameters = form.valueOf('parameters');
if (modulePath){
    Pmg.setFeedback(Pmg.message('actionDoing'));
    require([modulePath], function(LocalActions){
        if (!self.localActions){
            self.localActions = new LocalActions({form: self.form});
        }
        setTimeout(function(){
            const outcome = self.localActions[functionToExecute](parameters);
            form.setValueOf('outcome', outcome);
            Pmg.addFeedback(Pmg.message('actionDone'), null, ' ');
        }, 0);
    });
}else{
    Pmg.setFeedbackAlert(Pmg.message('needtoprovidemodulepath'));
}
EOT
        ;
    }
}
?>
