/*
 * mixin patterned after native dstore/tree, to support the tukos parent - child model, in particular:
 * - the parent property is 'parentid' (versus 'parent' in native dstore)
 */
define(["dojo/_base/declare", "tukos/utils"], function (declare, utils) {
    return declare(null, {
        constructor: function () {
            this.root = this;
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
        },

        mayHaveChildren: function (object) {
            return 'hasChildren' in object ? object.hasChildren : (object.id ? this.getChildren(object).fetchSync().length : false);//this.root.filter(this.getCollectionFilter().eq('parentid', object.id)).length > 0;
        },

        getRootCollection: function (filters) {
            var ids = [], filters = this.getCollectionFilter(filters);
            this.root.forEach(function(object){
                if (object.id){
                	ids.push(object.id);
                }
            });
            return this.root.filter(ids.length === 0 ? filters : filters.ni('parentid', ids));
            //return this.root.filter(this.getCollectionFilter(filters).ni('parentid', ids));
        },
        
        getFullCollection: function(filters){
            return this.root.filter(this.getCollectionFilter(filters));
        },
        
        getChildren: function (object) {
            return this.root.filter(this.getCollectionFilter().eq('parentid', object.id));
        }
    });
});
