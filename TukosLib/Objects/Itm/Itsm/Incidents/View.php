<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Itm\Itsm\Incidents;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    public $statusHistoryCols = ['progress', 'assignedto', 'escalationlevel'];

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Service Description', 'Incident Description');

        $customDataWidgets = [
            'parentid'          => ['atts' => ['edit' => ['placeHolder' => 'Enter this first...']]],
            'name'              => ['atts' => ['edit' => ['style' => ['width' => '20em']]]],
            'notifiedby'        => ViewUtils::objectSelectMulti('notifiedby', $this, 'Notified by'),
            'notifiedvia'       => ViewUtils::storeSelect('notifiedVia', $this, 'Notified via'),
            'callbackmethod'    => ViewUtils::storeSelect('callback', $this, 'Call back method'),
            'urgency'           => ViewUtils::storeSelect('urgency', $this, 'Urgency', null, ['atts' => ['edit' => [
                            'onChangeLocalAction' => [
                                'priority'  => ['value' =>
                                    "if (newValue === ''){return '';}else{var impactW = sWidget.form.getWidget('impact'), impactValue = impactW.get('value');" .
                                    "if (impactValue === ''){return '';}else{return tWidget.store.data[sWidget.store.index[newValue] + impactW.store.index[impactValue]].id;}}"
                                ],
                            ],
                        ]
                    ]
                ]
            ),
            'impact'            => ViewUtils::storeSelect('impact', $this, 'Impact', null, ['atts' => ['edit' => [
                            'onChangeLocalAction' => [
                                'priority'  => ['value' =>
                                    "if (newValue === ''){return '';}else{var urgencyW = sWidget.form.getWidget('urgency'), urgencyValue = urgencyW.get('value');" .
                                    "if (urgencyValue === ''){return '';}else{return tWidget.store.data[sWidget.store.index[newValue] + urgencyW.store.index[urgencyValue]].id;}}"
                                ],
                            ],
                        ]
                    ]
                ]
            ),
            'priority'          => ViewUtils::storeSelect('priority', $this, 'Priority', null, ['atts' => ['edit' => ['placeHolder' => 'from urgency and impact', 'disabled' => true, 'style' => ['fontWeight' => 700]]]]),
            'progress'            => ViewUtils::storeSelect('incidentsProgress', $this, 'Progress', null, [
                    'atts' => ['edit' => [
                            'onWatchLocalAction' => ['value' => ['progress' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->onProgressLocalAction()]]]]
                        ],
                    ],
                ]
            ),
            'assignedto'        => ViewUtils::objectSelectMulti('assignedto', $this, 'Assigned to', ['atts' => [
                    'edit' => ['dropdownFilters' => [/*'parentid' => '@parentid'*/'in' => '@parentid', 'list' => ['itsvcdescs' => ['supportgroups' => 'team']]]],
                ],
            ]),
            'escalationlevel'   => ViewUtils::storeSelect('escalationLevel', $this, 'Escalation level'),
            'category'          => ViewUtils::storeSelect('category', $this, 'Category'),
        ];

        $subObjects['objrelations'] = ['atts' => ['title'     => $this->tr('Related CIs')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['tasks'] = [
            'atts'  => ['title' => $this->tr('Assigned tasks'),],
            'filters' => ['parentid' => '@id',],
            'allDescendants' => true,
        ];
        $this->customize($customDataWidgets, $subObjects, [/*'grid' => ['statushistory'],*/ 'post' => ['statushistory']]);
        $gridCols = $this->statusHistoryCols;
        $gridCols[] = 'updated';
        $this->dataWidgets['statushistory'] = [
            'type' => 'storeDgrid', 
            'atts' => ['edit' => [
                    'label' => $this->tr('status tracking'),
                    'object' => $this->objectName,
                    'colsDescription' => $this->widgetsDescription($gridCols, false), 
                    'objectIdCols' => array_values(array_intersect($gridCols, $this->model->idCols)),
                    'maxHeight' => '300px', 'colspan' => 1, 'disabled' => true,
                'storeArgs' => ['idProperty' => 'idg']
                ]
            ],
        ];

    }
    function onProgressLocalAction(){
        return <<<EOT
return Pmg.serverDialog({object: 'itincidents', view: 'Edit', action: 'Process', query: {parentid: sWidget.valueOf('parentid'), progress: sWidget.valueOf('progress'), params: {process: 'getProgressChanged', noget: true}}}).then(function(response){
    utils.forEach(response.data, function(value, widgetName){
        sWidget.setValueOf(widgetName, value);
    });
});
EOT
        ;
    }
}
?>
