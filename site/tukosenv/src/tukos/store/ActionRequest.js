/*
 * Read-only store to suppport tukos action requests using the storeatts aproach
 */
 define(["dojo/_base/lang", 'dojo/_base/array', "dojo/json", "dojo/_base/declare", "dojo/store/util/QueryResults", "tukos/PageManager", "tukos/utils"
], function(lang, arrayUtil, JSON, declare, QueryResults, Pmg, utils /*=====, Store =====*/){

    var base = null;
    return declare(base, {
        constructor: function(options){// ??
            declare.safeMixin(this, options);
        },
    
        getIdentity: function(object){
            return object['id'];
        },

        get: function(id, options){// options is not used here. Remove ? (in JsonRest used for headers)
            if (typeof id == "undefined" || !id || id == 0){
                return {id: '', name: ''};
            }else{
                var item = Pmg.getExtra(id);
                if (item){
                	return lang.mixin(item, {id: id});
                }
            	var query = {one: true, storeatts: {where: {id: id}}};
                if (this.params){query.params = this.params}
            	return Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: query}).then(function(response){
                    return response.item;
                });
            }
        },
    
        query: function(query, options){
            if (!query || query == {}){
                return QueryResults([]);
            }else{
                var queryOptions = this.queryOptions(query, options), params = this.params || options.params;
                var dfdQuery = lang.mixin(queryOptions ? {storeatts: queryOptions} : {}, params ? {params: params} : {});

                var dfdResponse = Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: dfdQuery}, {}, '', true);
                var self = this,
                    total = dfdResponse.then(function(response){
                        return response.total;
                    });
                var results = dfdResponse.then(function(response){
                    return response.items;
                });
                results = lang.delegate(results,{total: total});
                return QueryResults(results);
            }    
        },
        
        queryOptions: function(query, options){
            options = options || {};
            var queryOptions = {};
            if (query){
                queryOptions.where = {};
                for (var i in query){
                    if (query[i] instanceof RegExp){
                        var target = encodeURIComponent(query[i]).replace('*', '%'); // '*' which are part of the string might be wrongly transformed to '%' by this :-(
                        if (target[0] !== '%'){
                            target = '%' + target;
                        }
                        queryOptions.where[i] = ['LIKE', target];
                    }else{
                        queryOptions.where[i] = query[i];
                    }
                }
            }
            if(options.start >= 0 || options.count != Infinity){
                queryOptions = this.rangeOptions(queryOptions, options);
            }
            if(options.sort){
                queryOptions = this.sortOptions(queryOptions, options.sort);
            }
            return queryOptions;    
        },
    
        rangeOptions: function(queryOptions, options){
            if (options.start > 0 || options.count != Infinity){
                queryOptions.range = {offset: options.start, limit: options.count};
            }
            return queryOptions;
        },
    
        sortOptions: function(queryOptions, sort){
            queryOptions.orderBy = {};
            arrayUtil.forEach(sort, function(sortOption){
                queryOptions.orderBy[sortOption.attribute] = (sortOption.descending ? 'DESC' : 'ASC');
            });
            return queryOptions;
        }
    });
});
