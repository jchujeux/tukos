define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dgrid/extensions/DnD", "tukos/utils"], 
function(declare, lang, when, Dnd, utils){
	return declare(Dnd, {
        postCreate: function(){
        	this.inherited(arguments);
            this.dndSource.getObject = this.getObject;
            this.dndSource.onDropInternal = this.onDropInternal;
            this.dndSource.onDropExternal = this.onDropExternal;
        },
        onDropInternal: function (nodes, copy, targetItem) {//override dgrid/extensions/dnd
            var grid = this.grid,
                store = grid.collection,
                targetSource = this,
                anchor = targetSource._targetAnchor,
                targetRow,
                nodeRow;
    
            if (anchor) { // (falsy if drop occurred in empty space after rows)
                targetRow = this.before ? anchor.previousSibling : anchor.nextSibling;
            }
    
            nodeRow = grid.row(nodes[0]);
            if (!copy && (targetRow === nodes[0] ||
                    (!targetItem && nodeRow && grid.down(nodeRow).element === nodes[0]))) {
                return;//drop is not moving anything
            }
    
            nodes.forEach(function (node) {
                when(targetSource.getObject(node), function (object) {
                    var id = store.getIdentity(object);
                    if (copy){
                        grid.createNewRow(lang.clone(object), targetItem, (targetItem ? 'before' : 'append'));
                    }else{
                        grid.moveRow(object, targetItem, (targetItem ? 'before' : 'append'));
                    }
                    // Self-drops won't cause the dgrid-select handler to re-fire,
                    // so update the cached node manually
                    if (targetSource._selectedNodes[id]) {
                        targetSource._selectedNodes[id] = grid.row(id).element;
                    }
                });
            });
            grid.refresh({keepScrollPosition: true});
        },
        onDropExternal: function (sourceSource, nodes, copy, targetItem) {
            var tGrid = this.grid, sGrid = sourceSource.grid, noRefresh = this.noRefreshOnUpdateDirty, idp = tGrid.collection.idProperty;
            this.noRefreshOnUpdateDirty = true;
        	if (tGrid.onDropCondition){
            	if (!tGrid.onDropConditionFunction){
            		tGrid.onDropConditionFunction = eutils.eval(tGrid.onDropCondition);
            	}
            	if (!tGrid.onDropConditionFunction(sGrid, tGrid)){
            		return;
            	}
            }
        	var store = tGrid.collection, mapping = tGrid.onDropMap && tGrid.onDropMap[sGrid.widgetName];
            if (mapping){
                var dropMode = mapping.mode, fieldsMapping = mapping.fields;
            }
            if (mapping && dropMode ===  'update'){
                nodes.forEach(function(node){
                    when (sourceSource.getObject(node), function(object){
                        for (field in fieldsMapping){
                            var sourceField = fieldsMapping[field];
                            if (object[sourceField]){
                                targetItem[field] = object[sourceField];
                                tGrid.updateDirty(targetItem[idp], field, targetItem[field]);
                            }
                        }
                        tGrid.collection.putSync(targetItem, {overwrite: true});
                    });
                });
            }else{    
                // TODO: bail out if sourceSource.getObject isn't defined?
                nodes.forEach(function (node) {
                    when(sourceSource.getObject(node), function (object) {
                        if (!copy) {
                            if (sGrid) {                            
                                sGrid.deleteRowItem(object);
                            }
                            else {
                                sourceSource.deleteSelectedNodes();
                            }
                        }
                        if (mapping){
                             var newItem = {};
                             for (field in fieldsMapping){
                                var sourceField = fieldsMapping[field];
                                if (object[sourceField]){
                                    newItem[field] = object[sourceField];
                                }
                            }
                        }else{
                            var newItem = lang.clone(object);
                        }
                        var init={};
                        tGrid.prepareInitRow(init);
                        tGrid.createNewRow(lang.mixin(init, utils.filter(newItem)), targetItem, (targetItem ? 'before' : 'append'));
                    });
                });
            }
            if (!copy){
                sourceSource.selectNone(); // deselect all
            }
            if (sGrid){
                sGrid.setSummary();
            } 
            tGrid.setSummary();
            this.noRefreshOnUpdateDirty = noRefresh;
            if (!noRefresh){
                tGrid.refresh({keepScrollPosition: true});           	
            }
            setTimeout(
                function(){
                    tGrid.layoutHandle.resize();
                    tGrid.bodyNode.scrollTop = tGrid.bodyNode.scrollHeight;
                    if (sGrid){
                        sGrid.bodyNode.scrollTop = sGrid.bodyNode.scrollHeight;
                    }
                },
                0
            );
        }
    });
}); 

