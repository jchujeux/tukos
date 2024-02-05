define (["dojo/_base/array", "dojo/_base/lang", "dojo/dom-style", "dojo/ready", "tukos/utils", "tukos/menuUtils",  "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], function(arrayUtil, lang, dst, ready, utils, mutils,  Pmg, messages){

        var   sizeUnits = [{id: 'auto', name: 'auto'}, {id: '%', name: '%'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}],
                sizeConstraintUnits =  [{id: '%', name: '%'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}],
                filtersUnits = [{id: 'yes', name: messages.yes}, {id: 'no', name: messages.no}],

                stylewidth = {root: 'style', att: 'width', name: messages.width, units: sizeUnits},
                minWidth = {root: 'style', att: 'minWidth', name: messages.minWidth, units: sizeConstraintUnits}, 
                maxWidth = {root: 'style', att: 'maxWidth', name: messages.maxWidth, units: sizeConstraintUnits},
                styleheight ={root: 'style', att: 'height', name: messages.height, units: sizeUnits},
                minHeight = {root: 'style', att: 'minHeight', name: messages.minHeight, units: sizeConstraintUnits},
                maxHeight = {root: 'style', att: 'maxHeight', name: messages.maxHeight, units: sizeConstraintUnits},
                valueAtt = {att: 'value', name: Pmg.message('value'), type: 'TextBox'},

                width = {stylewidth: stylewidth},
                value = {value: valueAtt},
                valueAndWidth = {value: valueAtt, stylewidth: stylewidth},
                height = {styleheight: styleheight},
                widthAndHeight = {stylewidth: stylewidth, styleheight: styleheight},
                widthConstraints = {stylewidth: stylewidth, minWidth: minWidth, maxWidth: maxWidth},
                valueAndWidthConstraints = lang.mixin({value: valueAtt}, widthConstraints),
                heightConstraints = {stylewidth: stylewidth, styleheight: styleheight, minHeight: minHeight, maxHeight: maxHeight},
                valueAndHeightConstraints = lang.mixin({value: valueAtt}, heightConstraints),
                widthAndHeightConstraints = {stylewidth: stylewidth, minWidth: minWidth, maxWidth: maxWidth, styleheight: styleheight, minHeight: minHeight, maxHeight: maxHeight},
                dgridCustomAtts = {
                    maxHeight: {att: 'maxHeight', name: messages.maxHeight, units: sizeConstraintUnits}, 
                    maxWidth: {att: 'maxWidth', name: messages.maxWidth, units: sizeConstraintUnits}, 
                    allowApplicationFilter: {att: 'allowApplicationFilter', name: messages.allowApplicationFilter, units: filtersUnits},
                    hideServerFilters: {att: 'hideServerFilters', name: messages.hideServerFilters, units: filtersUnits}
                },
                widgetsCustomAtts = {
                    CheckBox: value, TextBox: valueAndWidth,  NumberTextBox: valueAndWidth,  CurrencyTextBox: valueAndWidth,  TimeTextBox: valueAndWidth,
                    TukosNumberBox: valueAndWidth, TukosCurrencyBox: valueAndWidth,
                    Textarea: valueAndHeightConstraints,  //Select: {autoWidth: true/false, maxHeight, // not used //Button: "dijit/form/", // no need to customize
                    TukosDateBox:valueAndHeightConstraints,  //Editor: {}, // no need to customize
                    FormattedTextBox: valueAndHeightConstraints, MultiSelect: valueAndWidthConstraints, 
                    StoreSelect: valueAndWidthConstraints, ObjectSelect:  valueAndWidthConstraints,  ObjectSelectMulti:  valueAndWidthConstraints,  ObjectSelectDropDown: valueAndWidthConstraints,
                    NumberUnitBox: valueAndWidth, DateTimeBox: valueAndWidth,  SimpleDgrid: dgridCustomAtts,  StoreDgrid: dgridCustomAtts, OverviewDgrid: dgridCustomAtts, MobileOverviewGrid: dgridCustomAtts,
                    ContextTree: valueAndWidth, NavigationTree: widthAndHeightConstraints, PieChart: width, ColumnsChart: width,  Chart: width, Uploader: width, Downloader: width, StoreCalendar: widthAndHeight, StoreSimpleCalendar: widthAndHeight
                },
                    
                widgetCustomDialogDescription = {
                    widgetsDescription: {
                        att: {type: 'StoreSelect', atts: {widgetName: 'attribute', title: messages.attribute, placeHolder: messages.attribute + '  ...', style: {width: 'auto', maxWidth: '15em'}, storeArgs: {}}},
            			NumberUnitBox: {type: 'NumberUnitBox', atts: {widgetName: 'attValue', hidden: true, number: {style: {width: '3em'}, disabled: true}, unit: {placeHolder: messages.enterunit, style: {width: 'auto'}, storeArgs: {data:[]}}}}, 
            			TextBox: {type: 'TextBox', atts: {widgetName: 'attValue', style: {width: '20em'}, hidden: true}},
            			TukosTextarea: {type: 'TukosTextarea', atts: {widgetName: 'attValue', style: {width: '20em'}, hidden: true}},
            			StoreSelect: {type: 'StoreSelect', atts: {placeHolder: messages.selectvalue, style: {width: 'auto'}, hidden: true, storeArgs: {data: []}}},
            			RestSelect: {type: 'RestSelect', atts: {placeHolder: messages.selectvalue, style: {width: 'auto'}, hidden: true}},
						MultiSelect: {type: 'MultiSelect', atts: {widgetName: 'attValue', hidden: true, style: {width: 'auto', height: '200px'}}},
						SimpleDgridNoDnd: {type: 'SimpleDgridNoDnd', atts: {widgetName: 'attValue', hidden: true, dynamicColumns: true, adjustLastColumn: false, style: {maxWidth: '1000px'}}},
                        cancel: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClickAction:  'this.pane.close();'}},
                        apply: {type: 'TukosButton', atts: {label: Pmg.message('apply')}},
                        import: {type: 'TukosButton', atts: {label: Pmg.message('import')}},
                        export: {type: 'TukosButton', atts: {label: Pmg.message('export')}}
                    },
                    layout:{
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},
                        contents: {
                           row1: {tableAtts: {cols: 6, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['att', 'cancel', 'apply', 'import', 'export']},
                           row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['NumberUnitBox', 'TextBox', 'TukosTextarea', 'StoreSelect', 'RestSelect', 'MultiSelect', 'SimpleDgridNoDnd']}
                        }
                    }
                },
                widgetCustomDialog = false,
                widgetTypesAtts = {};
    return {
        sizeAtt: function(attName){
        	return {att: attName, type: 'NumberUnitBox', name: Pmg.message(attName), units: sizeUnits};
        },
        yesOrNoAtt: function(attName){
        	return {att: attName, type: 'StoreSelect', name: Pmg.message(attName), storeArgs: {data: [{id: '', name: ''}, {id: 'yes', name: Pmg.message('yes')}, {id: 'no', name: Pmg.message('no')}]}};
        },
        customizationContextMenuItems: function(widget, column){
    		var self = this, widgetType = column ? column.widgetType : widget.widgetType;
    		return (widgetsCustomAtts[widgetType] || widget.customizableAtts) ? [{atts: {label: messages.customizeWidget, onClick: function(evt){ lang.hitch(self, self.customDialogCallback)(widget, evt, column);}}}] : [];
    	},
    	idColsContextMenuItems: function(widget){
    		var self = this;
    		if (Pmg.isRestrictedUser()){
				return {};
    		}else if (Pmg.mayHaveNavigator()){
    			return [{atts: {label: messages.editinnewtab  , onClick: function(evt){self.editInNewTab(widget)}}}, {atts: {label: messages.showinnavigator, onClick: function(evt){self.showInNavigator(widget)}}}];
    		}else{
    			return [{atts: {label: messages.editinnewtab  , onClick: function(evt){self.editInNewTab(widget)}}}];
    		}
    	},
        setWidgetTypeAtts: function(widget){
            var widgetType = widget.widgetType;
            if (!widgetTypesAtts[widgetType]){
                var customAtts = widgetsCustomAtts[widgetType];
                var attsStoreData = [{id: '', name: ''}];
                for (var att in customAtts){
                    attsStoreData.push({id: att, name: customAtts[att].name});
                }
                widgetTypesAtts[widgetType] = {customAtts: customAtts, storeData: attsStoreData};
            }
            var pane = widgetCustomDialog.pane,
                attWidget = pane.getWidget('att'),
                attWidgetAtts = widgetTypesAtts[widgetType],
                customAtts = lang.mixin({}, attWidgetAtts.customAtts),
                attStoreData = arrayUtil.map(attWidgetAtts.storeData, function(item){return item;}),
                customizableAtts = widget.customizableAtts;
            for (var att in customizableAtts){
                customAtts[att] = customizableAtts[att];
                attStoreData.push({id: att, name: customAtts[att].name});
            }
            attWidget.store.setData(attStoreData);
            this.customAtts = customAtts;
        },

        attWatchCallback: function(attr, oldValue, newValue){
            var self = this, pane = widgetCustomDialog.pane, getWidget = lang.hitch(pane, pane.getWidget), customAtts = this.customAtts,
            	attValueWidgets = {NumberUnitBox: getWidget('NumberUnitBox'), TextBox: getWidget('TextBox'), TukosTextarea: getWidget('TukosTextarea'), StoreSelect: getWidget('StoreSelect'), RestSelect: getWidget('RestSelect'),
					MultiSelect: getWidget('MultiSelect'), SimpleDgridNoDnd: getWidget('SimpleDgridNoDnd')};
            if (newValue === ''){
            	var attValueType = '', attValueWidget = this.attValueWidget = attValueWidgets.NumberUnitBox;
            }else{
            	var atts = customAtts[newValue], attValueType = atts.type || 'NumberUnitBox', attValueWidget = this.attValueWidget = attValueWidgets[attValueType];
            }
            utils.forEach(attValueWidgets, function(widget, widgetType){
            	widget.set('hidden', widgetType !== attValueType);
            	if (widgetType === attValueType){
            		switch(widgetType){
            			case 'NumberUnitBox':
            				widget.unitField.store.setData(atts ? atts.units : []);
            				widget.numberField.set('disabled', true);
            				break;
            			case 'StoreSelect':
            				widget.store.setData(atts.storeArgs.data);
            				break;
            			case 'RestSelect':
            	            widget.set('dropdownFilters', atts.dropDownFilters);
            	            widget.storeArgs = lang.mixin(widget.storeArgs, atts.atts.storeArgs);
            				widget.set('store', Pmg.store(widget.storeArgs));
							break;
						case 'MultiSelect':
							widget.set('options', atts.options);
							break;
						case 'SimpleDgridNoDnd':
							widget.set('columns', atts.atts.columns);
							widget.set('store', atts.atts.storeArgs);
							//if (atts.atts.style){
								dst.set(widget.domNode, atts.atts.style || widget.style);
							//}
							if (atts.atts.initialRowValue){
								widget.set('initialRowValue', atts.atts.initialRowValue);
							}else{
								delete widget.initialRowValue;
							}
							if (atts.atts.onCellClickAction){
								widget.set('onCellClickAction', atts.atts.onCellClickAction);
							}else{
								delete widget.onCellClickAction;
							}
            		}
            		if (atts && atts.atts && atts.atts.tukosTooltip){
						//widget.form = self.widget.form;
						widget.set('tukosTooltip', atts.atts.tukosTooltip);
					}else{
						widget.set('tukosTooltip', {});
					}
            		widget.set('value', self.currentAttValue());
            	}
            });
			pane.resize();
			if (attValueType === 'SimpleDgridNoDnd'){setTimeout(function(){pane.resize();}, 0);}//for dgrid's noDataMessage not to overlap header
        },
        attValueUnitWatchCallback: function(attr, oldValue, newValue){
            var attValueWidget = this.attValueWidget;
            if (newValue === ''){
                attValueWidget.numberField.set('value','');
                attValueWidget.numberField.set('disabled', true);
            }else{
                var noNumberUnits = {auto: true, yes: true, no: true};
                if (noNumberUnits[newValue]){
                    attValueWidget.numberField.set('value','');
                    attValueWidget.numberField.set('disabled', true);
                }else{
                    attValueWidget.numberField.set('disabled', false);
                }
            }
        },

        applyCustom: function(){
            var widget = this.widget, widgetPane = widget.pane, pane = widgetCustomDialog.pane, attTarget = pane.valueOf('att'), attInfo = this.customAtts[attTarget], att = attInfo.att, attRoot = attInfo.root || att, 
                oldAtt = widget.get(attRoot), attObject = (oldAtt === '' && attRoot !== att ? {} : oldAtt), oldAttValue = (attRoot === att ? attObject : attObject[att]), newAttValue = this.newAttValue(), newAtt;
            //if (oldAttValue !== newAttValue){
                newAtt = attRoot === att ? newAttValue : utils.newObj([[att, newAttValue]]);//{[att]: newAttValue});
                widget.set(attRoot, newAtt);
                lang.setObject((widget.itemCustomization || 'customization') + (attRoot === 'value' ? ('.data.value.' + widget.widgetName) : ('.widgetsDescription.' + widget.widgetName + '.atts.' + attRoot)), newAtt, widgetPane);
            //}
        },

        applyGridEditorCustom: function(){
            var column = this.column, grid = column.grid, widget = this.widget, gridPane = grid.pane, pane = widgetCustomDialog.pane, attTarget = pane.valueOf('att'), attInfo = this.customAtts[attTarget], 
            	att = attInfo.att, attRoot = attInfo.root || att, oldAtt = widget.get(attRoot), attObject = (oldAtt === '' && attRoot !== att ? {} : oldAtt), oldAttValue = (attRoot === att ? attObject : attObject[att]),
            	newAttValue = this.newAttValue(), newAttObject = {};
            if (oldAttValue !== newAttValue){
                if (attRoot !== att){
                     newAttObject[att] = attObject[att] = newAttValue;
                }else{
                     newAttObject[att] = attObject = newAttValue;
                    }
                widget.set(attRoot, attObject);
                lang.setObject((grid.itemCustomization || 'customization') + '.widgetsDescription.' + grid.widgetName + '.atts.columns.' + column.field + '.editorArgs' + (attRoot === att ? '' : ('.' + attRoot)), newAttObject, gridPane);
            }
        },

        currentAttValue: function(){
            var widget = this.widget, pane = widgetCustomDialog.pane, attTarget = pane.valueOf('att');
            if (attTarget){
            	var attValueWidget = this.attValueWidget, attInfo = this.customAtts[attTarget], att = attInfo.att,  attRoot = attInfo.root || att, oldAttObject = widget.get(attRoot),
            		oldAttValue = (attRoot === att ? oldAttObject : oldAttObject[att]);
				switch (attValueWidget.widgetType){
					case 'NumberUnitBox': 
	    	            switch (typeof oldAttValue){
	    	                case 'string': return '[' + (/\d+/.exec(oldAttValue) || '""') + ',"' + /[a-z]+/i.exec(oldAttValue) + '"]';
	    	                case 'number': return '[' + oldAttValue + ', ""]';
	    	                default: return '';
	    	            }
					case 'SimpleDgridNoDnd':
						return oldAttValue ? JSON.parse(oldAttValue) : oldAttValue;
					default:
						return oldAttValue || '';
        		}
           }else{
        	   	return '';
           }
        },
                    
        customDialogCallback: function(widget, evt, column){
            var self = this;
			evt.preventDefault();
            evt.stopPropagation();
            if (widgetCustomDialog){
                this.customDialogOpen(widget, evt, column);
            }else{
            require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                widgetCustomDialog = new TukosTooltipDialog({paneDescription: widgetCustomDialogDescription});
                ready(function(){
					var pane = widgetCustomDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
	                paneGetWidget('att').watch('value',  lang.hitch(self, self.attWatchCallback));
	                paneGetWidget('NumberUnitBox').unitField.watch('value', lang.hitch(self, self.attValueUnitWatchCallback));
					self.customDialogOpen(widget, evt, column);
				});
            });
			}
        },   
        customDialogOpen: function(widget, evt, column){
            var self = this;
			ready(function(){
                var pane = widgetCustomDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                const importExportHideValue = utils.empty(widget.customizableAtts);
                self.widget = widget;
                self.column = column;
                
                paneGetWidget('apply').onClickFunction = (column ? lang.hitch(self, self.applyGridEditorCustom) : lang.hitch(self, self.applyCustom));
                paneGetWidget('import').onClickFunction = lang.hitch(self, self.importAction);
                paneGetWidget('export').onClickFunction = lang.hitch(self, self.exportAction);
                paneGetWidget('import').set('hidden', importExportHideValue);
                paneGetWidget('export').set('hidden', importExportHideValue);
                lang.hitch(self, self.setWidgetTypeAtts)(widget);
                widgetCustomDialog.open({parent: widget, x: evt.clientX, y: evt.clientY});
				paneGetWidget('att').set('value', '');
				paneGetWidget('NumberUnitBox').set('value', undefined);
				widgetCustomDialog.resize();
            });
	
		},
        newAttValue: function(){
            var attValueWidget = this.attValueWidget;
            switch(attValueWidget.widgetType){
				case 'NumberUnitBox': 
		            var newValue  = (attValueWidget.numberField.get('disabled')) ? '' : attValueWidget.numberField.get('value');
		            if (! attValueWidget.unitField.get('disabled')){
		                newValue += attValueWidget.unitField.get('value');
		            }
		            return newValue;
				case 'MultiSelect': 
					return attValueWidget.get('serverValue');
				case 'SimpleDgridNoDnd':
					return JSON.stringify(attValueWidget.collection.fetchSync().filter(function(row){
						utils.forEach(row, function(value, property){
							if (value === undefined || value === null || Number.isNaN(value)){
								delete row[property];
							}
						});
						return row;
					}));
				default:
					return attValueWidget.get('value');
			}
        },
        importAction: function(){
			const self = this, widget = this.widget, widgetTypeFilter = widget.widgetType + (widget.chartType ? '_' + widget.chartType : '');
            Pmg.confirm({title: Pmg.message('Selectwidgetcustomtemplatetoimport'), content: '<input data-dojo-type="tukos/ObjectSelect" name="id" id="id" data-dojo-props="object:\'customwidgets\', dropdownFilters:{widgettype:\'' + widgetTypeFilter + '\'} ">'}).then(
				function(){
					const theResponse = Pmg.dialogConfirm.get('value');
					Pmg.serverDialog({action: 'getItem', object: 'customwidgets', view: 'edit', query: {id: theResponse.id}}, 
						{},Pmg.message('ActionDone')).then(
							function(response){
								const customization = response.data  && response.data.value && response.data.value.customization;
								if (customization){
									utils.forEach(customization, function(newAtt, attRoot){
										widget.set(attRoot, newAtt);
                						lang.setObject((widget.itemCustomization || 'customization') + (attRoot === 'value' ? ('.data.value.' + widget.widgetName) : ('.widgetsDescription.' + widget.widgetName + '.atts.' + attRoot)), newAtt, widget.pane);
									});
								}else{
									Pmg.setFeedbackAlert(Pmg.message('YouNeedtoselecttemplateortemplatehasnocustomization'));
								}
							},
							function(error){
								Pmg.setFeedbackAlert('something went wrong');
							}
						);
				},
				function(){
					//Pmg.setFeedback('Cancelled');
				}
			);
		},
        exportAction: function(){
			const widget = this.widget, widgetTypeFilter = widget.widgetType + (widget.chartType ? '_' + widget.chartType : ''),  customAtts = {};
			utils.forEach(widget.customizableAtts, function(customAtt){
				if (widget.hasOwnProperty(customAtt.att)){
					customAtts[customAtt.att] = widget[customAtt.att];
				}
			});
			if (utils.empty(customAtts)){
				Pmg.setFeedbackAlert(Pmg.message('NoCustomizationtosave'));
			}else{
				const content = '<table>' +
					'<tr><td colspan=2 style="max-width: 350px;background-color:lightgrey"><i>' + Pmg.message('WidgetCustomExportExplanation') + '</i></td></tr>' +
					'<tr><td><label for="name">' + Pmg.message('existingCustomization') + ': </label></td><td><input data-dojo-type="tukos/ObjectSelect" name="id" id="id" data-dojo-props="object:\'customwidgets\', dropdownFilters:{widgettype:\'' + widgetTypeFilter + '\'} "></td></tr>' +
					'<tr><td><label for="name">' + Pmg.message('newName') + ': </label></td><td><input data-dojo-type="dijit/form/TextBox" type="text" name="name" id="name"></td></tr>' +
				'</table>';
                Pmg.confirm({title: Pmg.message('Exportcustomization'), content: content}).then(
					function(){
						const theResponse = Pmg.dialogConfirm.get('value');
						if (theResponse.id || theResponse.name){
							Pmg.serverDialog({action: 'Save', object: 'customwidgets', view: 'edit', query: {id: theResponse.id, timezoneOffset: (new Date()).getTimezoneOffset()}}, 
								{data: theResponse.id 
									? (theResponse.name	? {name: theResponse.name, customization: customAtts}	: {customization: customAtts})
									: {parentid: Pmg.get('userid'), name: theResponse.name, vobject: widget.form.object, widgettype: widget.widgetType + (widget.chartType ? '_' + widget.chartType : ''), customization: customAtts}
							},Pmg.message('ActionDone'));
						}else{
							Pmg.setFeedbackAlert(Pmg.message('YouneedtoselectExistingorenternew'));
						}
					},
					function(){
						//Pmg.setFeedback('Cancelled');
					}
				);
			}
		}
    }
});
