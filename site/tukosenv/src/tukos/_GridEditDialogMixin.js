define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/promise/all", "dojo/ready", "dojo/when", "tukos/utils", "tukos/TukosTooltipDialog",  "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, all, ready, when, utils, TukosTooltipDialog, Pmg, JSON, messages){
	return declare(null, {
        editInPopup: function(evt){
			this.openEditDialog(this.clickedRowValues(), {x: 0, y: 0});
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
            	editDialogDescription = editDialogAtts || false, editorWidgets = [], otherWidgets = [], description;
            widgetsDescription = {
                apply: {type: 'TukosButton', atts: {label: Pmg.message('apply'), onClick: lang.hitch(this, this.applyEditDialog)}},
            	close: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClick: lang.hitch(this, this.closeEditDialog)}}
            };	
            utils.forEach(columns, function(column, col){
            	if (!utils.in_array(col, ignoreColumns)){
            		var widgetType = column.widgetType || 'TextBox';
            		widgetsDescription[col] = {type: widgetType, atts: column.disabled ? {label: column.label, disabled: true} : lang.clone(column.editorArgs)};
            		(['Editor', 'SimpleEditor', 'MobileEditor'].includes(widgetType) ? editorWidgets : otherWidgets).push(col);
            	}
            });
        	description = {paneDescription: {form: form, widgetsDescription: widgetsDescription, postElts: otherWidgets.concat(editorWidgets), layout: {
	    			tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
	    			contents: lang.mixin({row0: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false},  widgets: ['apply', 'close']}}, this.editActionLayout || 
	    				{row1: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true},  widgets: otherWidgets}, row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},  widgets: editorWidgets}})
    			},
    			widgetsHider: !Pmg.isRestrictedUser(),
    			widgetsHiderArgs: {dialogPath: this.widgetName + '.atts.editDialogAtts.paneDescription.widgetsDescription.'},
                style: {minWidth: (dojo.window.getBox().w*0.8) + 'px',overflow: 'auto'}
        	}};
            
        	return editDialogDescription ? utils.mergeRecursive(description, editDialogDescription) : description;
        },

        openDialog: function(item, openArgs){
            var editDialog = this.editDialog, pane = editDialog.pane;
            pane.itemId = item[this.collection.idProperty];
			pane.emptyWidgets(pane.postElts);
			pane.resetChangedWidgets();
            pane.watchOnChange = true;
			pane.watchContext = 'server';
			pane.markIfChanged = false;
            when(editDialog.open(openArgs), lang.hitch(this, function(){
                when (pane.setWidgets({value: item}), function(){
                	pane.watchOnChange = pane.markIfChanged = true;
					pane.watchContext = 'user';
                	pane.resize();
                });
            }));
        },
        
        applyEditDialog: function(){
        	var editDialog = this.editDialog, pane = editDialog.pane, changedValues = pane.changedValues(pane.widgetsName);
        	changedValues[this.collection.idProperty] = pane.itemId;
        	this.updateRow(changedValues);
        	this.editDialog.pane.close();
        },
        closeEditDialog: function(){
        	this.editDialog.pane.close();
        }
    });
});
