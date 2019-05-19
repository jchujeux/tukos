define (["dojo/_base/declare", "dijit/form/Button", "dijit/popup", "tukos/menuUtils","tukos/PageManager"], 
    function(declare, Button, popup, mutils, Pmg, messages){
    return declare([Button], {
        postCreate: function(){
            var self = this;
        	this.inherited(arguments);
			this.customizableAtts = {defaultCalendar: {att: 'defaultCalendar', name: Pmg.message('associatedCalendar'), type: 'RestSelect', atts: {
				dropDownFilters: {contextpathid: '$tabContextId'}, storeArgs: {object: 'calendars', params: {getOne: 'calendarSelect', getAll: 'calendarsSelect'}}
			}}};
        	this.onClick = function(evt){
                evt.stopPropagation();
                evt.preventDefault();
        		var id = this.form.valueOf('id');
        		if (id){
        			Pmg.tabs.request({object: 'calendars', view: 'Edit', action: 'Tab', query: {contextpathid: self.form.tabContextId(), storeatts: {where: {parentid: id}, init: {googleSource: self.defaultCalendar || '', tukosSource: id, contextid: self.form.tabContextId()}}}});        			
        		}else{
                    Pmg.alert({title: Pmg.message('newNoCalendar'), content: Pmg.message('mustSaveFirst')});
        		}
        	}
        }
    });
});
