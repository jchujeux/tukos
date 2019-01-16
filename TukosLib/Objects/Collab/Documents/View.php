<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\Documents;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'File name');
        $customDataWidgets = [
            'mdate' => ViewUtils::timeStampDataWidget($this, 'Last modification date', ['atts' => ['edit' => ['disabled' => true]]]),
            'type'  => ViewUtils::textBox($this, 'Type', ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 65]]]),
            'size'  => ViewUtils::textBox($this, 'Size', ['atts' => [
                    'edit' =>  [
                        'style' => ['width' => '9em'], 'disabled' => true,
                        'onChangeLocalAction' => ['downloader' => ['hidden' =>"console.log('newValue: ' + newValue);if (newValue > 0){return false;}else{return true}"]],
                    ],
                    'storeedit' => ['width' => 65],
                ]
            ]),
            'sha1'  => ViewUtils::textBox($this, 'Sha1', ['atts' => ['edit' =>  ['disabled' => true], 'storeedit' => ['width' => 65]]]),
            'subFilesUploader' => ['type' => 'uploader', 'atts' => ['edit' => [
                               'title' => $this->tr('Sub-files Uploader'), 'colspan' => 2,
                        'uploaderAtts' => ['label' => $this->tr("Select files to upload"), 'multiple' => true, 'uploadOnSelect' => false],
                    'uploadButtonAtts' => ['label' => $this->tr("Upload now"), 'style' => ['display' => 'none;'], 'subFiles' => true],
                    ]
                ]
            ],
        ];

        $subObjects['documents'] = [
            'atts' => ['title' => $this->tr('sub-documents'), 'customContextMenu' => 'download.customContextMenu'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => true
            ];

        $this->customize($customDataWidgets, $subObjects, [
            'edit' => ['fileid'], 'grid' => ['fileid', 'subFilesUploader'], 'get' => ['fileid', 'subFilesUploader'], 'post' => ['fileid', 'subFilesUploader']
        ]);
    }    
}
?>
