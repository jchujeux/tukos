define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dijit/focus", "dijit/popup", "dijit/TooltipDialog"], 
function(declare, lang, dct, dst, focus, popup, TooltipDialog){
    return declare(TooltipDialog, {
        postCreate: function(){
        	this.inherited(arguments);
			var self = this, form = this.form, widgetsName = form.widgetsName;
            var hiderTable = self.hiderMenu = dct.create('table', {style: 'max-height: 300px; overflow: auto;background-color: Silver;'});
            for (var i in widgetsName){
                var widgetName = widgetsName[i];
                var widget = form.getWidget(widgetName);
                if (widget != undefined){
                    var tr       = dct.create('tr', {}, hiderTable);
                    var td       = dct.create('td', {}, tr);
                    var checkBox = dct.create('input', {id: form.id + 'hiderRow' + widgetName, type: 'checkbox', style: {width: '30px'}, checked: !widget.hidden, onchange: this.toggleWidgetHideMode}, td);
                    checkBox.widget = widget;
                    var td       = dct.create('td', {style: 'text-align: left;'}, tr);
                    td.appendChild(document.createTextNode(widget.title || widget.label));
                }
            };
            this.set('content', hiderTable);
            document.body.appendChild(this.domNode);
		},
		close: function(){
			if (this.isOpened){
				popup.close(this);
				this.isOpened = false;
				return true;
			}else{
				return false;
			}
		},
		toggleHiderMenu: function(){
			var self = this;
/*
			if (this.isOpened){
        		popup.close(this);
				this.isOpened = false;
			}else {
*/
			if (!this.close()){
				popup.open({popup: this, around: this.parent.domNode});
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
                	widget.layoutHandle.resize();
                }
                if (!widget.hidden && typeof widget.resize === 'function'){
                	setTimeout(function(){widget.resize();}, 0);// for dgrid's noDataMessage not to overlap header
            	}
            }
            lang.setObject('customization.widgetsDescription.' + widget.widgetName + '.atts.hidden', widget.hidden, widget.form);
        }
    });
}); 
