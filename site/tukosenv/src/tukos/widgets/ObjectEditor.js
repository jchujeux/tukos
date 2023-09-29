define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dijit/_WidgetBase", "tukos/utils", "tukos/hiutils", "tukos/widgetUtils", "dojo/domReady!"], 
function(declare, lang, dct, Widget, utils, hiutils, wutils){
    return declare(Widget, {
        postCreate: function(){
        	if (this.isEditTabWidget && !this.checkBoxChangeCallback){
        		this.checkBoxChangeCallback = lang.hitch(wutils, wutils.markAsChanged, this);
        	}
        },
    	_setValueAttr: function(value){
			this._set('value', utils.empty(value) ? '' : JSON.stringify(value));
        	if (this.valueNode){
                    dct.empty(this.valueNode);
                }
                this.selectedLeaves = {};
                this.valueNode =hiutils.objectTable(value, this.hasCheckboxes, this.selectedLeaves, this);
                this.domNode.appendChild(this.valueNode);
        },
        _getValueAttr: function(){
        	var value = this._get('value');
        	return value ? JSON.parse(value) : {};
        },
        _getServerValueAttr: function(){
        	return this.isEditTabWidget ? this.get('selectedLeaves') : this.get('value');
        }
    });
}); 
