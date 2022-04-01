<?php

namespace TukosLib\Objects\Physio\Patients\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
                'row1' => [
                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'label' => $this->view->tr('Socioadmin'), 'orientation' => 'vert', 'labelWidth' => 75, 'widgetWidths' => ['30%', '40%', '30%']],
                    'contents' => [
                        'col1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => $this->view->tr('Admininfo')],
                            'contents' => [
                                'row1' => ['tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => ['id', 'parentid', 'name', 'firstname', 'email', 'telmobile', 'sex', 'socialsecuid', 'birthdate', 'age', 'profession', 'maritalstatus']],
                                'row2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => ['hobbies', 'stravainfo']],
                            ],
                        ],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('Medicalbackground')],      
                            'contents' => [              
                                'row1' => ['tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => ['laterality', 'height', 'weight', 'imc', 'corpulence', 'morphotype', 'hrthreshold', 'ftp', 'speedthreshold', 'hrmin', 'h4timethreshold', 'h5timethreshold']],
                                'row2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'antecedents']],
                            ]
                        ],
                    ]
                ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowcomments', 'rowsubobjects', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
