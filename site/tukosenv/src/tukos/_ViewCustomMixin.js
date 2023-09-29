define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/ready", "dijit/registry", 
         "tukos/utils",  "tukos/PageManager", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, on, ready, registry, utils, Pmg){
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
	                        newCustomContent: {type: 'ObjectEditor', atts: lang.mixin({label: Pmg.message('newCustomContent')}, self.objectEditorCommonAtts(false, '600px'))},
	                        tukosCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'tukosCustomView', hidden: true}}, 
	                        tukosCustomViewLabel: {type: 'HtmlContent', atts: {value: Pmg.message('tukosCustomView'), hidden: true, disabled: true}},
	                        tukosCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, hidden: true,
	                        	onChange: lang.hitch(self, self.defaultCustomViewChange, 'tukos')}},
	                        defaultCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'defaultCustomView'}}, 
	                        defaultCustomViewLabel: {type: 'HtmlContent', atts: {value: Pmg.message('defaultCustomView'), disabled: true}},
	                        defaultCustomView: {type: 'ObjectSelect', atts: {object: 'customviews', dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(self, self.defaultCustomViewChange, 'user')}},
	                        itemCustomViewButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustomView'}}, 
	                        itemCustomViewLabel: {type: 'HtmlContent', atts: {style: {width: '180px'}, value: Pmg.message('itemCustomView')}},
	                        itemCustomView: {type: 'ObjectSelect', 
	                        	atts: {object: 'customviews', mode: form.paneMode, dropdownFilters: {vobject: form.object, view: form.viewMode, panemode: form.paneMode}, onChange: lang.hitch(self, self.itemCustomViewChange)}},
	                        itemCustomButton: {type: 'RadioButton', atts: {name: 'saveOption', value: 'itemCustom'}}, 
	                        itemCustomLabel: {type: 'HtmlContent', atts: {}},
	                        save: {type: 'TukosButton', atts: {label: Pmg.message('Save'), onClick: lang.hitch(self, self.saveCallback)}},
	                        close: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClickAction:  "this.pane.close();"}},
	                        newCustomView: {type: 'TukosButton', atts: {label: Pmg.message('newCustomView'), onClick: lang.hitch(self, self.newCustomView)}},
	                        more: {type: 'TukosButton', atts: {label: Pmg.message('more') + '...', onClick:lang.hitch(self, self.moreCallback)}},
	                        less: {type: 'TukosButton', atts: {label: Pmg.message('less') + '...', hidden: true, onClick:lang.hitch(self, self.lessCallback)}},
	                        tukosCustomViewContent: {type: 'ObjectEditor', atts: lang.mixin({title: Pmg.message('tukosCustomViewContent'), hidden: true}, self.objectEditorCommonAtts(true))}, 
	                        defaultCustomViewContent: {type: 'ObjectEditor', atts: lang.mixin({title: Pmg.message('defaultCustomViewContent')}, self.objectEditorCommonAtts(true))},
	                        itemCustomViewContent: {type: 'ObjectEditor', atts: lang.mixin({title: Pmg.message('itemCustomViewContent')}, self.objectEditorCommonAtts(true))},
	                        itemCustomContent: {type: 'ObjectEditor', atts: lang.mixin({title: Pmg.message('itemCustomContent')}, self.objectEditorCommonAtts(true))},
	                        customContentDelete: {type: 'TukosButton', atts: {title: Pmg.message('For selected items'), label: Pmg.message('customContentDelete'),  hidden: true, onClick: lang.hitch(self, self.deleteCallback)}}
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
	                                        tableAtts: {cols: 5, customClass: 'labelsAndValues', showLabels: false, label: Pmg.message('selectAction')},  
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
        objectEditorCommonAtts: function(hasCheckBoxes, maxHeight){
			const self = this, maxCustomContentWidth = (dojo.window.getBox().w*0.5) + 'px', maxColWidth  = '6em';
			return {hasCheckboxes: hasCheckBoxes, style: {maxHeight: maxHeight || '400px', maxWidth: maxCustomContentWidth, overflow: 'auto', paddingRight: '25px'}, keyToHtml: 'capitalToBlank', maxColWidth: maxColWidth, checkBoxChangeCallback: function(){
				const form = this.form, customToDelete = this.form.getWidget('customContentDelete'), isHidden = customToDelete.get('hidden'), willBeHidden = utils.empty(self.leavesToDelete(lang.hitch(form, form.getWidget)));
				if (willBeHidden !== isHidden){
					customToDelete.set('hidden', willBeHidden);
					form.resize();
				}
			}};
		},
        setVisibility: function(args){
            var  targetPane = this.currentPane(), form = targetPane.form || targetPane, pane = targetPane.customDialog.pane, viewMode = form.viewMode, isOverview = (viewMode === 'Overview'), isReadOnly = form.readonly,
                 paneGetWidget = lang.hitch(pane, pane.getWidget);
            paneGetWidget('tukosCustomView').set('value',  form.tukosviewid ? form.tukosviewid : '', false);
            paneGetWidget('defaultCustomView').set('value',  form.customviewid ? form.customviewid : '', false);
            paneGetWidget('itemCustomView').set('value',  form.itemcustomviewid ? form.itemcustomviewid : '', false);
            if ('hideMore' in args){
                if (Pmg.isAtLeastAdmin()){
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
            if (Pmg.isAtLeastAdmin()){
            	['tukosCustomView', 'tukosCustomViewButton', 'tukosCustomViewLabel'].forEach(function(widgetName){
                    paneGetWidget(widgetName).set('hidden', false);
            	});
            }
            if (args.hideEmptyNewCustom){
                var hideNewCustom = utils.empty(form.customization) ? true : false,
                      isNewItem = (form.valueOf('id') === '');
                paneGetWidget('newCustomContent').set('value', this.translatedContent(lang.clone(form.customization), form));
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
                var viewsSettingsWidget = pane.getWidget('viewsSettings'), itemCustomLabelWidget = paneGetWidget('itemCustomLabel');
                viewsSettingsWidget.set('label',  (hideNewCustom ? Pmg.message('customViewsSettings') : Pmg.message('selectSaveOption')));
                viewsSettingsWidget.set('cols',  (hideNewCustom ? 2 : 3));
                paneGetWidget('tukosCustomViewButton').set('disabled', (form.tukosviewid ? false : true));
                paneGetWidget('defaultCustomViewButton').set('disabled', (form.customviewid ? false : true));
                paneGetWidget('itemCustomViewButton').set('disabled', (form.itemcustomviewid && !isReadOnly ? false : true));
                paneGetWidget('itemCustomButton').set('disabled', isReadOnly);
                paneGetWidget('itemCustomView').set('disabled', isReadOnly);
                itemCustomLabelWidget.set('value',  isNewItem ?  '<i>' + Pmg.message('newItemCustom') + '</i>' : Pmg.message('itemCustomContent'));
                itemCustomLabelWidget.set('colspan', isNewItem ? 3 : 2);
            }
            if (!Pmg.isAtLeastAdmin()){
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
                	this.moreCallback({hideMore: false, hideEmptyNewCustom: false});
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
                	this.moreCallback({hideMore: false, hideEmptyNewCustom: false});
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
                Pmg.alert({title: Pmg.message('missingEntry'), content: Pmg.message('mustSelectSaveOption')}, pane.blurCallback);
            }
            /* here empty the newCustomContent ?*/
            if (close){
                this.setVisibility({hideMore: true});
                customDialog.close();
            }
        },
        moreCallback: function(options){
            var self = this, targetPane = this.currentPane(), targetNode = this.currentPaneNode(), form = targetPane.form || targetPane, id = form.valueOf('id'),  customDialog = targetPane.customDialog, pane = customDialog.pane, 
            	getWidget = lang.hitch(pane, pane.getWidget);
            this.setVisibility({hideMore: 'hideMore' in options ? options.hideMore : false, hideEmptyNewCustom: 'hideEmptyNewCustom' in options ? options.hideEmptyNewCustom : true});
			getWidget('customContentDelete').set('hidden', utils.empty(this.leavesToDelete(getWidget)));
            Pmg.serverDialog({object: form.object, view: form.viewMode, mode: form.paneMode, action: 'CustomViewMore', query: id ? {id: id} : {}}).then(
                function(response){
                    ['tukosCustomView', 'defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
                        const contentName = customSet + 'Content', contentWidget = getWidget(contentName), customContent = response[contentName];
                        contentWidget.set('value', self.translatedContent(customContent, form) || {});
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
        leavesToDelete: function(getWidget){
            var toDelete = {};
            ['tukosCustomView', 'defaultCustomView', 'itemCustomView', 'itemCustom'].forEach(function(customSet){
                var contentName = customSet + 'Content'
                var selectedLeaves = getWidget(contentName).get('selectedLeaves');
                if (selectedLeaves && !utils.empty(selectedLeaves)){
                    toDelete[customSet] = {items: selectedLeaves};
                }
            });
            return toDelete;
		},
        deleteCallback: function(){
            var targetPane = this.currentPane(), form =targetPane.form || targetPane, pane =targetPane.customDialog.pane,
                  getWidget = lang.hitch(pane, pane.getWidget),
                 toDelete  = this.leavesToDelete(getWidget);
            if (utils.empty(toDelete)){
                Pmg.alert({title: Pmg.message('missingEntry'), content: Pmg.message('noCustomToDelete')}, this.blurCallback);
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
                        getWidget('customContentDelete').set('hidden', true);
                        form.resize();
                    }
                );
            }
        },
        translatedContent: function(customContent, form){
			let columnsDescription;
			const translateContent = function(customContent, description){
				utils.forEach(customContent, function(subContent, key){
					const subDescription = typeof description === 'object' ? description[key] : {};
					if (typeof subContent === 'object'){
						switch (key){
							case 'widgetsDescription':
								utils.forEach(subContent, function(widgetContent, widgetName){
									translateContent(widgetContent, (subDescription || {})[widgetName]);
									widgetContent["#tKey"] = columnsDescription ? columnsDescription[widgetName].label : (((subDescription || {})[widgetName] || {}).atts || {}).label;
								});
								break;
							case 'columns':
								utils.forEach(subContent, function(widgetContent, widgetName){
									translateContent(widgetContent, subDescription[widgetName]);
									widgetContent["#tKey"] = subDescription[widgetName].label;
								});
								break;
							case 'series':
								utils.forEach(subContent, function(widgetContent, widgetName){
									translateContent(widgetContent, subDescription[widgetName]);
									widgetContent["#tKey"] = subDescription[widgetName].options.label;
								});
								break;
							case 'editDialogAtts':
								columnsDescription = description.columns;
								translateContent(subContent, subDescription);
								columnsDescription = false;
								break;
							default:
								translateContent(subContent, subDescription);
						}
						subContent["#tKey"] = Pmg.message(key, form.object);
					}else{
						customContent[key] = {"#tKey": Pmg.message(key, form.object), "#leafValue": subContent};
					}
				});
				return customContent;			
			}
			translateContent(customContent, form);
			return customContent;
		}
    });
});
