/*
 *    Provides a tab  object and associated methods
 */
 define (["dojo/_base/declare", "dojo/_base/lang", "dijit/layout/ContentPane", "dijit/Tooltip", "tukos/ObjectPane", "tukos/PageManager"], 
    function(declare, lang, ContentPane, Tooltip, ObjectPane, Pmg){
    return declare(ContentPane, {
        postCreate: function (){    
            if (this.formContent != undefined){
				this.serverFormContent = lang.clone(this.formContent);
                this._createForm(this.formContent);
            }
            this.widgetType = "TukosTab";
            this.inherited(arguments);
        },
        startup: function(){
        	this.inherited(arguments);
            if (this.titleTukosTooltip){
				const self = this;
				dojo.ready(function(){
					const atts = self.titleTukosTooltip, name = Pmg.tukosTooltipName(atts.onClickLink.name);
					if (name){
						new Tooltip({connectId: ['centerPanel_tablist_' + self.id], label: atts.label +  '<span style="text-decoration: underline; color: blue; cursor: pointer;" onclick="tukos.Pmg.viewTranslatedInBrowserWindow(\''+ name + '\', \'' +
							 (atts.onClickLink.object || self.formContent.object) + '\')">' + (atts.onClickLink.label || '(' + tukos.Pmg.message("more") + ' ...)') + ' </span>'});
					}else if (atts.label){
						new Tooltip({connectId: ['center_panel_' + self.id], label: atts.label});//centerPanel_tablist_dijit_layout_ContentPane_7
					}
				});
            }
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
