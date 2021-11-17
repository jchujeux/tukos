define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager"], 
function(declare, lang, utils, wutils, Pmg){
    return declare(null, {
        constructor: function(args){
			var form = args.form, sessionsGrid = this.sessionsGrid = form.getWidget('physiopersosessions');
			lang.mixin(this, args);
			this.sessionsStore = sessionsGrid.store;
        },
		dateChangeLocalAction: function(sWidget, tWidget, newValue, oldValue){
			var form = this.form;            
            var setEditValues = function(){
                form.serverDialog(lang.mixin(self.urlArgs || {action: 'Edit'}, {query: newValue ? {startdate: sWidget.get('value')} : {}}), [], form.get('dataElts'), Pmg.message('actionDone')); 
            }
            if(form.userChangesCount() <= 1){
                setEditValues();
            }else{
                Pmg.setFeedback(' ');
                Pmg.confirmForgetChanges({widgets: true}).then(
                		function(){setEditValues();}, 
                		function(){
							Pmg.setFeedback(Pmg.message('actionCancelled'));
							sWidget.set('value', oldValue);
							wutils.markAsUnchanged(sWidget);
						}
                );
            }
			return true;
		}
	});
});
