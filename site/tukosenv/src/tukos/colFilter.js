define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/store/Memory", "dijit/_WidgetBase", "dijit/form/TextBox", "dijit/form/FilteringSelect", "dijit/registry", "dojo/json", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, dct, Memory, Widget, TextBox, FilteringSelect, registry, JSON, messages){
    var oprStore = new Memory({data: [
        {name:  '', id:  ''}, {name:  '=', id:  '='}, {name: '<>', id: '<>'}, {name: '>', id: '>'}, {name: '<', id: '<'},
        {name: '>=', id: '>='}, {name: '<=', id: '<='}, {name: messages.rlike, id: 'RLIKE'},
        {name: messages.notrlike, id: 'NOT RLIKE'}, {name: messages.inrange, id: 'BETWEEN'}
    ]});
	return declare(Widget, {
        postCreate: function(){
            var onChange = lang.hitch(this, this.onChange), onBlur = lang.hitch(this, this.onBlur), onKeyDown = lang.hitch(this, this.onKeyDown);
            this.inherited(arguments);
            this.oprWidget = new FilteringSelect(lang.mixin(
            	{placeHolder: messages.selectfilter, store: typeof this.filters === 'object' ? new Memory({data: this.filters}) : oprStore, intermediateChanges: true, onBlur: onBlur, onKeyDown: onKeyDown, onChange: onChange}, this.oprAtts), dojo.doc.createElement('div')); 
            this.domNode.appendChild(this.oprWidget.domNode);
            this.entryWidget = new TextBox(lang.mixin({id: this.id + 'entry', intermediateChanges: true, onBlur: onBlur, onKeyDown: onKeyDown, onChange: onChange}, this.entryAtts), dojo.doc.createElement('div'));
            this.domNode.appendChild(this.entryWidget.domNode);
        },

        _setValueAttr: function(values){
            if (typeof values === 'string'){
                this.oprWidget.set('value', '=', false);
                this.entryWidget.set('value', values, false);               
            }else{
                this.oprWidget.set('value', values[0], false);
                this.entryWidget.set('value', values[1], false);
            }
        },

        _getValueAttr: function(){
            return [this.oprWidget.get('value'), this.entryWidget.get('value')];
        },

        _setDisabledAttr: function(value){
                this.oprWidget.set('disabled', value, false);
                this.entryWidget.set('disabled', value, false);
        },
        
        onKeyDown: function(event){
			if (event.keyCode === 13) {
				//event.preventDefault();
				var grid = this.grid;
				this.onFilterChange(this);
                //grid.form.serverDialog({action: 'reset', query: {}}, options, messages.actionDone).then(function(response){
                //grid.store.userFilters = grid.userFilters();
				grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                	//Pmg.setFeedback(messages.actionDone);
                //});
				console.log('was an enter');
			}        	
        },
        
        onChange: function(newValue){
        	this.hasChanged = true;
        },

        onBlur: function(event){
            if (this.hasChanged){
            	this.onFilterChange(this);
            	this.hasChanged = false;
            }
            console.log('was a blur in colFilter');
        }
    });
}); 

