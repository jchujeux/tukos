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
        getGrid: function(){
        	var widget = this.widget;
        	return (widget.form ? (widget.form.form || widget.form) : widget.getParent().form).getWidget(this.storeDgrid)
        },
        get: function(id, options){// options is not used here. Remove ?
            if (typeof id == "undefined" || id == '' || !id || id == 0 ){
                return {id: '', name: ''};
            }else if(this.storeDgrid){
            	var grid = this.getGrid();
            	return grid.newRowPrefixNamedIdItem(id);
            }else{
                return {id: id, name: Pmg.namedId(id)};
/*
                var namedId = Pmg.namedId(id);
                if (namedId !== id){
                    return {id: id, name: namedId};
                }else{
                    return Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: {one: true, storeatts: {where: {id: id}, cols: ['id', 'name']}}}).then(function(response){
                        var obj = {};
                        obj[response.id] = {name: response.name, object: this.object};
                        Pmg.addExtendedIdsToCache(obj);
                        return {id: response.id, name: response.name + '(' + response.id + ')'};
                    });
                }
*/
            }
        },
    
        query: function(query, options){
            if (!query || query == {}){
                return QueryResults([]);
            }else{
                var queryOptions = this.queryOptions(query, options);
                if (this.storeDgrid){
                	var grid = this.getGrid(), items = [];
                	grid.store.forEach(function(item){
                		items.push({id: item.id, name: (item.name || '') + '(' + item.id + ')'});
                	});
                	return QueryResults(items);
                }else{
                    var dfdResponse = Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query: queryOptions ? {storeatts: queryOptions} : {}}, {}, '',  true);
                    var self = this,
                        total = dfdResponse.then(function(response){
                            return response.total;
                        });
                    var results = dfdResponse.then(function(response){
                        var length = response.items.length, items = response.items;
                        items.forEach(function(item){
                        	item.name = item.name + '(' + item.id + ')';
                        });
                        items.unshift({id: '', name: ''});
                        return items;
                    });
                    results = lang.delegate(results,{total: total});
                    return QueryResults(results);                	
                }
            }    
        },
        queryOptions: function(query, options){
            options = options || {};
            var queryOptions = {cols: this.cols ? ['id', 'name'].concat(this.cols) : ['id', 'name']};
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
