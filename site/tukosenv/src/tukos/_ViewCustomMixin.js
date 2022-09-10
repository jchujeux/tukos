define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/ready", "dijit/registry", 
         "tukos/utils",  "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, on, ready, registry, utils, Pmg, messages){
    return declare(null, {
        
        openCustomDialog: function(){
            var self = this, targetNode = this.currentPaneNode(), targetPane = this.currentPane(), form = targetPane.form || targetPane, customDialog = targetPane.customDialog;
            if (customDialog){
                customDialog.pane.getWidget('newCustomContent').set('value', form.customization);
                self.setVisibility({hideEmptyNewCustom: true});
                customDialog.open({around: targetNode});
               customDialog.pane.resize();
            }else{
				require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
					customDialog = targetPane.customDialog = new TukosTooltipDialog({paneDescription: {form: form, 
	                    widgetsDescription: {
	                        newCustomContent: {type: 'ObjectEditor', atts: {label: messages.newCustomContent, keyToHtml: 'capitalToBlank', style: {maxWidth: '700px', maxHeight: '600px', overflow: 'auto'}}},
	                        tukosCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'tukosCustomView', hidden: true}}, 
	                        tukosCustomViewLabel: {type: 'HtmlContent', atts: {value: Pmg.message('tukosCustomView'), hidden: true, disabled: true}},
	                        tukosCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, hidden: true,
	                        	onChange: lang.hitch(self, self.defaultCustomViewChange, 'tukos')}},
	                        defaultCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'defaultCustomView'}}, 
	                        defaultCustomViewLabel: {type: 'HtmlContent', atts: {value: messages.defaultCustomView, disabled: true}},
	                        defaultCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(self, self.defaultCustomViewChange, 'user')}},
	                        itemCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustomView'}}, 
	                        itemCustomViewLabel: {type: 'HtmlContent', atts: {style: {width: '180px'}, value: messages.itemCustomView}},
	                        itemCustomView: {type: 'ObjectSelect', 
	                        	atts: {object: 'customviews', mode: form.paneMode, dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(self, self.itemCustomViewChange)}},
	                        itemCustomButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustom'}}, 
	                        itemCustomLabel: {type: 'HtmlContent', atts: {}},
	                        save: {type: 'TukosButton', atts: {label: messages.save, onClick: lang.hitch(self, self.saveCallback)}},
	                        close: {type: 'TukosButton', atts: {label: messages.close, onClickAction:  "this.pane.close();"}},
	                        newCustomView: {type: 'TukosButton', atts: {label: messages.newCustomView, onClick: lang.hitch(self, self.newCustomView)}},
	                        more: {type: 'TukosButton', atts: {label: messages.more, onClick:lang.hitch(self, self.moreCallback)}},
	                        less: {type: 'TukosButton', atts: {label: messages.less, hidden: true, onClick:lang.hitch(self, self.lessCallback)}},
	                        tukosCustomViewContent: {type: 'ObjectEditor', 
	                        	atts: {title: Pmg.message('tukosCustomViewContent'), hasCheckboxes: true, hidden: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto', paddingRight: '25px'}, keyToHtml: 'capitalToBlank'}},
	                        defaultCustomViewContent: {type: 'ObjectEditor', 
	                        	atts: {title: messages.defaultCustomViewContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto', paddingRight: '25px'}, keyToHtml: 'capitalToBlank'}},
	                        itemCustomViewContent: {type: 'ObjectEditor', atts: {title: messages.itemCustomViewContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto'}, keyToHtml: 'capitalToBlank'}},
	                        itemCustomContent: {type: 'ObjectEditor', atts: {title: messages.itemCustomContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto'}, keyToHtml: 'capitalToBlank'}},
	                        customContentDelete: {type: 'TukosButton', atts: {title: messages.forselectedcustom, label: messages.customContentDelete,  onClick: lang.hitch(self, self.deleteCallback)}}
	                    },
	                    layout:{
	                        tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false, orientation: 'vert'},
	                        contents: {
	                            col1: {
	                                tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
	                                contents: {
	                                    row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, labelWidth: 100, orientation: 'vert'},  widgets: ['newCustomContent']},
	                                    row2: {
	                                        tableAtts: {cols: 3, customClass: 'labelsAndValues', id: 'viewsSettings', showLabels: false},
	                                        widgets: ['tukosCustomViewButton', 'tukosCustomViewLabel', 'tukosCustomView', 'defaultCustomViewButton', 'defaultCustomViewLabel', 'defaultCustomView', 
	                                        		  'itemCustomViewButton', 'itemCustomViewLabel',  'itemCustomView', 'itemCustomButton', 'itemCustomLabel']
	                                    },
	                                    row3: {
	                                        tableAtts: {cols: 5, customClass: 'labelsAndValues', showLabels: false, label: messages.selectAction},  
	                                        widgets: ['save', 'close', 'newCustomView', 'more', 'less']
	                                    }
	                                }
	                            },
	                            col2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['tukosCustomViewContent', 'defaultCustomViewContent', 'itemCustomViewContent', 'itemCustomContent']},
	                            col3: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['customContentDelete']}
	                        }      
	                    },
	                    style: {width: "auto"}
	                }});
	                customDialog.pane.blurCallback = on.pausable(customDialog, 'blur', customDialog.close);
	
	                ready(function(){
	                    lang.hitch(self, self.setVisibility)({hideMore: true, hideEmptyNewCustom: true});
	                    customDialog.open({around: targetNode});
	                    customDialog.pane.resize();
	                });
				});
            }
        },
        setVisibility: function(args){
            var  targetPane = this.currentPane(), form = targetPane.form || targetPane, pane = targetPane.customDialog.pane, viewMode = form.viewMode, isOverview = (viewMode === 'Overview'), isReadOnly = form.readonly,
                 paneGetWidget = lang.hitch(pane, pane.getWidget);
            paneGetWidget('tukosCustomView').set('value',  form.tukosviewid ? form.tukosviewid : '', false);
            paneGetWidget('defaultCustomView').set('value',  form.customviewid ? form.customviewid : '', false);
            paneGetWidget('itemCustomView').set('value',  form.itemcustomviewid ? form.itemcustomviewid : '', false);
            if ('hideMore' in args){
                if (Pmg.get('userRights') === 'SUPERADMIN'){
                	paneGetWidget('tukosCustomViewContent').set('hidden', args.hideMore);
                }
            	['tukosCustomViewContent', 'defaultCustomViewContent', 'customContentDelete'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', args.hideMore);
                });
                ['itemCustomViewContent', 'itemCustomContent'].forEach(function(widgetName){
                    var widget = paneGetWidget(widgetName);
                    widget.set('hidden', args.hideMore || isOverview);
                    widget.set('disabled', isReadOnly);
                });
                paneGetWidget('more').set('hidden', !args.hideMore);
                paneGetWidget('less').set('hidden', args.hideMore);
            }
            if (Pmg.get('userRights') === 'SUPERADMIN'){
            	['tukosCustomView', 'tukosCustomViewButton', 'tukosCustomViewLabel'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', false);
            	});
            }
            if ('hideEmptyNewCustom' in args){
                var hideNewCustom = utils.empty(form.customization) ? true : false,
                      isNewItem = (form.valueOf('id') === '');
                paneGetWidget('newCustomContent').set('value', form.customization);
                ['newCustomContent', 'tukosCustomViewButton', 'defaultCustomViewButton', 'save'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', hideNewCustom);
                });
                ['itemCustomLabel'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', hideNewCustom || isOverview);
                });
                ['itemCustomViewLabel', 'itemCustomView'].forEach(function(widgetName){
                        paneGetWidget(widgetName).set('hidden', isNewItem || isOverview);                        
                });
                ['itemCustomViewButton', 'itemCustomButton'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', hideNewCustom || isNewItem || isOverview);                        
                });
                var viewsSettingsWidget = /*registry.byId(targetPane.id + 'viewsSettings')*/pane.getWidget('viewsSettings'), itemCustomLabelWidget = paneGetWidget('itemCustomLabel');
                viewsSettingsWidget.set('label',  (hideNewCustom ? messages.customViewsSettings : messages.selectSaveOption));
                viewsSettingsWidget.set('cols',  (hideNewCustom ? 2 : 3));
                paneGetWidget('tukosCustomViewButton').set('disabled', (form.tukosviewid ? false : true));
                paneGetWidget('defaultCustomViewButton').set('disabled', (form.customviewid ? false : true));
                paneGetWidget('itemCustomViewButton').set('disabled', (form.itemcustomviewid && !isReadOnly ? false : true));
                paneGetWidget('itemCustomButton').set('disabled', isReadOnly);
                paneGetWidget('itemCustomView').set('disabled', isReadOnly);
                itemCustomLabelWidget.set('value',  isNewItem ?  '<i>' + messages.newItemCustom + '</i>' : messages.itemCustom);
                itemCustomLabelWidget.set('colspan', isNewItem ? 3 : 2);
            }
            if (Pmg.get('userRights') !== 'SUPERADMIN'){
            	['tukosCustomView', 'tukosCustomViewContent', 'tukosCustomViewButton', 'tukosCustomViewLabel'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', true);
            	});
            }
        }, 
        defaultCustomViewChange: function(tukosOrUser, newValue){
            var targetPane = this.currentPane(), form = targetPane.form || targetPane, formViewAtt = tukosOrUser === 'tukos' ? 'tukosviewid' : 'customviewid', 
            	viewButton = tukosOrUser === 'tukos' ? 'tukosCustomViewButton' : 'defaultCustomViewButton';
            form[formViewAtt] = newValue;
        	Pmg.refresh(targetPane, 'TabDefaultCustomViewIdSave', {customviewid: newValue, tukosOrUser: tukosOrUser}, {values: true, customization: true}).then(lang.hitch(this, function(){
                var pane= targetPane.customDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                paneGetWidget(viewButton).set('disabled', (newValue ? false : true));
                if (paneGetWidget('more').get('hidden')){
                	this.moreCallback();
                }
                if (targetPane.isAccordion()){
                	Pmg.addCustom(formViewAtt, newValue);
                }
            }));
        },
        itemCustomViewChange: function(newValue){
            var targetPane = this.currentPane(), form = targetPane.form || targetForm, custom = utils.setObject([form.viewMode.toLowerCase(), form.paneMode.toLowerCase()], {itemcustomviewid: newValue});
            form.itemcustomviewid = newValue;
			Pmg.refresh(targetPane, 'TabSave', {id: lang.hitch(form, form.valueOf)('id'), custom: custom}, {values: true, customization: true}).then(lang.hitch(this, function(){
                var pane = targetPane.customDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                paneGetWidget('itemCustomViewButton').set('disabled', ((form.itemcustomviewid && form.itemcustomviewid !== '') ? false : true));
                if (paneGetWidget('more').get('hidden')){
                	this.moreCallback();
                }
                if (targetPane.isAccordion()){
                	Pmg.addCustom('itemcustomviewid', newValue);
                }
           }));
        },
        newCustomView: function(){
            var targetPane = this.currentPane(), form = targetPane.form, vobject = form.object.toLowerCase(), view = form.viewMode.toLowerCase(), paneMode = form.paneMode.toLowerCase();;
            Pmg.tabs.request({object: 'customviews', view:  'Edit', action: 'Tab'}).then(
                function(){
                    ready(function(){
                        var targetPane = Pmg.tabs.currentPane(), form = targetPane.form;
                        form.setValueOf('vobject', vobject);
                        form.setValueOf('view', view);
                        form.setValueOf('panemode', paneMode);
                    });
            });
            targetPane.customDialog.close();
        },
        saveCallback: function(){
            var close = true, targetPane = this.currentPane(), form = targetPane.form, customDialog = targetPane.customDialog, pane = customDialog.pane;
            switch (customDialog.get('value').saveOption){
            case 'tukosCustomView':
                Pmg.refresh(targetPane, 'TabDefaultCustomViewSave', {customization: form.customization, tukosOrUser: 'tukos'}, {values: true});
                break;
            case 'defaultCustomView':
                Pmg.refresh(targetPane, 'TabDefaultCustomViewSave', {customization: form.customization, tukosOrUser: 'user'}, {values: true});
                break;
            case 'itemCustomView': 
                Pmg.tabs.refresh('TabItemCustomViewSave', {id: form.itemcustomviewid/*, vobject: form.object, view: form.viewMode*/, customization: form.customization}, {values: true});
                break;
            case 'itemCustom': 
                Pmg.tabs.refresh('TabSave', {custom: utils.setObject([form.viewMode.toLowerCase(), form.paneMode.toLowerCase()], form.customization)}, {values: true});
                break;
            default:
                close = false;
                Pmg.alert({title: messages.missingEntry, content: messages.mustSelectSaveOption}, pane.blurCallback);
            }
            /* here empty the newCustomContent ?*/
            if (close){
                this.setVisibility({hideMore: true});
                customDialog.close();
            }
        },
        moreCallback: function(){
            var targetPane = this.currentPane(), targetNode = this.currentPaneNode(), form = targetPane.form || targetPane, id = form.valueOf('id'),  customDialog = targetPane.customDialog, pane = customDialog.pane, 
            	getWidget = lang.hitch(pane, pane.getWidget);
            this.setVisibility({hideMore: false, hideEmptyNewCustom: true});
            Pmg.serverDialog({object: form.object, view: form.viewMode, mode: form.paneMode, action: 'CustomViewMore', query: id ? {id: id} : {}}).then(
                function(response){
                    ['tukosCustomView', 'defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
                        var contentName = customSet + 'Content'
                        getWidget(contentName).set('value', response[contentName] || {});
                    });
                    pane.resize();
                    customDialog.close();
                    customDialog.open({around: targetNode});
                }
            );
        },
        lessCallback: function(){
        	this.setVisibility({hideMore: true, hideEmptyNewCustom: true});
        	var customDialog = this.currentPane().customDialog;
        	customDialog.pane.resize();
        	customDialog.close();
        	customDialog.open({around: this.currentPaneNode()});
        },
        deleteCallback: function(){
            var targetPane = this.currentPane(), form =targetPane.form || targetPane, pane =targetPane.customDialog.pane,
                  getWidget = lang.hitch(pane, pane.getWidget),
                 toDelete  = {};
            ['tukosCustomView', 'defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
                var contentName = customSet + 'Content'
                var selectedLeaves = getWidget(contentName).get('selectedLeaves');
                if (selectedLeaves && !utils.empty(selectedLeaves)){
                    toDelete[customSet] = {items: selectedLeaves};
                    
                }
            });
            if (utils.empty(toDelete)){
                Pmg.alert({title: messages.missingEntry, content: messages.noCustomToDelete}, this.blurCallback);
            }else{
                if (toDelete.tukosCustomView){toDelete.tukosCustomView.viewId = form.tukosviewid;}
                if (toDelete.defaultCustomView){toDelete.defaultCustomView.viewId = form.customviewid;}
                if (toDelete.itemCustomView){toDelete.itemCustomView.viewId = form.itemcustomviewid;}                   
                Pmg.tabs.refresh('TabCustomDelete', toDelete, {values: true, customization: true}).then(
                    function(response){
                        var customContent = response.customContent;
                        for (var customSet in customContent){
                            getWidget(customSet + 'Content').set('value', customContent[customSet]);
                        }
                    }
                );
            }
        }
    });
});
