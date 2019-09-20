define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/string", "dijit/form/Button", "dijit/registry", "tukos/PageManager", "tukos/utils", "tukos/Download", "dojo/json"], 
function(declare, lang, ready, string, Button, registry, Pmg, utils, download, JSON){
    var toProcess;
	return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.on("click", function(evt){
                var form = self.form, grid = form.getWidget(self.grid);
                evt.stopPropagation();
                evt.preventDefault();

                var action = self.serverAction, isItemsChange = utils.in_array(action, ['Modify', 'Delete']), needsRevert = this.needsRevert || isItemsChange || action === 'Duplicate', queryParams = self.queryParams;
                toProcess = action === 'Reset' ? {} : (isItemsChange ? this.editableIdsToProcess(grid) : this.idsToProcess(grid)) ;
                Pmg.setFeedback(self.actionStartMessage);
                
                if (action === 'Search'){
                	lang.hitch(self, self.searchAction());
                }else if(action === 'Reset'){
                    if (self.dialogDescription){
                        if (self.tooltipDialog){
                            self.tooltipDialog.open({around: self.domNode});
                        }else{
                            require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                                self.dialogDescription.paneDescription.attachedWidget = self;
                                self.dialogDescription.paneDescription.form = self.form;
                                self.dialogDescription.paneDescription.tabContextId = lang.hitch(self.form, self.form.tabContextId);
                                self.tooltipDialog = new TukosTooltipDialog(self.dialogDescription);
                                self.tooltipDialog.on('blur', self.tooltipDialog.close);
                                self.tooltipDialog.open({around: self.domNode});
                            });
                        }
                    }else{
	                	lang.hitch(self, self.resetAction());
                    }
                }else if (toProcess.ids.length == 0){
                    Pmg.alert({title: Pmg.message('noRowSelectedNoAction') + toProcess.warning});
                }else{
                    if (action === 'Modify'){
                    	if (utils.empty(grid.modify.values)){
	                    	Pmg.alert({title: Pmg.message('noModifySelectedNoAction'), content: ''});
                    	}else{
                            require(["tukos/TukosTooltipDialog", "dstore/Memory"], function(TooltipDialog, Memory){
	                        	var tooltipDialog = self.tooltipDialog || (self.tooltipDialog = new TooltipDialog({
                        			paneDescription: {
                        				widgetsDescription: {
                        					message: {type: 'HtmlContent', atts: {label: '', style: {marginLeft: '3em'}}},
                        					//cols: {type: 'ReadonlyGrid', atts: {
                            				cols: {type: 'BasicGrid', atts: {
                            					label: Pmg.message('columns to modify'), allowSelectAll: true, collection: new Memory({data: []}),
                            					columns: {selector: {selector: 'checkbox', width: 50}, col: {label: Pmg.message('column'), width: 200}, value: {label: Pmg.message('newValue')}}}},
                        					allpages: {type: 'CheckBox', atts: {label: Pmg.message('allpages'), onClick: function(evt){
                        							var pane = tooltipDialog.pane, getWidget = lang.hitch(pane, pane.getWidget), messageWidget = getWidget('message');
                        							if (this.checked){
                        								this.previousMessage = messageWidget.get('value');
                        								messageWidget.set('value', string.substitute(Pmg.message('allpages ${number}'), {number: grid.form.valueOf('filteredrecords')}));
                        								toProcess.all = true;
                        							}else{
                        								messageWidget.set('value', this.previousMessage);
                        								toProcess.all = false;
                        							}
                        						}}
                        					},
                        					apply: {type: 'TukosButton', atts: {label: Pmg.message('apply'), onClick: function(evt){
                                            	var urlArgs = {action: action, query: queryParams ? {params: queryParams} : {}};
                                            	if (toProcess.all){
                                            		urlArgs.query.storeatts = {where: grid.userFilters()};
                                            	}
                        						self.form.serverDialog(urlArgs, {ids: toProcess.all || toProcess.ids, values: grid.modify.values}, [], Pmg.message('actionDone')).then(function(response){
            	                                    tooltipDialog.close();
                        							grid.revert();
                                            	});	                        						
                        					}}},
                        					cancel: {type: 'TukosButton', atts: {label: Pmg.message('cancel'), onClick: function(evt){tooltipDialog.close();}}}
                        				},
                        				layout: {
                        					tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
                        					contents:{
                        						row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['cols']},
                        						row2: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false}, contents:{
                        							col1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true}, widgets: ['allpages']},
                        							col2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false, spacing: 5}, widgets: ['message', 'apply', 'cancel']}
                        						}}
                        					}
                        				},
                        				style: {width: '50em', minWidth: "30em", maxWidth: "100em"}
                        			},
                        			onOpen: function(){
                        				var data = [], pane = tooltipDialog.pane, getWidget = lang.hitch(pane, pane.getWidget), cols = getWidget('cols'), messageWidget = getWidget('message'), allPages = getWidget('allpages'),
                        					collection = cols.collection, i = 0, message;
                        				allPages.set('checked', false);
                        				toProcess.all = false;
                        				allPages.set('hidden', !grid.allSelected);
                        				utils.forEach(grid.modify.displayedValues, function(value, col){
                        					data.push({id: i++, selector: true, col: grid.columns[col].label, value: value});
                        				});
                        				messageWidget.set('value', string.substitute(Pmg.message('will modify ${number} items: ${ids}'), {number: toProcess.ids.length, ids: toProcess.ids.join(', ') + '<p>' + toProcess.warning}));
                        				collection.setData(data);
                        				cols.refresh();
                        				cols.selectAll();
                        				tooltipDialog.resize();
                        			},
                        			onBlur: function(){
                        				tooltipDialog.close();
                        			}
	                        	}));
	                            tooltipDialog.open({around: self.domNode});
	                        });
                            	                    	
                    		
                    	}
                    }else{
                        Pmg.confirm({title: Pmg.message('overview' + action) + ' ' + toProcess.ids.length + ' ' + Pmg.message('selectedEntries') + toProcess.warning, content: Pmg.message('sureWantToContinue')}).then(
                            function(){
                            	if(action === "Process" && (queryParams || {}).process === "exportItems"){
                                    var visibleCols = [];
                                	utils.forEach(grid.columns, function(column){
                                    	if (!column.hidden){
                                    		visibleCols.push(column.field);
                                    	}
                                    });
                                	download.download({object: form.object, view: form.viewMode, action: action, query: {params: queryParams}}, 
                                    		{data: {ids: JSON.stringify(toProcess.ids), visibleCols: JSON.stringify(visibleCols), modifyValues: JSON.stringify(grid.modify.values)}}
                                	);
                                }else{// is modify
                                	self.form.serverDialog({action: action, query: queryParams ? {params: queryParams} : {}}, {ids: toProcess.ids, values: grid.modify}, [], Pmg.message('actionDone')).then(function(response){
	                                    if (needsRevert){
	                                    	grid.revert();
	                                    }
                                	});
                                }
                            },
                            function(){
                                Pmg.setFeedback(Pmg.message('actionCancelled'));
                            }
                        );
                    }
                }
            });
        },

        searchAction: function(data){
            var form = this.form, grid = form.getWidget(this.grid), filter = {pattern: form.valueOf('pattern')}, contextpathid = form.valueOf('contextid'), id = form.valueOf('id');
            if (id){
            	filter.id = id;
            }
            if (contextpathid){
            	filter.contextpathid = contextpathid
            }
            
        	Pmg.setFeedback(Pmg.message('actionDoing'));
            grid.set('collection', grid.store.filter(filter));
            Pmg.setFeedback(Pmg.message('actionDone'));
        },

        resetAction: function(options){
            var form = this.form, parent = form.parent, title = parent.get('title'), url = require.toUrl('tukos/resources/images/loadingAnimation.gif'), grid = form.getWidget(this.grid), queryParams = this.queryParams;
        	if (queryParams){
                form.serverDialog({action: 'Reset', query: {params: queryParams}}, options || {}, [], Pmg.message('actionDone')).then(function(response){
            		response.feedback.pop();
                    Pmg.alert({title: Pmg.message(queryParams.process), content: response.feedback.join('<br>')});
                	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                    Pmg.setFeedback(Pmg.message('actionDone'));
                });
        	}else{
            	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                Pmg.setFeedback(Pmg.message('actionDone'));        		
        	}
        },
        
        idsToProcess: function(grid){
			var idsToProcess = new Array;
			for (var id in grid.selection){
				if (grid.selection[id]){
					idsToProcess.push(id);
				}
			}
			return {ids: idsToProcess, warning: ''};
		},	

		editableIdsToProcess: function(grid){
			var deselect = 0, selectionLength = 0;
			var idsToProcess = new Array;
			for (var id in grid.selection){
				if (grid.selection[id]){
					var row = grid.row(id); 
					if (row.data['canEdit']){
						idsToProcess.push(id);
						selectionLength += 1;
					}else{
						grid.deselect(id);
						deselect += 1;
					}
				}
			}
			return {ids: idsToProcess, selectionLength: selectionLength, warning: (deselect > 0  ? '<p>' + deselect + Pmg.message('werereadonlyexcluded') : '')};
		}
    });
});
