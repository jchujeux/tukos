/*
 *    Provides a tab  object and associated methods
 */
 define (["dojo/_base/declare", "dijit/layout/ContentPane", "dijit/registry", "tukos/ObjectPane", "dojo/domReady!"], 
    function(declare, ContentPane, registry, ObjectPane){
    return declare(ContentPane, {
        postCreate: function (){    
            if (this.formContent != undefined){
                this._createForm(this.formContent);
            }
            this.widgetType = "TukosTab";
            this.inherited(arguments);
        },
        refresh: function(formContent){
            this.form.destroyRecursive();
            this._createForm(formContent);
        },
        _createForm: function(formContent){
        	this.form = new ObjectPane(formContent);
        	this.form.parent = this;
        	this.onClose = function(){
        		return this.form.onClose();
        	}
        	this.addChild(this.form);
        },
        isObjectPane: function(){
        	return typeof(this.form.object) !== "undefined";
        }, 
        
        isAccordion: function(){
        	return false;
        },
            
        isTab: function(pane){
        	return true;
        }
    })
});
