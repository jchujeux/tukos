define(["dojo/_base/declare", "tukos/utils"], function (declare, utils) {
    return declare(null, {
        constructor: function () {
            this.applicationCollectionFilter = new this.Filter();
        },
        userCollectionFilters: function(){
        	var userCollectionFilter = new this.Filter(), map = {'=': 'eq', '<>': 'ne', '>': 'gt', '<': 'lt', '>=': 'gte', '<=': 'lte', 'RLIKE': 'rlike', 'NOT RLIKE': 'notrlike', 'BETWEEN': 'between'}, columns = this.columns;
			if (this.userFilters){
				utils.forEach(this.userFilters(), function(filter, col){
	        		var opr = filter[0], value = filter[1], column = columns[col];
	        		if (opr && value){
	        			if (column.widgetType === "StoreSelect"){
	        				value = utils[(opr === 'RLIKE' || opr === 'NOT RLIKE') ? 'includesReplace' : 'findReplace'](column.editorArgs.storeArgs.data, 'name', value, 'id', {}, true, true);
	        			}
	        			userCollectionFilter = userCollectionFilter[map[opr]](col, value);
	        		}
	        	});
			}        	
        	return userCollectionFilter;
        },
        getCollectionFilter: function(filters){
            var combinedFilter = (new this.Filter()).and(this.applicationCollectionFilter, this.userCollectionFilters());
        	if (filters){
            	filters.forEach(function(filter){
            		combinedFilter = combinedFilter[filter[0]](filter[1], filter[2]);
            	});
            }
            return combinedFilter;
        }
    });
});
