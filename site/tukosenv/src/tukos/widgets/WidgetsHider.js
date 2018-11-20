define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dijit/_WidgetBase", "dijit/TooltipDialog", "dijit/popup", "dojo/json", "dojo/domReady!"], 
function(declare, lang, dct, style, Widget, TooltipDialog, popup, JSON){
    return declare(Widget, {
        postCreate: function(){
            this.inherited(arguments);
            this.isOpened = false;
            this.hiderButton = dct.create('button', {'class': 'ui-icon tukos-hider-toggle', type: 'button', onclick: lang.hitch(this,this.toggleHiderMenu)}, this.domNode);
        },

        toggleHiderMenu: function(){
	        if (!this.dialog){
				var form = this.form, widgetsName = form.widgetsName;
	        	this.dialog = new TooltipDialog({});
	            var hiderTable = this.hiderMenu = dct.create('table', {style: 'max-height: 300px; overflow: auto;background-color: #fff;'});
	            for (var i in widgetsName){
	                var widgetName = widgetsName[i];
	                var widget = form.getWidget(widgetName);
	                if (widget != undefined){
	                    var tr       = dct.create('tr', {/*class: 'tukos-hider-menu-row'*/}, hiderTable);
	                    var td       = dct.create('td', {/*class: 'tukos-hider-menu-row'*/}, tr);
	                    var checkBox = dct.create('input', {id: form.id + 'hiderRow' + widgetName/*, 'class': 'tukos-hider-menu-check'*/, type: 'checkbox', checked: !widget.hidden, onclick: lang.hitch(this, this.toggleWidgetHideMode)}, td);
	                    checkBox.widgetName = widgetName;
	                    var td       = dct.create('td', {style: 'text-align: left;'}, tr);
	                    td.appendChild(document.createTextNode(widget.title || widget.label));
	                }
	            };
	            this.dialog.set('content', hiderTable);
			}
			if (this.isOpened){
				popup.close(this.dialog);
				this.isOpened = false;
			}else{
				popup.open({
	                popup: this.dialog,
	                around: this.hiderButton
	            });
				this.isOpened = true;
			}
        },
        
        toggleWidgetHideMode: function(evt){
            var form = this.form, target = evt.currentTarget, checked = target.checked, widgetName = target.widgetName, widget = form.getWidget(widgetName);
            if (widget.disabled){
                widget.hidden = !checked;//JCH see below
            }else{
                widget.set('hidden', !checked);//JCH: for disabled textarea, this generates NS_ERROR_UNEXPECTED under firefox(!)
            }
            widget.layoutHandle.resize();
            if (!widget.hidden && typeof widget.resize === 'function'){
            	setTimeout(function(){widget.resize();}, 0);// for dgrid's noDataMessage not to overlap header
        	}
            //lang.setObject((widget.itemCustomization || 'customization') + '.widgetsDescription.' + widget.widgetName + '.atts.hidden', widget.hidden, form);
            lang.setObject('customization.widgetsDescription.' + widget.widgetName + '.atts.hidden', widget.hidden, form);
        }
    });
}); 
