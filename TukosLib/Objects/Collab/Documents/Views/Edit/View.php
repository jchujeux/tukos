<?php

namespace TukosLib\Objects\Collab\Documents\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController); 

        unset($this->dataLayout['contents']['row1']['widgets'][array_search('subFilesUploader', $this->dataLayout['contents']['row1']['widgets'])]);
        $addedRowLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
            'widgets' => ['subFilesUploader'],
        ];
        $offset = array_search('row3', array_keys($this->dataLayout['contents']));
        $this->dataLayout['contents'] = array_merge(
            array_slice(
                $this->dataLayout['contents'], 0, $offset), [
                    'row21' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['subFilesUploader'],
                    ]
                ],
            array_slice($this->dataLayout['contents'], $offset, null)
        );
        $this->actionLayout['contents']['actions']['widgets'][] = 'uploader';
        $this->actionWidgets['uploader'] = Widgets::description(['type' => 'uploader', 'atts' => ['edit' => [
                    'title' => $this->view->tr('uploadOne'),
                    'uploaderAtts' => ['label' => $this->view->tr("Select file to upload"), 'multiple' => false, 'uploadOnSelect' => false],
                    'uploadButtonAtts' => ['label' => $this->view->tr("Upload now"), 'style' => ['display' => 'none;'], 'subFiles' => false],
                ]
            ]
        ]);
        $this->actionLayout['contents']['actions']['widgets'][] = 'downloader';
        $this->actionWidgets['downloader'] = Widgets::description(['type' => 'downloader', 'atts' => ['edit' => [
                    'title' => $this->view->tr('downloadOne'),
                ]
            ]
        ]);
      
    }
}
?>
