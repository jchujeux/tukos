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
        },
        getCollectionFilter: function(filters){
            if (!this.collectionFilter){
                this.collectionFilter = this.defaultCollectionFilter = new this.Filter();
            }
            if (filters){
            	var collectionFilter = this.defaultCollectionFilter;
            	filters.forEach(function(filter){
            		collectionFilter = collectionFilter[filter[0]](filter[1], filter[2]);
            	});
            	this.collectionFilter = collectionFilter;
            }
            return this.collectionFilter;
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

        getChildren: function (object) {
            return this.root.filter(this.getCollectionFilter().eq('parentid', object.id));
        }
    });
});
