<?php
namespace TukosLib\Objects\BusTrack\Dashboards;

class ViewActionStrings{

    protected  static $widgets = [
        'paymentsflag' => "['paymentscount', 'paidvatfree', 'paidwithvatwot', 'paidvat', 'paidwot', 'paidwt', 'paidwotpercategory', 'paymentslog']",
        'pendinginvoicesflag' => "['pendingamount', 'pendinginvoiceslog']",
        'unassignedpaymentsflag' => "['unassignedamount', 'unassignedpaymentslog']"
    ];
    
    static function flagLocalActionString($flagName){
        $widgets = self::$widgets;
        return <<<EOT
var form = sWidget.form;
sWidget.set('value', newValue ? 'YES' : '');
{$widgets[$flagName]}.forEach(function(widgetName){
    form.getWidget(widgetName).set('hidden', newValue);
});
console.log('checkbox action was triggerred - $flagName = ' + newValue);
form.resize();
return true;
EOT;
    }
    static function flagLocalAction($flagName){
        return ['checked' => [$flagName => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => self::flagLocalActionString($flagName)]]]];
    }
    static function openActionString(){
        $widgets = self::$widgets;
        return <<<EOT
var form = this, getWidget = lang.hitch(form, form.getWidget);
{$widgets['paymentsflag']}.forEach(function(widgetName){
    form.getWidget(widgetName).set('hidden', getWidget('paymentsflag').get('checked'));
});
{$widgets['pendinginvoicesflag']}.forEach(function(widgetName){
    form.getWidget(widgetName).set('hidden', getWidget('pendinginvoicesflag').get('checked'));
});
{$widgets['unassignedpaymentsflag']}.forEach(function(widgetName){
    form.getWidget(widgetName).set('hidden', getWidget('unassignedpaymentsflag').get('checked'));
});
form.resize();
EOT;
    }
}
?>