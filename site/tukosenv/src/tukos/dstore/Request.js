/*
 * Read-only store to suppport use of dgrid in tukos
 */
define([
	'dojo/request',
	'dojo/_base/lang',
	'dojo/_base/array',
	'dojo/json',
	'dojo/_base/declare',
	'dstore/Store',
	'dstore/QueryResults',
    'tukos/PageManager'
], function (request, lang, arrayUtil, JSON, declare, Store, QueryResults, Pmg) {

	return declare(Store, {

		fetch: function () {
            
            var results = this.requestDialog(this.queryOptions({}));
			return new QueryResults(results.data, {response: results.response});
		},

		fetchRange: function (kwArgs) {

            var results = this.requestDialog(this.queryOptions({range: {offset: kwArgs.start, limit: kwArgs.end - kwArgs.start}}));
			return new QueryResults(results.data, {totalLength: results.total,response: results.response});
		},

        requestDialog: function(queryOptions){
            var response = Pmg.serverDialog({object: this.object, view: this.view, mode: this.mode, action: this.action, query:  queryOptions ? {storeatts: queryOptions} : {}}, {}, '', true);
			var collection = this;
			return {
				data: response.then(function (response) {
					var results = response.items;
					for (var i = 0, l = results.length; i < l; i++) {
						results[i] = collection._restore(results[i], true);
					}
					return results;
				}),
				total: response.then(function (response) {
					if (collection.postFetchAction){
						collection.postFetchAction(response);
					}
					return response.total;
				}),
				response: response.response
			};
        },

        queryOptions: function(queryOptions){
            arrayUtil.forEach(this.queryLog, function (entry) {
				var type = entry.type,
					optionMethod = type + 'Options';

				if (this[optionMethod]) {
					this[optionMethod].call(this, queryOptions, entry.normalizedArguments[0]);
				} else {
					console.warn('Unable to render query params for "' + type + '" query', entry);
				}
			}, this);
			queryOptions.where = lang.mixin(queryOptions.where || {}, this.userFilters());
            return queryOptions;
		},


		filterOptions: function (queryOptions, filter) {
			var type = filter.type;
			var args = filter.args;
			if (type === 'string' || type === 'or'){
				console.log('type "' + type + '" is not supported yet in tukos/dstore/Request');
			}else{
                if (!queryOptions.where){
                    queryOptions.where = {};
                    this.filterNumericKey = 0;
                }
                if (type === 'eq'){
                    queryOptions.where[args[0]] = args[1];
                }else if (type === 'and'){
                	this.filterOptions(queryOptions, args[0]);
                	this.filterOptions(queryOptions, args[1]);
                }else{// JCH - this does not make sense !
                    queryOptions.where[this.filterNumericIndex] = {col: args[0], opr: type, values: args[1]};
                    this.filterNumericKey += 1;
                }
            }
            return queryOptions;
		},

		sortOptions: function(queryOptions, sort){
			queryOptions.orderBy = {};
            arrayUtil.forEach(sort, function(sortOption){
                queryOptions.orderBy[sortOption.property] = (sortOption.descending ? 'DESC' : 'ASC');
            });
            return queryOptions;
        }
	});
});
