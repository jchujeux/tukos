<?php

namespace TukosLib\Objects\BusTrack\Reconciliations\Customers\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\BusTrack\Reconciliations\Customers\Views\Edit\ViewActionStrings as VAS;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController); 
        $this->dataLayout['contents']['row1'] = [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['50%', '50%']],
            'contents' => [
                'col1' => [
                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['id', 'parentid', 'name', 'startdate', 'enddate', 'nocreatepayments', 'verificationcorrections']
                ],
                'col2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => ['comments']
                ]
            ]
        ];
        
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
            'widgets' => ['paymentslog'],
        ];
        $this->actionWidgets['process']['atts'] = array_merge($this->actionWidgets['process']['atts'], [
            'label' => $this->view->tr('Synchronize'),
            'allowSave' => true,
            'urlArgs' => ['query' => ['params' => json_encode(['process' => 'processOne', 'save' => true])]], 
            'includeWidgets' => ['parentid', 'startdate', 'enddate']
        ]);
        $this->actionLayout['contents']['actions']['widgets'][] = 'importbankreport';
        $this->actionWidgets['importbankreport'] = ['type' => 'SimpleUploader', 'atts' => ['label' => $this->view->tr('Importpayments'), 'multiple' => false, 'uploadOnSelect' => true, 'grid' => 'paymentslog', 'serverAction' => 'Process',
            'includeWidgets' => ['id', 'startdate', 'enddate', 'parentid'], 'queryParams' => json_encode(['process' => 'importPayments', 'noget' => true]), 'onCompleteAction' => VAS::onImportCompleteAction()]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'synchronize';
        $this->actionWidgets['synchronize'] =  ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('synchronize'), 'onClickAction' => VAS::syncOnClickAction($this->view->model->customersOrSuppliers)]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'verifyreconciliation';
        $this->actionWidgets['verifyreconciliation'] = ['type' => 'SimpleUploader', 'atts' => ['label' => $this->view->tr('Verifyreconciliation'), 'multiple' => false, 'uploadOnSelect' => true, 'grid' => 'paymentslog', 
            'serverAction' => 'Process', 'includeWidgets' => ['id', 'startdate', 'enddate', 'parentid', 'verificationcorrections'], 'includeGridWidgets' => ['paymentslog'], 'queryParams' => json_encode(['process' => 'verifyReconciliation', 'noget' => true]), 
            'onCompleteAction' => VAS::onVerificationCompleteAction()]];
    }
}
?>
