<?php

namespace TukosLib\Objects\BusTrack\Dashboards\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\BusTrack\Dashboards\ViewActionStrings as VAS;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController); 
        $this->dataLayout['contents']['row1'] = [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['50%', '50%']],
            'contents' => [
                'col1' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row1' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'contents' => [
                                'col1' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'contents' => [
                                        'row1' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'labelWidth' => 150],
                                            'widgets' => ['id', 'parentid', 'name', 'startdate', 'enddate']
                                        ],
                                        'row2' => [
                                            'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'labelWidth' => 100],
                                            'widgets' => ['paymentsflag', 'pendinginvoicesflag', 'unassignedpaymentsflag']
                                        ]
                                    ]
                                ],
                                'col2' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'labelWidth' => 150],
                                    'widgets' => ['paymentscount', 'paidvatfree', 'paidwithvatwot', 'paidvat', 'paidwot', 'paidwt', 'pendingamount', 'unassignedamount']
                                ]
                            ]
                        ],
                        'row2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert'],
                            'widgets' => ['comments']
                            
                        ]
                    ]
                ],
                'col2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert'],
                    'widgets' => ['paidwotpercategory']
                ]
            ]
        ];
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
            'widgets' => ['paymentslog', 'pendinginvoiceslog', 'unassignedpaymentslog'],
        ];
        $this->actionWidgets['process']['atts'] = array_merge($this->actionWidgets['process']['atts'], [
            'allowSave' => true,
            'urlArgs' => ['query' => ['params' => json_encode(['process' => 'processOne', 'save' => true])]], 
            'includeWidgets' => ['parentid', 'startdate', 'enddate']
        ]);
        $this->actionLayout['contents']['actions']['widgets'][] = 'process';
        $this->onOpenAction = VAS::openActionString();
    }
}
?>
