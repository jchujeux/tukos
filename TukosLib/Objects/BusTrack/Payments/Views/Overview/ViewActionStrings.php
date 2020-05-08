<?php
namespace TukosLib\Objects\BusTrack\Payments\Views\Overview;


class ViewActionStrings{
    public static function onImportCompleteAction(){
        return <<<EOT
        var form = this.form;
        form.setValueOf('payments', data['payments']);
        form.resize();
EOT;
    }
    public function syncOnClickAction(){
        return <<<EOT
	var self = this, pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), label = this.get('label'), payments = paneGetWidget('payments'), selection = payments.get('selection'),
        store = payments.get('store'), paymentsToSync = [];
    utils.forEach(selection, function(isSelected, idProp){
        if (isSelected){
            paymentsToSync.push(store.getSync(idProp));
        }
    });
	this.set('label', Pmg.loading(label));
	Pmg.serverDialog({action: 'Process', object: 'bustrackpayments', view: 'overview', query: {params: {process: 'syncPayments', noget: true}}},
            {data: {payments: paymentsToSync, organization: pane.valueOf('organization')}}).then( 
        function(response){
            var updatedPayments = response.data.payments;		  
            utils.forEach(updatedPayments, function(payment){
                store.putSync(payment);
            });
		    self.set('label', label);
		    pane.resize();
        },
        function(error){
            console.log('error');
        }
	);
EOT;
    }
}
?>