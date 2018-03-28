/*
 * Read-only store to suppport the ObjectSelect widget
 */
 define(["dojo/_base/lang", 'dojo/_base/array', "dojo/json", "dojo/_base/declare", "dojo/store/util/QueryResults", "tukos/PageManager", "tukos/utils"
], function(lang, arrayUtil, JSON, declare, QueryResults, Pmg, utils /*=====, Store =====*/){

    var base = null;
    return declare(base, {
        constructor: function(options){// ??
            declare.safeMixin(this, options);
        },
    
        previousStart: 0,
    
        getIdentity: function(object){
            return object['id'];
        },

        get: function(id, options){// options is not used here. Remove ?
            if (typeof id == "undefined" || id == '' || !id || id == 0 ){
                //return QueryResults([]);
                return {id: '', name: ''};
            }else{
                var namedId = Pmg.namedId(id);
                if (namedId !== id){
                    return {id: id, name: namedId};//QueryResults({id: id, name: namedId});
                }else{
                    return Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: {one: true, storeatts: {where: {id: id}, cols: ['id', 'name']}}}).then(function(response){
                        var obj = {};
                        obj[response.id] = {name: response.name, object: this.object};
                        Pmg.addExtendedIdsToCache(obj);
                        return {id: response.id, name: response.name + '(' + response.id + ')'};
                    });
                }
            }
        },
    
        query: function(query, options){
            if (!query || query == {}){
                return QueryResults([]);
            }else{
                var queryOptions = this.queryOptions(query, options);
                var dfdResponse = Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: queryOptions ? {storeatts: queryOptions} : {}}, {}, '',  true);
                var self = this,
                    total = dfdResponse.then(function(response){
                        return response.total;
                    });
                var results = dfdResponse.then(function(response){
                    var length = response.items.length;
                    var items = [{id: '', name: ''}];
                    for (var i = 0; i < length; i++){
                        items[i+1] = {id: response.items[i].id, name: response.items[i].name + '(' + response.items[i].id + ')'};
                    }
                    return items;
                });
                results = lang.delegate(results,{total: total});
                return QueryResults(results);
            }    
        },
        
        queryOptions: function(query, options){
            options = options || {};
            var queryOptions = {cols: ['id', 'name']};
            if (query){
                queryOptions.where = {};
                for (var i in query){
                    var queryItem = query[i];
                    if (queryItem instanceof RegExp){
                        queryOptions.where[i] = ['LIKE', encodeURIComponent('%' + queryItem.toString().slice(0, -1) + '%')];
                    }else{
                        queryOptions.where[i] = queryItem;
                    }
                }
            }
            if(options.start >= 0 || options.count >= 0){
                queryOptions = this.rangeOptions(queryOptions, options);
            }
            if(options && options.sort){
                queryOptions = this.sortOptions(queryOptions, options.sort);
            }
            return queryOptions;    
        },
    
        rangeOptions: function(queryOptions, options){
            if (options.start > 0 || options.count != Infinity){
                if (options.start > this.previousStart){// due to adding a blank item at the top of every page
                    options.start += -1;
                }
                this.previousStart = options.start;
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
