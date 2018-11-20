define (["dojo/_base/declare", "dojo/_base/lang", "dgrid/OnDemandGrid", "dgrid/selector", "dgrid/extensions/DijitRegistry", "dgrid/extensions/ColumnHider", "dgrid/extensions/ColumnResizer"], 
function(declare, lang, Grid, Selector, DijitRegistry, Hider, Resizer){
    return declare([Grid, DijitRegistry, Hider, Resizer, Selector], {
        postCreate: function(){
            this.inherited(arguments);
            if (this.maxHeight){
                this.set('maxHeight', this.maxHeight);          	
            }
            if (this.maxWidth){
            	this.set('maxWidth', this.maxWidth);
            }
            if (this.minWidth){
            	this.set('minWidth', this.minWidth);
            }
            if (this.width){
            	this.set('width', this.width);
            }
            this.on("dgrid-columnstatechange", function(evt){
                var grid = evt.grid;
                if (grid.customizationPath){
                    lang.setObject(grid.customizationPath + 'columns.' + evt.column.field + '.hidden', evt.hidden, grid.form);
                }
            });
            this.on("dgrid-columnresize", function(evt){
                var grid = evt.grid;
                if (evt.width != 'auto' && grid.customizationPath){
                	lang.setObject(grid.customizationPath + 'columns.' + grid.columns[evt.columnId].field + '.width', evt.width, grid.form);
                }
            });
        },
    	_setMaxHeight: function(value){
            this.bodyNode.style.maxHeight = value;
        }
    });
});
