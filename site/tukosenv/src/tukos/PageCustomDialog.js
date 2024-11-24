define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojo/ready", "dojo/when", "dijit/focus", "dijit/registry", "tukos/utils", "tukos/TukosTooltipDialog",  "tukos/PageManager"], 
    function(declare, lang, dst, ready, when, focus, registry, utils, TukosTooltipDialog, Pmg){
    return declare(TukosTooltipDialog, {
        postCreate: function(){
        	lang.mixin(this, this._dialogAtts());
            this.inherited(arguments);
        },
        _dialogAtts: function(){
            const onWatch = lang.hitch(this, this.onWatchLocalAction), onGridWatch = lang.hitch(this, this.onGridWatchLocalAction)
            	  description =  {paneDescription: {
            	  	  widgetsDescription: {
							newPageCustom: {type: 'ObjectEditor', atts: {label: Pmg.message('newCustomContent'), keyToHtml: 'capitalToBlank', onWatchLocalAction: onWatch('newPageCustom', this.newPageCustomAction, true, true)}},
                    		tukosOrUser: {type: 'StoreSelect', atts: {label: Pmg.message('tukosOrUser'), storeArgs: {data: Pmg.idsNamesStore(['', 'allUsers', 'thisUser'], true)}, onWatchLocalAction: onWatch('tukosOrUser', lang.hitch(this, this.tukosOrUserAction), true)}},
                    		pageCustomForAll: {type: 'StoreSelect', atts: {label: Pmg.message('pageCustomForAll'), storeArgs: {data: Pmg.idsNamesStore(['', 'YES', 'NO'])}, onWatchLocalAction: onWatch('pageCustomForAll')}},
                    		contextCustomForAll: {type: 'StoreSelect', atts: {label: Pmg.message('contextCustomForAll'), storeArgs: {data: Pmg.idsNamesStore(['', 'YES', 'NO'])}, onWatchLocalAction: onWatch('contextCustomForAll')}},
                    		defaultTukosUrls: {type: 'SimpleDgrid', atts: {label: Pmg.message('defaultTukosUrls'), storeType: 'MemoryTreeObjects', storeArgs: {idProperty: 'idg'}, initialId: true, style: {width: '500px'}, deleteRowAction: this.deleteRowAction('defaultTukosUrls'),// noDeleteRow: true, 
                    			columns: {
	                            	rowId: {field: 'rowId', label: '', width: 40, className: 'dgrid-header-col', hidden: true},
	                            	app: {field: 'app', label: Pmg.message('tukosAppName'), canEdit: "canEditRow", editOn: 'click', editor: 'StoreSelect', renderCell: 'renderStoreValue', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('app'), storeArgs: {data: Pmg.idsNamesStore(['TukosApp', 'TukosBus', 'TukosSports', 'TukosMSQR', 'TukosWoundTrack'])}, onWatchLocalAction: onGridWatch('defaultTukosUrls')}},
	                            	object: {field: 'object', label: Pmg.message('module'), canEdit: 'canEditRow', editOn: 'click', editor: 'StoreSelect', renderCell: 'renderStoreValue', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('object'), storeArgs: {data: Pmg.idsNamesStore(Pmg.get('allowedModules'))}, onWatchLocalAction: onGridWatch('defaultTukosUrls')}},
	                            	view: {field: 'view', label: Pmg.message('view'), canEdit: 'canEditRow', editOn: 'click', editor: 'StoreSelect', renderCell: 'renderStoreValue', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('view'), storeArgs: {data: Pmg.idsNamesStore(['edit', 'overview'])}, onWatchLocalAction: onGridWatch('view')}},
	                            	query: {field: 'query', label: Pmg.message('defaultUrlQuery'), canEdit: 'canEditRow', editOn: 'click', editor: 'TextBox', renderCell: 'renderContent', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('view'), onWatchLocalAction: onGridWatch('defaultTukosUrls')}},
	                        	}
                        	}},
                    		hideLeftPane: {type: 'StoreSelect', atts: {label: Pmg.message('hideLeftPane'), storeArgs: {data: Pmg.idsNamesStore(['', 'YES', 'NO'])},	onWatchLocalAction: onWatch('hideLeftPane', this.hideLeftPaneAction)}},
                    		leftPaneWidth: {type: 'TextBox', atts: {label: Pmg.message('leftPaneWidth'),	onWatchLocalAction: onWatch('leftPaneWidth', this.leftPaneWidthAction)}},
                    		panesConfig: {type: 'SimpleDgridNoDnd', atts: {label: Pmg.message('panesConfig'), storeType: 'MemoryTreeObjects', storeArgs: {idProperty: 'idg'}, initialId: true, style: {width: '500px'}/*, noDeleteRow: true*/, deleteRowAction: this.deleteRowAction('panesConfig'), 
	                    		columns: {
	                            	rowId: {field: 'rowId', label: '', width: 40, className: 'dgrid-header-col', hidden: true},
	                            	name: {field: 'name', label: Pmg.message('panename'), canEdit: "canEditRow", editOn: 'click', editor: 'StoreSelect', renderCell: 'renderStoreValue', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('name'), storeArgs: {data: this.accordionStoreData()}, onWatchLocalAction: onGridWatch('panesConfig')}},

                               /*'selectonopen' => Widgets::description(Widgets::checkBox(['edit' => ['label' => $tr('selectonopen'), 'onChangeLocalAction' => $this->selectedAction(),
                                    'onWatchLocalAction' => $this->gridWatchLocalAction('panesConfig')]]), false),*/

	                            	selectonopen: {field: 'selectonopen', label: Pmg.message('selectonopen'), canEdit: 'canEditRow'/*, editOn: 'click'*/, editor: 'CheckBox', renderCell: 'renderCheckBox', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('selectonopen'), onChangeLocalAction: utils.selectedAction, onWatchLocalAction: onGridWatch('panesConfig', utils.selectedAction)}},
	                            	present: {field: 'present', label: Pmg.message('presentpane'), canEdit: 'canEditRow', editOn: 'click', editor: 'StoreSelect', renderCell: 'renderStoreValue', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('present'), storeArgs: {data: Pmg.idsNamesStore(['YES', 'NO'])}, onWatchLocalAction: onGridWatch('panesConfig')}},
	                            	associatedtukosid: {field: 'associatedtukosid', label: Pmg.message('associatedtukosid'), canEdit: 'canEditRow', editOn: 'click', editor: 'TextBox', renderCell: 'renderContent', renderHeaderCell: 'renderHeaderContent', 
	                            		editorArgs: {label: Pmg.message('associatedtukosid'), fetchProperties: {sort: ['name']}, ignoreCase: true, object: 'calendars', searchAttr: 'name', searchDelay: 500, onWatchLocalAction: onGridWatch('panesConfig')}},
	                        	}
                        	}},
							defaultClientTimeout: {type: 'TextBox', atts: {label: Pmg.message('defaultClientTimeout'),	onWatchLocalAction: onWatch('defaultClientTimeout', this.watchLocalAction)}},
							defaultServerTimeout: {type: 'TextBox', atts: {label: Pmg.message('defaultServerTimeout'),	onWatchLocalAction: onWatch('defaultServerTimeout', this.watchLocalAction)}},
							fieldsMaxSize: {type: 'TextBox', atts: {label: Pmg.message('fieldsMaxSize'),	onWatchLocalAction: onWatch('fieldsMaxSize', this.watchLocalAction)}},
                    		historyMaxItems: {type: 'TextBox', atts: {label: Pmg.message('historyMaxItems'),	onWatchLocalAction: onWatch('historyMaxItems', this.watchLocalAction)}},
                    		ignoreCustomOnClose: {type: 'StoreSelect', atts: {label: Pmg.message('ignoreCustomOnClose'), storeArgs: {data: Pmg.idsNamesStore(['', 'YES', 'NO'])},	onWatchLocalAction: onWatch('ignoreCustomOnClose', this.watchLocalAction)}},
                    		showTooltips: {type: 'StoreSelect', atts: {label: Pmg.message('showTooltips'), storeArgs: {data: Pmg.idsNamesStore(['', 'YES', 'NO'], true)},	onWatchLocalAction: onWatch('showTooltips', this.watchLocalAction)}},
                    		close: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClickAction: this.closeOnClickAction}},
                    		saveuser: {type: 'TukosButton', atts: {label: Pmg.message('saveForCurrentUser'), onClickAction: this.saveOnClickAction('user')}},
                    		saveall: {type: 'TukosButton', atts: {label: Pmg.message('saveForAllUsers'), onClickAction: this.saveOnClickAction('tukos')}},
				  },
				  layout: {
                    tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
                    contents: {
						row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['newPageCustom']},
						row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, labelWidth: 250}, widgets: [
							'pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'hideLeftPane', 'leftPaneWidth', 'panesConfig', 'defaultClientTimeout', 'defaultServerTimeout', 'fieldsMaxSize', 'historyMaxItems', 'ignoreCustomOnClose', 'showTooltips']},
						row3: {
							tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false},
							contents: {
								col1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, labelWidth: 200}, widgets: ['tukosOrUser']},
								col2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['close', 'saveuser', 'saveall']}
							}
						}
					}
				},
				onOpenAction: this.fillDialogFields('open')
			}};
            return description;
        },
		onWatchLocalAction: function(widgetName, additionalWatchAction, noDefaultAction, serverTrigger){
            var watchArgs = {value: {}}, watchAction = function(sWidget, tWidget, newValue){
        		if (!noDefaultAction){
	        		Pmg.addCustom(widgetName, newValue);
	        		sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));            
				}
	            if (additionalWatchAction){
	            	additionalWatchAction(sWidget, tWidget, newValue);
				}
	            return true;
	        };
            watchArgs.value[widgetName] = {localActionStatus: {triggers: {user: true, server: serverTrigger}, action: lang.hitch(this, watchAction)}};
            return watchArgs;
        },
        onGridWatchLocalAction: function(widgetName, additionalWatchAction, att){
            var watchArgs = {value: {}}, watchAction = function(sWidget, tWidget, newValue){
				var grid = sWidget.parent, form = grid.form; 
				Pmg.addCustom(widgetName, grid.dirty);
				form.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
	            if (additionalWatchAction){
	            	additionalWatchAction(sWidget, tWidget, newValue);
				}
	            return true;
	        };
            watchArgs[att || 'value'][widgetName] = {localActionStatus: lang.hitch(this, watchAction)};
            return watchArgs;
        },
		newPageCustomAction: function(sWidget, tWidget, newValue){
			var pane = sWidget.pane, disabled = utils.empty(newValue) ? true : false;
			['saveuser', 'saveall'].forEach(function(widgetName){
			    pane.getWidget(widgetName).set('disabled', disabled);
			});
			sWidget.set('hidden', disabled); 
			pane.resize();
		},
		tukosOrUserAction: function(sWidget, tWidget, newValue){
			this.fillDialogFields('tukosOrUser', sWidget, tWidget, newValue)();
		},
		hideLeftPaneAction: function(sWidget, tWidget, newValue){
			dst.set('leftPanel', 'display', (newValue === 'NO' ? 'block' : 'none'));
			if (newValue === 'NO'){
				Pmg.lazyCreateAccordion();
			}
			Pmg.addCustom('hideLeftPane', newValue);
			sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
			setTimeout(function(){registry.byId('appLayout').resize();}, 100);
		},
		leftPaneWidthAction: function(sWidget, tWidget, newValue){
			dst.set('leftPanel', 'width', newValue);
			Pmg.addCustom('leftPaneWidth', newValue);
			sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
			setTimeout(function(){registry.byId('appLayout').resize();}, 100);
		},
		deleteRowAction: function(widgetName){
			return function(row){
				Pmg.addCustom(widgetName, utils.newObj([[row.rowId, '~delete']]));
				this.form.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
			}
		},
		closeOnClickAction: function(){
			this.pane.close();
		},
		saveOnClickAction: function(tukosOrUser){
			return function(){
				var self = this, pane = this.pane, setValueOf = lang.hitch(pane, pane.setValueOf), data = Pmg.getCustom({tukosOrUserOrChanges: 'changes'});
				Pmg.setFeedback(Pmg.message('saving') + '...');
				Pmg.serverDialog({object: 'users', view: 'NoView', action: 'PageCustomSave', query: {tukosOrUser: tukosOrUser}}, {data: data}).then(function(response){
				    Pmg.setCustom({tukosOrUserOrChanges: tukosOrUser}, response.pagecustom);    
				    Pmg.setCustom({tukosOrUserOrChanges: 'changes'}, {});    
				    setValueOf('newPageCustom', {});
				    Pmg.setCustom(null, utils.mergeRecursive(lang.clone(Pmg.getCustom({tukosOrUserOrChanges: 'tukos'})), lang.clone(Pmg.getCustom({tukosOrUserOrChanges: 'user'}))));
				    pane.close();
				});
			}
		},
    	fillDialogFields: function(mode, sWidget, tWidget, newValue){
			return function(){
				var form = (mode === 'open' ? this : sWidget.form), setValueOf = lang.hitch(form, form.setValueOf), 
		    		widgets = ['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'hideLeftPane', 'leftPaneWidth', 'panesConfig', 'fieldsMaxSize', 'defaultClientTimeout', 'defaultServerTimeout', 'historyMaxItems', 'ignoreCustomOnClose', 'showTooltips'];
				form.watchOnChange = false;
				form.markIfChanged = false;
				form.emptyWidgets(widgets);
				var disabled = (mode === 'open' ? false : (newValue ? true : false));
				widgets.forEach(function(widgetName){
				    form.getWidget(widgetName).set('disabled', disabled);
				});
				utils.forEach(Pmg.getCustom(mode === 'open' || !newValue ? undefined : {tukosOrUserOrChanges: newValue === 'allUsers' ? 'tukos' : 'user'}), function(value, widgetName){
				    setValueOf(widgetName, value);
				});
				if (mode === 'open'){
				    if (!Pmg.isAtLeastAdmin()){
				        ['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'tukosOrUser', 'saveall'].forEach(function(widgetName){
				            form.getWidget(widgetName).set('hidden', true);
				        });
				        form.resize();
				    }
				    form.watchOnChange = true;
				    setValueOf('newPageCustom', Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
				    setValueOf('tukosOrUser', '');
				}
				form.watchOnChange = true;
				form.markIfChanged = true;
			}
	    },
	    accordionStoreData: function(){
			var result = [];
			Pmg.cache.accordionDescription.forEach(function(description){
				result.push({id: description.id, name: description.title});
			});
			return result;
		}
    });
});
