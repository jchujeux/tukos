define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/ready", "dijit/form/Button", "dijit/registry", "tukos/PageManager", "tukos/utils", "tukos/DialogConfirm", "tukos/Download", "dojo/json", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, ready, Button, registry, Pmg, utils, DialogConfirm, download, JSON, messages){
    return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            on(this, "click", function(evt){
                var form = self.form, grid = form.getWidget(self.grid);
                evt.stopPropagation();
                evt.preventDefault();

                var action = self.serverAction, isItemsChange = utils.in_array(action, ['modify', 'delete']), needsRevert = this.needsRevert || isItemsChange || action === 'duplicate', queryParams = self.queryParams,
                			 toProcess = action === 'reset' ? {} : (isItemsChange ? this.editableIdsToProcess(grid) : this.idsToProcess(grid)) ;
                Pmg.setFeedback(self.actionStartMessage);
                
                if (action === 'search'){
                	lang.hitch(self, self.searchAction());
                }else if(action === 'reset'){
                    if (self.dialogDescription){
                        if (self.tooltipDialog){
                            self.tooltipDialog.open({around: self.domNode});
                        }else{
                            require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                                self.dialogDescription.paneDescription.attachedWidget = self;
                                self.dialogDescription.paneDescription.form = self.form;
                                self.dialogDescription.paneDescription.tabContextId = lang.hitch(self.form, self.form.tabContextId);
                                self.tooltipDialog = new TukosTooltipDialog(self.dialogDescription);
                                on(self.tooltipDialog, 'blur', self.tooltipDialog.close);
                                self.tooltipDialog.open({around: self.domNode});
                            });
                        }
                    }else{
	                	lang.hitch(self, self.resetAction());//JCH - action can probably be removed, 
                    }
                }else if (toProcess.ids.length == 0){
                    var dialog = new DialogConfirm({title: messages.noRowSelectedNoAction + toProcess.warning, hasSkipCheckBox: false, hasCancelButton: false});
                    dialog.show().then(
                        function(){Pmg.setFeedback(messages.noActionDone);},
                        function(){Pmg.setFeedback(messages.noActionDone);}
                    );                                 
                }else{
                    if (action === 'modify' && grid.modify.length == 0){
                        var dialog = new DialogConfirm({title: messages.noModifySelectedNoAction, hasSkipCheckBox: false, hasCancelButton: false});
                        dialog.show().then(
                            function(){Pmg.setFeedback(messages.noActionDone);},
                            function(){Pmg.setFeedback(messages.noActionDone);}
                        );
                    }else{
                        var dialog = new DialogConfirm({title: messages['overview' + action] + toProcess.ids.length + messages.entries + toProcess.warning, content: messages.sureWantToContinue, hasSkipCheckBox: false});
                        dialog.show().then(
                            function(){
                            	if(action === "process" && (queryParams || {}).process === "exportItems"){
                                    var visibleCols = [];
                                	utils.forEach(grid.columns, function(column){
                                    	if (!column.hidden){
                                    		visibleCols.push(column.field);
                                    	}
                                    });
                                	download.download({object: form.object, view: form.viewMode, action: action, query: {params: queryParams}}, {data: {ids: JSON.stringify(toProcess.ids), visibleCols: JSON.stringify(visibleCols)}});
                                }else{
                                	self.form.serverDialog({action: action, query: queryParams ? {params: queryParams} : {}}, {ids: toProcess.ids, values: grid.modify.values}, [], messages.actionDone).then(function(response){
	                                    if (needsRevert){
	                                    	grid.revert();
	                                    }
                                	});
                                }
                            },
                            function(){
                                Pmg.setFeedback(messages.actionCancelled);
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
            
        	Pmg.setFeedback(messages.actionDoing);
            grid.set('collection', grid.store.filter(filter));
            Pmg.setFeedback(messages.actionDone);
        },

        resetAction: function(options){
            var form = this.form, parent = form.parent, title = parent.get('title'), url = require.toUrl('tukos/resources/images/loadingAnimation.gif'), grid = form.getWidget(this.grid), queryParams = this.queryParams;
        	if (queryParams){
                form.serverDialog({action: 'reset', query: {params: queryParams}}, options || {}, [], messages.actionDone).then(function(response){
            		response.feedback.pop();
                    Pmg.alert({title: messages[queryParams.process], content: response.feedback.join('<br>')});
                	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                    Pmg.setFeedback(messages.actionDone);
                });
        	}else{
            	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                Pmg.setFeedback(messages.actionDone);        		
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
			var deselect = 0;
			var idsToProcess = new Array;
			for (var id in grid.selection){
				if (grid.selection[id]){
					var row = grid.row(id); 
					if (row.data['canEdit']){
						idsToProcess.push(id);
					}else{
						grid.deselect(id);
						deselect += 1;
					}
				}
			}
			return {ids: idsToProcess, warning: (deselect > 0  ? '<p>' + deselect + messages.werereadonlyexcluded : '')};
		}
    });
});
