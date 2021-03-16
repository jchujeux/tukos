define(["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/when", "dijit/_editor/_Plugin", "dijit/form/DropDownButton", "tukos/widgets/editor/plugins/_TagEditDialog", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, ready, when, _Plugin, Button, _TagEditDialog, messages) {

    var SelectionEditor = dojo.declare([_Plugin], {
    
        iconClassPrefix: "dijitAdditionalEditorIcon",
        
        _initButton: function(){
            var editor = this.editor, createDropDown = lang.hitch(this, this._createDropDown);
            this.button = new Button({
                label: messages["editSelectionTag"],
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "TagEdit",
                tabIndex: "-1",
                loadDropDown: function(callback){
                	this.dropDown = createDropDown();
                	ready(function(){
                		callback();
                	});
                }
            });
        },
        updateState: function(){
            // Over-ride for button state control for disabled to work.
            this.button.set("disabled", this.get("disabled"));
        },
        setEditor: function(editor){
            this.editor = editor;
            this._initButton();
        },
        _createDropDown: function(){
        	var dropDown = this.dropDown = new _TagEditDialog({
                editor: this.editor, button: this.button,
        		dialogAtts: function(){
                    return this._dialogAtts(
                    	{tagname: {type: 'TextBox', atts: {title: messages.tagName, style: {width: '10em'}, disabled: true}}}, 
                    	{headerRow: {tableAtts: {cols: 1, customClass: 'labelsAndValues', label: messages.tagEditor, showLabels: true, orientation: 'horiz'}, widgets: ['tagname']}}, 
                    	['apply', 'close'], {tableAtts: {cols: 3,   customClass: 'labelsAndValues', showLabels: false}, widgets: ['apply', 'close']},
                    	['width', 'height', 'display', 'margin', 'pageBreakInside']);
                }
        	});
        	dropDown.openDialog = this.openDialog;
        	return dropDown;
        },
        openDialog: function(){
	        var target = this.target, pane = this.pane;
			when(this.inherited(arguments), function(response){
	        	if (response){
	                pane.getWidget('tagname').set('value', target.tagName);
	                return true;       		
	        	}else{
	        		return false;
	        	};
			});
        }
    });
	_Plugin.registry['SelectionEditor'] = function(){return new SelectionEditor({})};
    return SelectionEditor;
});
