define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/promise/all", "dojo/ready", "dojo/when", "tukos/utils", "tukos/TukosTooltipDialog", "tukos/Download",  "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, all, ready, when, utils, TukosTooltipDialog, download, Pmg, JSON, messages){
	return declare(null, {
        editInPopup: function(evt){
			this.openEditDialog(this.clickedRowValues(), {x: evt.clientX, y: evt.clientY});
        },
		openEditDialog: function(item, openArgs){
            var self = this, editDialog = this.editDialog;
            if (editDialog){
                this.openDialog(item, openArgs);
            }else{
                editDialog = this.editDialog = new TukosTooltipDialog(this.dialogAtts());
                editDialog.pane.blurCallback = on.pausable(editDialog, 'blur', editDialog.close);
                ready(function(){
                    lang.hitch(self, self.openDialog)(item, openArgs);
                });
            }
        },
        
        dialogAtts: function(form){
            var self = this, form = this.form, columns = this.columns, widgetsDescription, layout = {}, editDialogAtts = this.editDialogAtts || {}, ignoreColumns = editDialogAtts.ignoreColumns || [], 
            	editDialogDescription = editDialogAtts.description || false, editorWidgets = [], otherWidgets = [], description;
            widgetsDescription = {
                save: {type: 'TukosButton', atts: {label: messages.save, onClick: lang.hitch(this, this.saveEditDialog)}},
            	cancel: {type: 'TukosButton', atts: {label: messages.cancel, onClick: lang.hitch(this, this.cancelEditDialog)}}
            };	
            utils.forEach(columns, function(column, col){
            	if (!utils.in_array(col, ignoreColumns)){
            		var widgetType = column.widgetType || 'TextBox';
            		widgetsDescription[col] = {type: widgetType, atts: lang.clone(column.editorArgs) || {label: column.label, disabled: true}};
            		(widgetType === 'Editor' ? editorWidgets : otherWidgets).push(col);
            	}
            });
        	description = {paneDescription: {form: form, widgetsDescription: widgetsDescription, postElts: otherWidgets.concat(editorWidgets), layout: {
	    			tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
	    			contents:{
	                    row3: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false},  widgets: ['save', 'cancel']},
	                    row1: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true},  widgets: otherWidgets},
	                    row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},  widgets: editorWidgets}
	    			}
    			},
                style: {maxWidth: (dojo.window.getBox().w*0.5) + 'px',maxHeight: (dojo.window.getBox().h*0.5) + 'px', overflow: 'auto'}
        	}};
            
        	return editDialogDescription ? utils.mergeRecursive(description, editDialogDescription) : description;
        },

        openDialog: function(item, openArgs){
            var editDialog = this.editDialog, pane = editDialog.pane;
            pane.itemId = item[this.collection.idProperty];
            pane.watchOnChange = pane.markIfChanged = false;
            when(editDialog.open(openArgs), lang.hitch(this, function(){
                when (pane.setWidgets({value: item}), function(){
                	pane.watchOnChange = pane.markIfChanged = true;
                });
            }));
        },
        
        saveEditDialog: function(){
        	var editDialog = this.editDialog, pane = editDialog.pane, changedValues = pane.changedValues(pane.widgetsName);
        	changedValues[this.collection.idProperty] = pane.itemId;
        	this.updateRow(changedValues);
        	this.editDialog.pane.close();
        },
        cancelEditDialog: function(){
        	this.editDialog.pane.close();
        }
    });
});
