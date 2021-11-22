define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dijit/popup", "dijit/TooltipDialog", "dijit/focus", "tukos/PageManager"], 
function(declare, lang, dct, dst, popup, TooltipDialog, focus, Pmg){
    return declare(TooltipDialog, {
        postCreate: function(){
        	this.inherited(arguments);
			var self = this, form = this.form, widgetsName = form.widgetsName;
            var hiderTable = self.hiderMenu = dct.create('table', {style: 'max-height: 300px; overflow: auto;background-color: Silver;'});
            for (var i in widgetsName){
                var widgetName = widgetsName[i];
                var widget = form.getWidget(widgetName);
                if (widget != undefined){
                    var tr       = dct.create('tr', {}, hiderTable),  td       = dct.create('td', {}, tr), id = form.id + 'hiderRow' + widgetName;
                    var checkBox = dct.create('input', {id: id, type: 'checkbox', style: {width: '30px'}, checked: !widget.hidden, onchange: this.toggleWidgetHideMode}, td);
                    checkBox.widget = widget;
                    var td       = dct.create('td', {style: 'text-align: left;'}, tr);
                    var label = dct.create('label', {"for": id}, td);
                    label.appendChild(document.createTextNode(widget.title || widget.label)); 
                }
            };
            this.set('content', hiderTable);
			if (!Pmg.isMobile()){
				this.onBlur = function(){
					setTimeout(function(){
						self.close();
					}, 300);
				}
			}
            //document.body.appendChild(this.domNode);
		},
		close: function(){
			if (this.isOpened){
				popup.close(this);
				this.isOpened = false;
				if (Pmg.isMobile()){
					this.button.set('icon',  "mblDomButtonBlueCirclePlus");
				}
				return true;
			}else{
				return false;
			}
		},
		toggleHiderMenu: function(){
			if (!this.close()){
                if (Pmg.isMobile()){
					this.button.set('icon', "mblDomButtonBlueCircleMinus");
				}
				popup.open({popup: this, around: this.buttonNode});
				focus.focus(this.domNode);
				this.isOpened = true;
			}
        },
        toggleWidgetHideMode: function(change){
            var widget = this.widget, checked = change.currentTarget.checked;
            if (widget.layoutContainer){
            	dst.set(widget.layoutContainer, 'display', checked ? '' : 'none');
            	widget.set('hidden', !checked);
            }else{
                if (widget.disabled){
                    widget.hidden = !checked;//JCH see below
                }else{
                    widget.set('hidden', !checked);//JCH: for disabled textarea, this generates NS_ERROR_UNEXPECTED under firefox(!)
                }
                if (widget.layoutHandle){
                	widget.layoutHandle.unfreezeWidth = true;
					widget.layoutHandle.unfrozenWidths = 0;
					widget.layoutHandle.resize();
					widget.layoutHandle.unfreezeWidth = false;
					//widget.layoutHandle.resize();
                }
                if (widget.layoutHandle.unfrozenWidths){
                	setTimeout(function(){// for dgrid's noDataMessage not to overlap header
						widget.layoutHandle.needsToFreezeWidth = true;
						widget.layoutHandle.resize();
						widget.layoutHandle.needsToFreezeWidth = false;
					}, 0);
            	}
            }
            lang.setObject('customization.widgetsDescription.' + widget.widgetName + '.atts.hidden', widget.hidden, widget.form);
        }
    });
}); 
