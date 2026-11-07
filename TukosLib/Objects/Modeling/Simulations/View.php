<?php
namespace TukosLib\Objects\Modeling\Simulations;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $dirColTemplate =  ['overflow' => "auto", 'renderHeaderCell' => "renderHeaderContent", 'renderCell' => "renderContent", 'rowsFilters' => true, 'editor' => "Textarea", 'widgetType' => "Textarea",
            'editorArgs' => ['style' => ['width' => '15em']], 'minWidth' => 40, 'editOn' => "click, keydown"/*, 'field' => "dof", 'label' => "dof", 'title' => "dof"*/, 'disabled' => false, 'canEdit' => "canEditRow"];
        $dirColTemplateScientific =  ['overflow' => "auto", 'renderHeaderCell' => "renderHeaderContent", 'renderCell' => "renderContent", 'rowsFilters' => true, 'editor' => "TukosNumberBox", 'widgetType' => "TukosNumberBox", 
            'constraints' => ['type' => 'scientific', 'zeroThreshold' => 1E-10, 'places' => 2],
            'editorArgs' => ['constraints' => ['type' => 'scientific', 'places' => 3], 'style' => ['width' => '3em']], 'minWidth' => 40, 'editOn' => "click, keydown", 'field' => "dof", 'label' => "dof", 'title' => "dof", 'disabled' => false, 'canEdit' => "canEditRow"];
        $customizableAtts = [
            'diagramOptions' => ['att' => 'diagramOptions', 'type' => 'TukosTextarea', 'name' => $this->tr('diagramOptions'), 'atts' => ['style' => ['width' => "500px"]]],
            ];
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '20em']]]],
            'comments' => ['atts' => ['edit' => ['width' => '500px', 'height' => '100px']]],
            'dimension'=> ViewUtils::tukosNumberBox($this, 'dimension', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'onChangeLocalAction' => ['nodalsolution' => ['value' => 'return [];']]]]]),
            'ndof'=> ViewUtils::tukosNumberBox($this, 'ndof', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'disabled' => true]]]),
            'linearity' => ViewUtils::storeSelect('linearity', $this, 'Linearity', [true, 'ucfirst', false, false, false], ['atts' => ['edit' => ['onChangeLocalAction' => ['nodalsolution' => ['value' => 'return [];']]]]]),
            'nonlinearoptions'=> ViewUtils::textArea($this, 'Nonlinearoptions', ['atts' => ['edit' => ['style' => ['width' => '20em'], 'onChangeLocalAction' => ['nodalsolution' => ['value' => 'return [];']]]]]),
            'timedependency' => ViewUtils::storeSelect('timeDependency', $this, 'Timedependency', [true, 'ucfirst', false, false, false], ['atts' => ['edit' =>['onChangeLocalAction' => ['nodalsolution' => ['value' => 'return [];']]]]]),
            'meshid'    => ViewUtils::objectSelect($this, 'meshid', 'mdlmeshes', ['atts' => ['edit' => ['onChangeLocalAction' => ['nodalsolution' => ['collection' => 'return [];'], 'gmeshdiagram' => ['value' => 'return "";']]], 'storeedit' => ['width' => 100]]]),
            'properties'=> ViewUtils::textArea($this, 'Globalproperties', ['atts' => ['edit' => ['style' => ['width' => '20em'], 'onChangeLocalAction' => ['nodalsolution' => ['value' => 'return [];']]]]]),
            'dofnames' => ViewUtils::JsonGrid($this, 'Dofnames', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'constraints'=> ViewUtils::textBox($this, 'consraintsname', ['atts' => ['storeedit' => ['width' => 90, 'editorArgs' => ['style' => ['width' => '8em'], 'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onDofNamesChangeAction()]]]]]]),
                'rhs'=> ViewUtils::textBox($this, 'rhsname', ['atts' => ['storeedit' => ['width' => 90, 'editorArgs' => ['style' => ['width' => '8em'], 'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onDofNamesChangeAction()]]]]]]),
                'solution'=> ViewUtils::textBox($this, 'solutionname', ['atts' => ['storeedit' => ['width' => 90, 'editorArgs' => ['style' => ['width' => '8em'], 'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onDofNamesChangeAction()]]]]]]),
            ], ['atts' => ['edit' => ['afterActions' => ['deleteRow' =>$this->onDofNamesRowChangeAction(), 'addRow' =>$this->onDofNamesRowChangeAction()], 'maxHeight' => '150px']]]),
            'boundariesconstraints' => ViewUtils::JsonGrid($this, 'Boundariesconstraints', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'boundaryId'=> ViewUtils::textBox($this, 'boundaryid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '8em']]]]]),
            ], ['atts' => [
                'edit' => ['maxHeight' => '300px', 'dofColPrefix' => 'constraints', 'dirColTemplate' => $dirColTemplate, 'onWatchLocalAction' => ['updateDirty' => ['nodalsolution' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>  'return [];']]]]]]]),
            'nodalconstraints' => ViewUtils::JsonGrid($this, 'Nodalconstraints', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'nodeId'=> ViewUtils::tukosNumberBox($this, 'nodeid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
            ], ['atts' => [
                'edit' => ['maxHeight' => '300px', 'dofColPrefix' => 'constraints', 'dirColTemplate' => $dirColTemplate, 'onWatchLocalAction' => ['updateDirty' => ['nodalsolution' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>  'return [];']]]]]]]),
            'boundariesrhs' => ViewUtils::JsonGrid($this, 'Boundariesrhs', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'boundaryId'=> ViewUtils::textbox($this, 'boundaryid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '8em']]]]]),
            ], ['atts' => [
                'edit' => ['maxHeight' => '300px', 'dofColPrefix' => 'rhs', 'dirColTemplate' => $dirColTemplateScientific, 'onWatchLocalAction' => ['updateDirty' => ['nodalsolution' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>  'return [];']]]]]]]),
            'nodalrhs' => ViewUtils::JsonGrid($this, 'Nodalrhs', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'nodeId'=> ViewUtils::tukosNumberBox($this, 'nodeid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
            ], ['atts' => [
                    'edit' => ['maxHeight' => '300px', 'dofColPrefix' => 'rhs', 'dirColTemplate' => $dirColTemplateScientific, 'onWatchLocalAction' => ['updateDirty' => ['nodalsolution' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>  'return [];']]]]]]]),
            'nodalsolution' => ViewUtils::JsonGrid($this, 'Nodalsolution', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'nodeId'=> ViewUtils::tukosNumberBox($this, 'nodeid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px', 'dofColPrefix' => 'solution', 'dirColTemplate' => $dirColTemplateScientific]]]),
            'groups' => ViewUtils::JsonGrid($this, 'groups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupId', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'dimension'=> ViewUtils::tukosNumberBox($this, 'dimension', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'globalDirections'=> ViewUtils::textBox($this, 'globaldirections', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '6em']]]]]),
                'ndof'=> ViewUtils::tukosNumberBox($this, 'ndof', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '6em']]]]]),
                'globalDofs'=> ViewUtils::textBox($this, 'globalDofs', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'problemType' => ViewUtils::storeSelect('problem', $this, 'Problemtype', [true, 'ucfirst', false, false, false], ['atts' => ['storeedit' => ['width' => 200, 'editorArgs' => ['style' => ['width' => '10em']]]]]),
                'elementType' => ViewUtils::storeSelect('elementType', $this, 'ElementType'),
                'rheologyType' => ViewUtils::storeSelect('rheology', $this, 'rheologyType', [true, 'ucfirst', false, false, false], ['atts' => ['storeedit' => ['width' => 200, 'editorArgs' => ['style' => ['width' => '10em']]]]]),
                'materialId' => ViewUtils::objectSelect($this, 'materialId', 'mdlmaterials', ['atts' => ['storeedit' => ['width' => 100]]]),
                'properties'=> ViewUtils::textArea($this, 'Otherproperties', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '8em']]]]]),
                'integrationOrder'=> ViewUtils::textBox($this, 'integrationOrder(s)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '6em']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px', 'objectIdCols' => ['materialId'], 'onWatchLocalAction' => ['updateDirty' => ['nodalsolution' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>  'return [];']]]]]]]),
            'gmeshdiagram' => ViewUtils::lazyEditor($this, 'SolutionDiagram', ['atts' => ['edit' => ['width' => '400px', 'height' => '400px', 'customizableAtts' => $customizableAtts], 'overview' => ['hidden' => true]]]),
        ];
        $this->customize($customDataWidgets);
    }
    public function onDofNamesChangeAction(){
        return <<<EOT
sWidget.form.localActions.setNodalConstraintsColumns();
EOT
        ;
    }
    public function onDofNamesRowChangeAction(){
        return <<<EOT
this.form.localActions.setNodalConstraintsColumns();
EOT
        ;
    }
    public function solutionRemoveAction(){
        return <<<EOT
sWidget.form.setValueOf('nodalsolution', []);
EOT
        ;
    }
}
?>
