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

                var action = self.serverAction, isItemsChange = utils.in_array(action, ['Modify', 'Delete', 'Restore']), queryParams = self.queryParams;
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
                                self.tooltipDialog.open({around: self.domNode});
                            });
                        }
                    }else{
	                	lang.hitch(self, self.resetAction());
                    }
                }else if (toProcess.ids.length == 0){
                    Pmg.alert({title: Pmg.message('noRowToProcessNoAction') + toProcess.warning});
                }else{
                	if (action === 'Modify' && utils.empty(grid.modify.values)){
                    	Pmg.alert({title: Pmg.message('noModifySelectedNoAction'), content: ''});
                	}else{
                        require(["tukos/TukosTooltipDialog", "dstore/Memory"], function(TooltipDialog, Memory){
                        	var tooltipDialog = self.tooltipDialog || (self.tooltipDialog = new TooltipDialog({
                    			paneDescription: {
                    				widgetsDescription: {
                    					message: {type: 'HtmlContent', atts: {label: '', style: {marginLeft: '3em'}}},
                        					cols: {type: 'BasicGrid', atts: {
                        					label: Pmg.message('columns to modify'), allowSelectAll: true, collection: new Memory({data: []}),
                        					columns: {selector: {selector: 'checkbox', width: 50}, col: {label: Pmg.message('column'), width: 200}, value: {label: Pmg.message('newValue')}}}},
                    					allpages: {type: 'CheckBox', atts: {label: Pmg.message('allpages'), onClick: function(evt){
                    							var pane = tooltipDialog.pane, getWidget = lang.hitch(pane, pane.getWidget), messageWidget = getWidget('message');
                    							if (this.checked){
                    								this.previousMessage = messageWidget.get('value');
                    								messageWidget.set('value', string.substitute(Pmg.message('allpages ${actioned} ${number}' + (action === 'Process' ? '' : ' permanently')),
														{actioned: Pmg.message({Modify: 'modified', Delete: 'eliminated', Restore: 'restored', Process: 'exported'}[action]), number: grid.form.valueOf('filteredrecords')}));
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
					                    	if(action === "Process" && (queryParams || {}).process === "exportItems"){
					                            var visibleCols = [];
					                        	utils.forEach(grid.columns, function(column){
					                            	if (!column.hidden){
					                            		visibleCols.push(column.field);
					                            	}
					                            });
												urlArgs.object = form.object;
												urlArgs.view = form.viewMode;
                								urlArgs.query = utils.mergeRecursive(urlArgs.query, {contextpathid: grid.form.tabContextId(), timezoneOffset: (new Date()).getTimezoneOffset()});
												download.download(urlArgs, {data: {ids: JSON.stringify(toProcess.all || toProcess.ids), visibleCols: JSON.stringify(visibleCols), modifyValues: JSON.stringify(grid.modify.values)}});
											}else{
									        	Pmg.setFeedback(Pmg.message('actionDoing'));
												var label = this.get('label'), _self = this;	                    						
												this.set('label', Pmg.loading(label));
												self.form.serverDialog(urlArgs, {ids: toProcess.all || toProcess.ids, values: grid.modify.values}, [], Pmg.message('actionDone')).then(function(response){
	        	                                    tooltipDialog.close();
	                    							grid.revert();
	                                        	});	                        						
									            grid.on('dgrid-refresh-complete', function(){
													Pmg.setFeedback(Pmg.message('actionDone'));
													_self.set('label', label);
												});
											}
                    					}}},
                    					cancel: {type: 'TukosButton', atts: {label: Pmg.message('Cancel'), onClick: function(evt){tooltipDialog.close();}}}
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
                        			messageWidget.set('value', string.substitute(Pmg.message('will be ${actioned} ${number} items: ${ids}' + (action === 'Process' || action === 'Restore' ? '' : ' permanently')), 
										{actioned: Pmg.message({Duplicate: 'duplicated', Modify: 'modified', Delete: 'eliminated', Restore: 'restored', Process: 'exported'}[action]), number: toProcess.ids.length, 
										ids: toProcess.ids.join(', ') + '<p>' + toProcess.warning}));
									if (action === 'Modify'){
										cols.set('hidden', false);	                        				
										utils.forEach(grid.modify.displayedValues, function(value, col){
                        					data.push({id: i++, selector: true, col: grid.columns[col].label, value: value});
                        				});
                        				collection.setData(data);
                        				cols.refresh();
                        				cols.selectAll();
									}else{
										cols.set('hidden', true);
									}
                    				tooltipDialog.resize();
                    			},
                    			onBlur: function(){
                    				tooltipDialog.close();
                    			}
                        	}));
                            tooltipDialog.open({around: self.domNode});
                        });
                	}
                }
            });
        },

        searchAction: function(data){
            var form = this.form, grid = form.getWidget(this.grid), filter = {pattern: form.valueOf('pattern')}, contextpathid = form.valueOf('contextid'), id = form.valueOf('id'), searchType = form.valueOf('itemstypestosearch');
            if (id){
            	filter.id = id;
            }
            if (contextpathid){
            	filter.contextpathid = contextpathid
            }
            if (searchType === 'eliminateditems'){
				grid.store.eliminatedItems = true;
			}else{
				delete grid.store.eliminatedItems;
			}
        	Pmg.setFeedback(Pmg.message('actionDoing'));
			var label = this.get('label'), self = this;	                    						
			this.set('label', Pmg.loading(label));
            grid.set('collection', grid.store.filter(filter));
            grid.on('dgrid-refresh-complete', function(){
				Pmg.setFeedback(Pmg.message('actionDone'));
				self.set('label', label);
			});
        },

        resetAction: function(options){
            var form = this.form, parent = form.parent, title = parent.get('title'), url = require.toUrl('tukos/resources/images/loadingAnimation.gif'), grid = form.getWidget(this.grid), queryParams = this.queryParams;
			var label = this.get('label'), self = this;	                    						
			this.set('label', Pmg.loading(label));
        	if (queryParams){
                form.serverDialog({action: 'Reset', query: {params: queryParams}}, options || {}, [], Pmg.message('actionDone')).then(function(response){
            		response.feedback.pop();
                    Pmg.alert({title: Pmg.message(queryParams.process), content: response.feedback.join('<br>')});
                	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                });
        	}else{
            	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
        	}
             grid.on('dgrid-refresh-complete', function(){
				Pmg.setFeedback(Pmg.message('actionDone'));
				self.set('label', label);
			});
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
