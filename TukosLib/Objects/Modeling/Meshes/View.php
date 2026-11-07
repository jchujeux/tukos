<?php
namespace TukosLib\Objects\Modeling\Meshes;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '40em']]]],
            'comments' => ['atts' => ['edit' => ['width' => '800px', 'height' => '100px']]],
            'snodes' => ViewUtils::JsonGrid($this, 'Nodes', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'nodeId'=> ViewUtils::tukosNumberBox($this, 'nodeid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord1'=> ViewUtils::tukosNumberBox($this, 'xcoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord2'=> ViewUtils::tukosNumberBox($this, 'ycoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord3'=> ViewUtils::tukosNumberBox($this, 'zcoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]])
            ], ['atts' => ['edit' => ['maxHeight' => '300px', 'tukosTooltip' => ['label' => 'Hello', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'ModelingMeshesSnodesTukosTooltip']],
            ]]]),
            's1dgroups' => ViewUtils::JsonGrid($this, '1dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'steps1' => ViewUtils::tukosNumberBox($this, 'rsteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'boundaries'=> ViewUtils::textArea($this, 'Boundaries conditions', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '8em']]]]]),
                'backgroundColor' => viewUtils::colorPickerTextBox($this, 'BackgroundColor'),
                'opacity'=> ViewUtils::tukosNumberBox($this, 'opacity', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em'], 'constraints' => ['pattern' => '#.#']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            's2dgroups' => ViewUtils::JsonGrid($this, '2dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'steps1' => ViewUtils::tukosNumberBox($this, 'rsteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'steps2' => ViewUtils::tukosNumberBox($this, 'ssteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node4'=> ViewUtils::tukosNumberBox($this, 'node4', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node5'=> ViewUtils::tukosNumberBox($this, 'node5 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node6'=> ViewUtils::tukosNumberBox($this, 'node6 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node7'=> ViewUtils::tukosNumberBox($this, 'node7 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node8'=> ViewUtils::tukosNumberBox($this, 'node8 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'boundaries'=> ViewUtils::textArea($this, 'Boundaries conditions', ['atts' => ['storeedit' => ['width' => '200', 'editorArgs' => ['style' => ['width' => '200px']]]]]),
                'backgroundColor' => viewUtils::colorPickerTextBox($this, 'BackgroundColor', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '4em']]]]]),
                'opacity'=> ViewUtils::tukosNumberBox($this, 'opacity', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em'], 'constraints' => ['pattern' => '#.#']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            's3dgroups' => ViewUtils::JsonGrid($this, '3dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid'),
                'steps1' => ViewUtils::tukosNumberBox($this, 'rsteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'steps2' => ViewUtils::tukosNumberBox($this, 'ssteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'steps3' => ViewUtils::tukosNumberBox($this, 'tsteps', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node4'=> ViewUtils::tukosNumberBox($this, 'node4', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node5'=> ViewUtils::tukosNumberBox($this, 'node5', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node6'=> ViewUtils::tukosNumberBox($this, 'node6', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node7'=> ViewUtils::tukosNumberBox($this, 'node7', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node8'=> ViewUtils::tukosNumberBox($this, 'node8', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node9'=> ViewUtils::tukosNumberBox($this, 'node9 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node10'=> ViewUtils::tukosNumberBox($this, 'node10 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node11'=> ViewUtils::tukosNumberBox($this, 'node11 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node12'=> ViewUtils::tukosNumberBox($this, 'node12 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node13'=> ViewUtils::tukosNumberBox($this, 'node13 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node14'=> ViewUtils::tukosNumberBox($this, 'node14 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node15'=> ViewUtils::tukosNumberBox($this, 'node15 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node16'=> ViewUtils::tukosNumberBox($this, 'node16 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node17'=> ViewUtils::tukosNumberBox($this, 'node17 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node18'=> ViewUtils::tukosNumberBox($this, 'node18 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node19'=> ViewUtils::tukosNumberBox($this, 'node19 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node20'=> ViewUtils::tukosNumberBox($this, 'node20 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'boundaries'=> ViewUtils::textArea($this, 'Boundaries conditions', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '8em']]]]]),
                'backgroundColor' => viewUtils::colorPickerTextBox($this, 'BackgroundColor'),
                'opacity'=> ViewUtils::tukosNumberBox($this, 'opacity', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em'], 'constraints' => ['pattern' => '#.#']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'gnodes' => ViewUtils::JsonGrid($this, 'GeneratedNodes', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'nodeId'=> ViewUtils::tukosNumberBox($this, 'nodeid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord1'=> ViewUtils::tukosNumberBox($this, 'xcoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord2'=> ViewUtils::tukosNumberBox($this, 'ycoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'coord3'=> ViewUtils::tukosNumberBox($this, 'zcoord', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]])
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'gboundaries' => ViewUtils::JsonGrid($this, 'GeneratedBoundaries', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'boundaryId'=> ['field' => 'boundaryId', 'label' => $this->tr('boundaryid'), 'width' => 80],
                'type '=> ['field' => 'type', 'label' => $this->tr('boundarytype'), 'width' => 80],
                'nodes' => ViewUtils::htmlContent($this, 'Boundarydescription', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => []]]),
                ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'g1dgroups' => ViewUtils::JsonGrid($this, 'Generated1dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'elementId'=> ViewUtils::textBox($this, 'elementid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]])
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'g2dgroups' => ViewUtils::JsonGrid($this, 'Generated2dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'elementId'=> ViewUtils::textBox($this, 'elementid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node4'=> ViewUtils::tukosNumberBox($this, 'node4'),
                'node5'=> ViewUtils::tukosNumberBox($this, 'node5 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node6'=> ViewUtils::tukosNumberBox($this, 'node6 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node7'=> ViewUtils::tukosNumberBox($this, 'node7 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node8'=> ViewUtils::tukosNumberBox($this, 'node8 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'g3dgroups' => ViewUtils::JsonGrid($this, 'Generated3dgroups', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'groupId'=> ViewUtils::textBox($this, 'groupid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'elementId'=> ViewUtils::textBox($this, 'elementid', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node1'=> ViewUtils::tukosNumberBox($this, 'node1', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node2'=> ViewUtils::tukosNumberBox($this, 'node2', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node3'=> ViewUtils::tukosNumberBox($this, 'node3', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node4'=> ViewUtils::tukosNumberBox($this, 'node4', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node5'=> ViewUtils::tukosNumberBox($this, 'node5', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node6'=> ViewUtils::tukosNumberBox($this, 'node6', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node7'=> ViewUtils::tukosNumberBox($this, 'node7', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node8'=> ViewUtils::tukosNumberBox($this, 'node8', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node9'=> ViewUtils::tukosNumberBox($this, 'node9 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node10'=> ViewUtils::tukosNumberBox($this, 'node10 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node11'=> ViewUtils::tukosNumberBox($this, 'node11 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node12'=> ViewUtils::tukosNumberBox($this, 'node12 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node13'=> ViewUtils::tukosNumberBox($this, 'node13 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node14'=> ViewUtils::tukosNumberBox($this, 'node14 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node15'=> ViewUtils::tukosNumberBox($this, 'node15 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node16'=> ViewUtils::tukosNumberBox($this, 'node16 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node17'=> ViewUtils::tukosNumberBox($this, 'node17 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node18'=> ViewUtils::tukosNumberBox($this, 'node18 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node19'=> ViewUtils::tukosNumberBox($this, 'node19 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
                'node20'=> ViewUtils::tukosNumberBox($this, 'node20 (optional)', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '3em']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
            'smeshdiagram' => ViewUtils::lazyEditor($this, 'SourceMeshDiagram', ['atts' => ['edit' => ['width' => '400px', 'height' => '400px'], 'overview' => ['hidden' => true]]]),
            'gmeshdiagram' => ViewUtils::lazyEditor($this, 'GeneratedMeshDiagram', ['atts' => ['edit' => ['width' => '400px', 'height' => '400px'], 'overview' => ['hidden' => true]]]),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
