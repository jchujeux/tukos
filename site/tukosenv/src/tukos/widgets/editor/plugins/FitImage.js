define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojo/mouse", "dijit/_editor/_Plugin", "dijit/form/Button", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, domStyle, mouse, _Plugin, Button, messages) {
        
    var FitImage = dojo.declare([_Plugin], {
    
        iconClassPrefix: "dijitAdditionalEditorIcon",
        
        //visualTag: '<span class="visualTag" style="background-color:lightgrey">¤</span>',

        _initButton: function(){
            var editor = this.editor, self = this;//, createDropDown = lang.hitch(this, this._createDropDown);
            this.button = new Button({
                label: messages["fitImageToWindow"],
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "FitWindow",
                tabIndex: "-1",
                onClick: function(e){
                	domStyle.set(editor.selection.getSelectedElement(), 'width', '100%');
                	console.log(e);                		
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
        
	    close: function(){
	    	popup.close(this.dropDown);
	    }

    });
	_Plugin.registry['FitImage'] = function(){return new FitImage({})};
	
    return FitImage;

});
