<?php

namespace TukosLib\Objects\BusTrack\Dashboards\Customers\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;

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
                                            'widgets' => ['id', 'parentid', 'name']
                                        ],
                                        'row2' => [
                                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'labelWidth' => 150],
                                            'widgets' => ['startdate', 'startdatependinginvoices', 'enddate']
                                        ],
                                    ]
                                ],
                                'col2' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'contents' => [
                                        'row1' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => $this->view->tr('Keyindicators')],
                                            'contents' => [
                                                'row1' => [
                                                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                                    'widgets' => []
                                                ],
                                                'row2' => [
                                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 250],
                                                    'widgets' => ['pendingamount']
                                                ]
                                            ]
                                        ]
                                    ]
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
                    'widgets' => ['totalwotpercategory']
                ]
            ]
        ];
        $kpiWidgets = [];
        foreach(['label', 'details', 'exp', 'unexp', 'total'] as $label){
            $kpiWidgets[] = "label{$label}";
        }
        foreach(['vatfree', 'withvatwot', 'vat', 'wot', 'wt'] as $label){
            foreach (['label', 'details', 'exp', 'unexp', 'total'] as $prefix){
                $kpiWidgets[] = "{$prefix}{$label}";
            }
        }
        $this->dataLayout['contents']['row1']['contents']['col1']['contents']['row1']['contents']['col2']['contents']['row1']['contents']['row1']['widgets'] = $kpiWidgets;
        
        
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
            'widgets' => ['paymentslog', 'pendinginvoiceslog', 'paymentsdetailslog'],
        ];
        $this->actionWidgets['process']['atts'] = array_merge($this->actionWidgets['process']['atts'], [
            'label' => $this->view->tr('generateoractualize'),
            'allowSave' => true,
            'urlArgs' => ['query' => ['params' => json_encode(['process' => 'processOne', 'save' => true])]], 
            'includeWidgets' => ['parentid', 'startdate', 'enddate']
        ]);
        $this->actionLayout['contents']['actions']['widgets'][] = 'process';
    }
}
?>
