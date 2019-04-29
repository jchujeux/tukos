define (["dojo/_base/declare", "dijit/form/Button", "dijit/popup", "tukos/menuUtils", "tukos/DialogConfirm","tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, Button, popup, mutils, DialogConfirm, Pmg, messages){
    return declare([Button], {
        postCreate: function(){
            var self = this;
        	this.inherited(arguments);
			this.customizableAtts = {defaultCalendar: {att: 'defaultCalendar', name: messages.defaultCalendar, type: 'RestSelect', atts: {
				dropDownFilters: {contextpathid: '$tabContextId'}, storeArgs: {object: 'calendars', params: {getOne: 'calendarSelect', getAll: 'calendarsSelect'}}
			}}};
        	this.onClick = function(evt){
                evt.stopPropagation();
                evt.preventDefault();
        		var id = this.form.valueOf('id');
        		if (id){
        			Pmg.tabs.request({object: 'calendars', view: 'Edit', action: 'Tab', query: {contextpathid: self.form.tabContextId(), storeatts: {where: {parentid: id}, init: {googleSource: self.defaultCalendar || '', tukosSource: id, contextid: self.form.tabContextId()}}}});        			
        		}else{
                    var dialog = new DialogConfirm({title: messages.newNoCalendar, content: messages.mustSaveFirst, hasSkipCheckBox: false});
                    dialog.show().then(function(){Pmg.setFeedback(messages.actionCancelled);},
                                       function(){Pmg.setFeedback(messages.actionCancelled);});
        			
        		}
        	}
        }
    });
});
