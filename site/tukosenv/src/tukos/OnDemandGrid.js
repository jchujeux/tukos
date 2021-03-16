define (["dojo/_base/declare", "dojo/dom-construct", "dgrid/OnDemandGrid", "dgrid/extensions/DijitRegistry", "tukos/dstore/Request"], 
function(declare, dct, Grid, DijitRegistry, Request){
    return declare([Grid, DijitRegistry], {
    	postCreate: function(){
    		this.inherited(arguments);
    		this.collection = new Request(this.storeArgs);
        	for (var i in this.columns){
                this.columns[i].renderCell = this.renderContent;
            }
    	},
        renderContent: function(object, innerHTML, node){
            var grid = this.grid, row = grid.row(object), rowHeight = ((grid.rowHeights || {})[row.id] ? grid.rowHeights[row.id] : this.minHeightFormatter || '15em');
            return dct.create('div', {innerHTML: innerHTML, style: {maxHeight: rowHeight, overflow: 'auto'}});
        }
    });
});
