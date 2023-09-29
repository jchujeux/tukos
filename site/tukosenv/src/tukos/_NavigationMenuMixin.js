define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dijit/popup", "dijit/focus",  "tukos/utils", "tukos/PageManager"], 
    function( declare, lang, ready, popup, focusUtil, utils, Pmg){
    var customContextDialog;
	return declare(null, {
        onClickMenuItem: function(evt){
			evt.preventDefault();
			evt.stopPropagation();
            focusUtil.curNode && focusUtil.curNode.blur();
        	Pmg.tabs.gotoTab(this.onClickArgs);
			if (Pmg.isMobile()){
				return false;
			};
        },
        onChangeObjectSelect: function(newValue){
            if (newValue !== ''){
                var self = this;
                this.onChangeArgs.object = this.object;
                this.onChangeArgs.query =( this.sendAsNew ? {dupid: newValue, grade: 'NORMAL'} : {id: newValue});
                Pmg.tabs.request(this.onChangeArgs).then(function(){
					self.set('value', '', false, '');
				});
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
                widget.on('click', this.onClickMenuItem);
            }
            if (widget.onChangeArgs){
                widget.onChange = this.onChangeObjectSelect;
            }
            return widget;
        },
        addContext: function(item){
			if (Pmg.isAtLeastAdmin() || Pmg.getCustom('contextCustomForAll') === 'YES' || (!Pmg.get('noContextCustomForAll') && Pmg.getCustom('contextCustomForAll') !== 'NO')){
				item.on ('contextmenu', lang.hitch(this, this.contextMenuCallback, item));
			}
            return item;
        },
        contextMenuCallback: function(item, evt){
            evt.preventDefault();
            evt.stopPropagation();
            this.openCustomContextDialog(item);
        },
        openCustomContextDialog: function(item){
            var self = this, pane;
        	if (customContextDialog){
            	self.openDialog(item);         	
            }else{
                require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                	customContextDialog = new TukosTooltipDialog({paneDescription: {
            			widgetsDescription:{
            				tukosContext: {type: 'ObjectSelectDropDown', atts: {label: Pmg.message('TukosContext'), style: {width: '12em'}, table: 'contexts', dropDownWidget: {type: 'StoreTree', atts: Pmg.cache.contextTreeAtts}}},
            				userContext: {type: 'ObjectSelectDropDown', atts: {label: Pmg.message('UserContext'), style: {width: '12em'}, table: 'contexts', dropDownWidget: {type: 'StoreTree', atts: Pmg.cache.contextTreeAtts}}},
            				activeContext: {type: 'ObjectSelectDropDown', atts: {label: Pmg.message('ActiveContext'), style: {width: '12em'}, table: 'contexts', dropDownWidget: {type: 'StoreTree', atts: Pmg.cache.contextTreeAtts},
            					disabled: true}},
            				save: {type: "TukosButton", atts: {label: Pmg.message('Save'), onClick: function(evt){lang.hitch(self, self.saveCustomContext())}}},
            				cancel:{type: "TukosButton", atts: {label: Pmg.message('Close'), onClick: function(evt){lang.hitch(self, self.cancelCustomContext())}}}
            			},
            			layout: {
            				tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
            				contents: {
                				row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true}, widgets: ['tukosContext', 'userContext', 'activeContext']},
                				row2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['save', 'cancel']}
            				}
            			}, 
            			postElts: ['tukosContext', 'userContext']
                	}});
                    ready(function(){
                    	if (!Pmg.isAtLeastAdmin()){
                    		customContextDialog.pane.getWidget('tukosContext').set('hidden', true);
                    	}
                    	self.openDialog(item);
                    });
                });
            }
        },
        openDialog: function(item){
        	var pane = customContextDialog.pane;
        	pane.markIfChanged = false;
        	pane.resetChangedWidgets();
        	pane.setValueOf('tukosContext', item.tukosContext || '');
        	pane.setValueOf('userContext', item.userContext || '');
        	pane.setValueOf('activeContext', item.activeContext || '');
        	pane.markIfChanged = true;
        	customContextDialog.item = item;
        	popup.open({parent: item, popup: customContextDialog, around: item.domNode});
        },
        saveCustomContext: function(){
            var self = this, item = customContextDialog.item, pane = customContextDialog.pane;
    		Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'ModuleContextSave', query: {module: item.moduleName}}, {data: pane.changedValues(['tukosContext', 'userContext'])}).then(
    			function(response){
    				pane.setValueOf('activeWidget', response.data.activeWidget);
    				utils.forEach(pane.changedWidgets, function(widget, name){
    					item[name] = widget.get('value');
    				});
    				pane.resetChangedWidgets();
    			}
    		);
        },
        cancelCustomContext: function(){
            Pmg.setFeedback(Pmg.message('actionCancelled'));
            customContextDialog.close();
        }
    });
});
