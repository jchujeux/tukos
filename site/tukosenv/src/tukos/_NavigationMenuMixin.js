define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dijit/popup", "dijit/focus",  "tukos/PageManager"], 
    function( declare, lang, ready, popup, focusUtil, Pmg){
    var customContextDialog;
	return declare(null, {
        onClickMenuItem: function(evt){
            focusUtil.curNode && focusUtil.curNode.blur();
        	Pmg.tabs.request(this.onClickArgs);
        },
        onChangeObjectSelect: function(newValue){
            if (!(newValue === '')){
                var self = this;
                this.onChangeArgs.object = this.object;
                this.onChangeArgs.query =( this.sendAsNew ? {dupid: newValue, grade: 'NORMAL'} : {id: newValue});
                Pmg.tabs.request(this.onChangeArgs);
                setTimeout(function(){
                        self.set('value', '', false, '');
                    }, 100
                );
            }
            dijit.popup.close(dijit.getEnclosingWidget(this));                
        },
        moduleContextId: function(widget){
            if (widget.parentMenu && widget.parentMenu.navigationItem){
                return widget.parentMenu.navigationItem.descriptor.atts.contextid || this.moduleContextId(widget.parentMenu);
            }else{
                return undefined;
            }
        },
        addTriggers: function(widget){
            if (widget.onClickArgs){
                lang.setObject('query.contextpathid', this.moduleContextId(widget), this.onClickArgs);
                //widget.onClick = this.onClickMenuItem;
                widget.on('click', this.onClickMenuItem);
            }
            if (widget.onChangeArgs){
                widget.onChange = this.onChangeObjectSelect;
            }
            return widget;
        },
        addContext: function(item){
            item.on ('contextmenu', lang.hitch(this, this.contextMenuCallback, item));
            return item;
        },
        contextMenuCallback: function(item, evt){
            evt.preventDefault();
            evt.stopPropagation();
            this.openCustomContextDialog(item);
        },
        openCustomContextDialog: function(item){
            var self = this;
        	if (customContextDialog){
            	customContextDialog.pane.setValueOf('contextSelect', item.context || '');
                popup.open({parent: item, popup: customContextDialog, around: item.domNode});            	
            }else{
                require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                	customContextDialog = new TukosTooltipDialog({paneDescription: {
            			widgetsDescription:{
            				contextSelect: {type: 'ObjectSelectDropDown', atts: {label: Pmg.message('Context'), style: {width: '12em'}, table: 'contexts', dropDownWidget: {type: 'StoreTree', atts: Pmg.cache.contextTreeAtts}}},
            				save: {type: "TukosButton", atts: {label: Pmg.message('Save'), onClick: function(evt){lang.hitch(self, self.saveCustomContext(item))}}},
            				cancel:{type: "TukosButton", atts: {label: Pmg.message('Cancel'), onClick: function(evt){lang.hitch(self, self.cancelCustomContext(item))}}}
            			},
            			layout: {
            				tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
            				contents: {
                				row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['contextSelect']},
                				row2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['save', 'cancel']}
            				}
            			}
                	}});
                    ready(function(){
                    	customContextDialog.pane.setValueOf('contextSelect', item.context || '');
                    	popup.open({parent: item, popup: customContextDialog, around: item.domNode});
                    });
                });
            }
        },
        saveCustomContext: function(item){
            var self = this;
            Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'ModuleContextSave'}, {data: {module: item.moduleName, contextid: customContextDialog.pane.valueOf('contextSelect')}}, Pmg.message('actionDone')).then(
                function(response){
                    item.context = response.contextid;
                    customContextDialog.close();
                }
            );
        },
        cancelCustomContext: function(item){
            Pmg.setFeedback(Pmg.message('actionCancelled'));
            customContextDialog.close();
        }
    });
});
