/*
 *  tukos   mixin for dynamic widget information handling and cell rendering
 */
define (["dojo/_base/lang", "dijit/Tooltip", "tukos/widgetUtils"], 
    function(lang, Tooltip, wutils){
    return {
        getAttr: function(attrName){
            return this[attrName] || this.getParent()[attrName];// widgets on a grid miss some attributes (form & formId in paticular) => if not found we look for it into its parent (e.g. a grid)
        },
        valueOf: function(name, displayed){
            return lang.hitch(this, wutils.valueOf)(name, displayed);
        },
		displayedValueOf: function(name){
			return lang.hitch(this, wutils.displayedValueOf)(name);
		},
        setValueOf:  function(name, value){
            return lang.hitch(this, wutils.setValueOf)(name, value);
        },
        setValuesOf:  function(data){
            return lang.hitch(this, wutils.setValuesOf)(data);
        },
        _setReadOnlyAttr: function(newValue){
        	this._set('readonly', newValue);
        	if (newValue){
        		this.defaultBackgroundColor = this.defaultBackgroundColor || this.get('style').backgroundColor;
        		this.set('style', {backgroundColor: 'WhiteSmoke'});
        	}else{
        		if (this.defaultBackgroundColor){
        			this.set('style', {backgroundColor: this.defaultBackgroundColor});
        		}
        	}
        },
        _setTukosTooltipAttr: function(atts){
			if (atts.onClickLink){
				const self = this, name = tukos.Pmg.tukosTooltipName(atts.onClickLink.name);
				if (name){
					self.customContextMenuItems = function(){
						return [{atts: {label: tukos.Pmg.message('help')  , onClick: function(){
							tukos.Pmg.viewTranslatedInBrowserWindow(name, atts.onClickLink.object || self.getRootForm().object)
						}}}];
					};
				}
				if (tukos.Pmg.getCustom('showTooltips') === 'YES'){
					if (this.tooltipInstance){
						this.tooltipInstance.destroy();
					}
					if (name){
						this.tooltipInstance = new Tooltip({connectId: atts.connectId || [this.domNode], label: atts.label +  '<span style="text-decoration: underline; color: blue; cursor: pointer;" onclick="tukos.Pmg.viewTranslatedInBrowserWindow(\''+ name + '\', \'' + 
							(atts.onClickLink.object || self.getRootForm().object) + '\')">' + 	(atts.onClickLink.label || '(' + tukos.Pmg.message("more") + ' ...)') + ' </span>'});
					}else if (atts.label){
						this.tooltipInstance = new Tooltip({connectId: [this.domNode], label: atts.label});
					}
					if (atts.position){
						this.tooltipInstance.set('position', atts.position);
					}
				}
				
			}else{
				if (this.tooltipInstance){
					this.tooltipInstance.destroy();
				}
				this.customContextMenuItems = function(){
					return []
				};
			}
		},
		_setTableContainerLabelAttr: function(newValue){
			if (this.tableContainerLabel){
				this.tableContainerLabel.innerHTML = newValue;
			}
		},
        getRootForm: function(){
        	return this.form.form || this.form;
        }
    }
});