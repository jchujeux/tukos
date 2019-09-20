/*
 * mixin patterned after native dstore/tree, to support the tukos parent - child model, in particular:
 * - the parent property is 'parentid' (versus 'parent' in native dstore)
 */
define([
	'dojo/_base/declare'
	/*=====, 'dstore/Store'=====*/
], function (declare /*=====, Store=====*/) {
    return declare(null, {
        constructor: function () {
            this.root = this;
            this.userCollectionFilter = new this.Filter(); this.applicationCollectionFilter = new this.Filter();
        },
        getCollectionFilter: function(filters){
            var combinedFilter = (new this.Filter()).and(this.applicationCollectionFilter, this.userCollectionFilter);
        	if (filters){
            	filters.forEach(function(filter){
            		combinedFilter = combinedFilter[filter[0]](filter[1], filter[2]);
            	});
            }
            return combinedFilter;
        },

        mayHaveChildren: function (object) {
            return 'hasChildren' in object ? object.hasChildren : this.getChildren(object).fetchSync().length;//this.root.filter(this.getCollectionFilter().eq('parentid', object.id)).length > 0;
        },

        getRootCollection: function (filters) {
            var ids = [];
            this.root.forEach(function(object){
                ids.push(object.id);
            });
            return this.root.filter(this.getCollectionFilter(filters).ni('parentid', ids));
        },
        
        getFullCollection: function(filters){
            return this.root.filter(this.getCollectionFilter(filters));
        },
        
        getChildren: function (object) {
            return this.root.filter(this.getCollectionFilter().eq('parentid', object.id));
        }
    });
});
