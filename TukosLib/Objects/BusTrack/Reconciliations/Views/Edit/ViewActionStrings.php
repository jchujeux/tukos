<?php
namespace TukosLib\Objects\BusTrack\Reconciliations\Views\Edit;


class ViewActionStrings{
    public static function onImportCompleteAction(){
        return <<<EOT
        var form = this.form;
        form.setValueOf('paymentslog', data['payments']);
        form.resize();
EOT;
    }
    public function syncOnClickAction(){
        return <<<EOT
	var self = this, form = this.form, getWidget = lang.hitch(form, form.getWidget), label = this.get('label'), paymentsLog = getWidget('paymentslog'), selection = paymentsLog.get('selection'),
        store = paymentsLog.get('store'), paymentsToSync = [];
    utils.forEach(selection, function(isSelected, idProp){
        if (isSelected){
            paymentsToSync.push(store.getSync(idProp));
        }
    });
	this.set('label', Pmg.loading(label));
    Pmg.serverDialog({action: 'Process', object: 'bustrackreconciliations', view: 'edit', query: {id: form.valueOf('id'), params: {process: 'syncPayments', noget: true}}}, 
            {data: {payments: paymentsToSync, organization: form.valueOf('parentid')}}).then( 
        function(response){
            //paymentsLog.set('value', response.data.paymentslog);            
            var updatedPayments = response.data.paymentslog;		  
            utils.forEach(updatedPayments, function(payment){
                //store.putSync(payment);
                paymentsLog.updateRow(payment);
            });
		    self.set('label', label);
		    //pane.resize();
        },
        function(error){
            console.log('error');
        }
	);
EOT;
    }
}
?>