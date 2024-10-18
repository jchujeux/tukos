/*
 * mixin patterned after native dstore/tree, to support the tukos parent - child model, in particular:
 * - the parent property is 'parentid' (versus 'parent' in native dstore)
 */
define(["dojo/_base/declare", "tukos/dstore/UserFilters", "tukos/utils"], function (declare, UserFilters, utils) {
    return declare(UserFilters, {
        constructor: function () {
            this.root = this;
        },
        mayHaveChildren: function (object) {
            return 'hasChildren' in object ? object.hasChildren : (object.id ? this.getChildren(object).fetchSync().length : false);
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
