define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/on", "dojo/ready", "dijit/registry", 
         "dojo/request", "dojo/when", "tukos/utils", "tukos/TukosTooltipDialog", 
         "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, dct, on, ready, registry, request, when, utils, TukosTooltipDialog, Pmg, messages){
    return declare(null, {
        
        openCustomDialog: function(){
            var self = this, targetNode = this.currentPaneNode(), targetPane = this.currentPane(), form = targetPane.form || targetPane, customDialog = targetPane.customDialog;
            if (customDialog){
                customDialog.pane.getWidget('newCustomContent').set('value', form.customization);
                self.setVisibility({hideEmptyNewCustom: true});
                customDialog.open({around: targetNode});
               customDialog.pane.resize();
            }else{
                customDialog = targetPane.customDialog = new TukosTooltipDialog({paneDescription: {form: form, 
                    widgetsDescription: {
                        newCustomContent: {type: 'ObjectEditor', atts: {label: messages.newCustomContent, keyToHtml: 'capitalToBlank', style: {maxWidth: '600px', maxHeight: '600px', overflow: 'auto'}}},
                        defaultCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'defaultCustomView'}}, 
                        defaultCustomViewLabel: {type: 'HtmlContent', atts: {value: messages.defaultCustomView, disabled: true}},
                        defaultCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(this, this.defaultCustomViewChange)}},
                        itemCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustomView'}}, 
                        itemCustomViewLabel: {type: 'HtmlContent', atts: {style: {width: '180px'}, value: messages.itemCustomView}},
                        itemCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', mode: form.paneMode, dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(this, this.itemCustomViewChange)}},
                        itemCustomButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustom'}}, 
                        itemCustomLabel: {type: 'HtmlContent', atts: {}},
                        save: {type: 'TukosButton', atts: {label: messages.save, onClick: lang.hitch(this, this.saveCallback)}},
                        close: {type: 'TukosButton', atts: {label: messages.close, onClickAction:  "this.pane.close();"}},
                        newCustomView: {type: 'TukosButton', atts: {label: messages.newCustomView, onClick: lang.hitch(this, this.newCustomView)}},
                        more: {type: 'TukosButton', atts: {label: messages.more, onClick:lang.hitch(this, this.moreCallback)}},
                        less: {type: 'TukosButton', atts: {label: messages.less, hidden: true, onClick:lang.hitch(this, this.lessCallback)}},
                        defaultCustomViewContent: {type: 'ObjectEditor', atts: {title: messages.defaultCustomViewContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto', paddingRight: '25px'}, keyToHtml: 'capitalToBlank'}},
                        itemCustomViewContent: {type: 'ObjectEditor', atts: {title: messages.itemCustomViewContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto'}, keyToHtml: 'capitalToBlank'}},
                        itemCustomContent: {type: 'ObjectEditor', atts: {title: messages.itemCustomContent, hasCheckboxes: true, style: {maxHeight: '400px', maxWidth: '600px', overflow: 'auto'}, keyToHtml: 'capitalToBlank'}},
                        customContentDelete: {type: 'TukosButton', atts: {title: messages.forselectedcustom, label: messages.customContentDelete,  onClick: lang.hitch(this, self.deleteCallback)}}
                    },
                    layout:{
                        tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false, orientation: 'vert'},
                        contents: {
                            col1: {
                                tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                                contents: {
                                    row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, labelWidth: 100, orientation: 'vert'},  widgets: ['newCustomContent']},
                                    row2: {
                                        tableAtts: {cols: 3, customClass: 'labelsAndValues', id: targetPane.id + 'viewsSettings', showLabels: false},
                                        widgets: ['defaultCustomViewButton', 'defaultCustomViewLabel', 'defaultCustomView', 'itemCustomViewButton', 'itemCustomViewLabel',  'itemCustomView', 'itemCustomButton', 'itemCustomLabel']
                                    },
                                    row3: {
                                        tableAtts: {cols: 5, customClass: 'labelsAndValues', showLabels: false, label: messages.selectAction},  
                                        widgets: ['save', 'close', 'newCustomView', 'more', 'less']
                                    }
                                }
                            },
                            col2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['defaultCustomViewContent', 'itemCustomViewContent', 'itemCustomContent']},
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
            }
        },

        setVisibility: function(args){
            var  targetPane = this.currentPane(), form = targetPane.form || targetPane, pane = targetPane.customDialog.pane, viewMode = form.viewMode, isOverview = (viewMode === 'Overview'), isReadOnly = form.readOnly;
                    paneGetWidget = lang.hitch(pane, pane.getWidget);
            paneGetWidget('defaultCustomView').set('value',  form.customviewid ? form.customviewid : '', false);
            paneGetWidget('itemCustomView').set('value',  form.itemcustomviewid ? form.itemcustomviewid : '', false);
            if ('hideMore' in args){
                ['defaultCustomViewContent', 'customContentDelete'].forEach(function(widgetName){
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
            if ('hideEmptyNewCustom' in args){
                var hideNewCustom = utils.empty(form.customization) ? true : false,
                      isNewItem = (form.valueOf('id') === '');
                paneGetWidget('newCustomContent').set('value', form.customization);
                ['newCustomContent', 'defaultCustomViewButton', 'save'].forEach(function(widgetName){
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
                var viewsSettingsWidget = registry.byId(targetPane.id + 'viewsSettings'), itemCustomLabelWidget = paneGetWidget('itemCustomLabel');
                viewsSettingsWidget.set('label',  (hideNewCustom ? messages.customViewsSettings : messages.selectSaveOption));
                viewsSettingsWidget.set('cols',  (hideNewCustom ? 2 : 3));
                paneGetWidget('defaultCustomViewButton').set('disabled', (form.customviewid ? false : true));
                paneGetWidget('itemCustomViewButton').set('disabled', (form.itemcustomviewid && !isReadOnly ? false : true));
                paneGetWidget('itemCustomButton').set('disabled', isReadOnly);
                paneGetWidget('itemCustomView').set('disabled', isReadOnly);
                itemCustomLabelWidget.set('value',  isNewItem ?  '<i>' + messages.newItemCustom + '</i>' : messages.itemCustom);
                itemCustomLabelWidget.set('colspan', isNewItem ? 3 : 2);
            }
        }, 

        defaultCustomViewChange: function(newValue){
            var targetPane = this.currentPane(), form = targetPane.form || targetPane;
            form.customviewid = newValue;
        	Pmg.refresh(targetPane, 'TabDefaultCustomViewIdSave', {customviewid: newValue}, {values: true, customization: true}).then(lang.hitch(this, function(){
                var pane= targetPane.customDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                paneGetWidget('defaultCustomViewButton').set('disabled', ((form.customviewid && form.customviewid !== '') ? false : true));
                if (paneGetWidget('more').get('hidden')){
                	this.moreCallback();
                }
                if (targetPane.isAccordion()){
                	Pmg.addCustom('customviewid', newValue);
                }
            }));
        },

        itemCustomViewChange: function(newValue){
            var targetPane = this.currentPane(), form = targetPane.form || targetForm, custom = utils.setObject([form.viewMode.toLowerCase(), form.paneMode.toLowerCase()], {itemcustomviewid: newValue});
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
            var targetPane = this.currentPane(), form = targetPane.form, object = form.object, view = form.viewMode;
            Pmg.tabs.request({object: 'customviews', view:  'Edit', action: 'Tab'}).then(
                function(){
                    ready(function(){
                        var targetPane = Pmg.tabs.currentPane(), form = targetPane.form;
                        form.getWidget('vobject').set('value', object);
                        form.getWidget('view').set('value',  view);
                    });
            });
            targetPane.customDialog.close();
        },

        saveCallback: function(){
            var close = true, targetPane = this.currentPane(), form = targetPane.form, customDialog = targetPane.customDialog, pane = customDialog.pane;
            switch (customDialog.get('value').saveOption){
                case 'defaultCustomView':
                    Pmg.refresh(targetPane, 'TabDefaultCustomViewSave', form.customization, {values: true});
                    break;
                case 'itemCustomView': 
                    Pmg.tabs.refresh('TabItemCustomViewSave', {id: form.itemcustomviewid/*, vobject: form.object, view: form.viewMode.toLowerCase()*/, customization: form.customization}, {values: true});
                    break;
                case 'itemCustom': 
                    Pmg.tabs.refresh('TabSave', {custom: utils.setObject([form.viewMode.toLowerCase(), form.paneMode.toLowerCase()], form.customization)}, {values: true});
                    break;
                default:
                    close = false;
                    Pmg.alert({title: messages.missingEntry, content: messages.mustSelectSaveOption}, pane.blurCallback);
            }
            if (close){
                this.setVisibility({hideMore: true});
                customDialog.close();
            }
        },

        moreCallback: function(){
            var targetPane = this.currentPane(), targetNode = this.currentPaneNode(), form = targetPane.form || targetPane, id = form.valueOf('id'),  customDialog = targetPane.customDialog, pane = customDialog.pane, getWidget = lang.hitch(pane, pane.getWidget);
            this.setVisibility({hideMore: false});
            Pmg.serverDialog({object: form.object, view: form.viewMode.toLowerCase(), mode: form.paneMode.toLowerCase(), action: 'CustomViewMore', query: id ? {id: id} : {}}).then(
                function(response){
                    ['defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
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
        	this.setVisibility({hideMore: true});
        	var customDialog = this.currentPane().customDialog;
        	customDialog.pane.resize();
        	customDialog.close();
        	customDialog.open({around: this.currentPaneNode()});
        },

 
        deleteCallback: function(){
            var targetPane = this.currentPane(), form =targetPane.form || targetPane, pane =targetPane.customDialog.pane,
                  getWidget = lang.hitch(pane, pane.getWidget),
                 toDelete  = {};
            ['defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
                var contentName = customSet + 'Content'
                var selectedLeaves = getWidget(contentName).get('selectedLeaves');
                if (selectedLeaves && !utils.empty(selectedLeaves)){
                    toDelete[customSet] = {items: selectedLeaves};
                    
                }
            });
            if (utils.empty(toDelete)){
                Pmg.alert({title: messages.missingEntry, content: messages.noCustomToDelete}, this.blurCallback);
            }else{
                if (toDelete.defaultCustomView){toDelete.defaultCustomView.viewId = form.customviewid;}
                if (toDelete.itemCustomView){toDelete.itemCustomView.viewId = form.itemcustomviewid;}                   
                Pmg.tabs.refresh('CustomDelete', toDelete, {values: true, customization: true}).then(
                    function(response){
                        //console.log('in delectecallback response');
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
