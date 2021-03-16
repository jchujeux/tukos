define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/on", "tukos/BasicGrid", "tukos/dstore/Request", "tukos/menuUtils", "tukos/widgets/widgetCustomUtils", "tukos/_GridUserFilterMixin", "tukos/utils", "tukos/PageManager"], 
    function(declare, lang, ready, on, BasicGrid, Request, mutils, wcutils, _GridUserFilterMixin, utils, Pmg){
    return declare([BasicGrid, _GridUserFilterMixin], {
        constructor: function(args){
            lang.mixin(args.storeArgs, {storeParam: Pmg.get('sortParam'), target: Pmg.requestUrl(args.storeArgs)});
            args.store = lang.mixin(new Request(args.storeArgs), {postFetchAction: lang.hitch(this, this.postFetchAction)});
            args.collection = args.store.filter({contextpathid: args.form.tabContextId});
            for (var i in args.columns){
                var column = args.columns[i], field = column['field'];
                if (column.filter){
                	this.hasFilters = true;
                	console.log('field: ' + field + ' has filter');
                }
                if (column.rowsFilters){
                	this.mayHaveFilters = true;
                }
            }
        },
        postCreate: function(){
            this.inherited(arguments);
            this.on(on.selector(".dgrid-row, .dgrid-header", "contextmenu"), lang.hitch(this, this.contextMenuCallback));
            this.contextMenuItems.idCol = lang.hitch(this, wcutils.idColsContextMenuItems)(this).concat([{atts: {label: Pmg.message('togglerowheight'), onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}]);
        },
        resize: function(){
			var self = this, previousScrollPosition = this.getScrollPosition(), viewNode;
			this.inherited(arguments);
			setTimeout(function(){
				self.scrollTo(previousScrollPosition);
			}, 100);
	    	if (viewNode = this.form.domNode.parentNode){
		    	var style = this.bodyNode.style, bodyHeight = parseInt(window.getComputedStyle(document.body).getPropertyValue('height')), viewHeight = parseInt(window.getComputedStyle(viewNode).getPropertyValue('height')),
		    		maxHeight, newMaxHeight;
				style.maxWidth = parseInt(window.getComputedStyle(viewNode).getPropertyValue('width'));
				if (viewHeight !== this.previousViewHeight){
			    	maxHeight = style.maxHeight === '' ? 0 : parseInt(style.maxHeight);
			    	style.maxHeight = (maxHeight + bodyHeight - viewHeight) + 'px';
			    	newMaxHeight = parseInt(style.maxHeight);
			    	this.previousViewHeight = viewHeight;
				}
		    	console.log('maxHeight: ' + maxHeight + ' bodyHeight: ' + bodyHeight + ' viewHeight: ' + viewHeight + ' newMaxHeight: ' + newMaxHeight);
	    	}
        },
        contextMenuCallback: function(evt){
            evt.preventDefault();
        	console.log('mobile contextmenucallback');
        	var row = (this.clickedRow = this.row(evt)), cell = this.clickedCell = this.cell(evt), column = cell.column, clickedColumn = this.clickedColumn = this.column(evt);
                var menuItems = lang.clone(this.contextMenuItems);
                var colItems = row ? (column.onClickFilter || utils.in_array(column.field, this.objectIdCols) ? 'idCol' : 'row') : 'header';
                if (colItems !== 'header' && menuItems.canEdit && row.data.canEdit !== false){
                	menuItems[colItems] = menuItems[colItems].concat(menuItems.canEdit);
                }
                mutils.setContextMenuItems(this, menuItems[colItems].concat(lang.hitch(wcutils, wcutils.customizationContextMenuItems)(this)));
        },
        allowSelect: function(row){
            if (typeof row.id == 'undefined'){//is the header rather than a data row (?)
                return true;
            }else if (row.data.canEdit){
                return true;
            }else{
                return false;
            }
        },
        getObject: function(node){
            return this.grid.clickedRow.data;
        }, 
        postFetchAction: function(response){
        	if (response.summary){
        		this.form.setWidgets(response.summary);
        	}
        },        
        editInNewTab: function(grid){
            var field  = grid.clickedCell.column.field,
            query = {};
	        if (grid.clickedCell.column.onClickFilter){
	            var object  = grid.object === 'tukos' ? grid.cellValueOf('object') : grid.object,
	                fields = grid.clickedCell.column.onClickFilter;
	            for (var i in fields){
	                var field = fields[i];
	                var value = grid.cellValueOf(field);
	                if (value){
	                    query[field] = value;
	                }
	            }
	        }else{
	            var id = grid.cellValueOf(field);
	            if (id){
	                object = Pmg.objectName(id, grid.form.objectDomain);
	                query.id = id;
	            }
	        }
	        if (!utils.empty(query)){
	            Pmg.tabs.gotoTab({object: object, view: 'Edit', mode: 'Mobile', action: 'Tab', query: query});
	        }
	    },
        cellValueOf: function(field, idPropertyValue){
            if (idPropertyValue){
                if (this.collection.getSync){
                    return this.collection.getSync(idPropertyValue)[field];
                }else{
                    var query = {};
                    query[this.idProperty] = idPropertyValue;
                    return this.collection.filter(query).then(function(response){//to be tested!
                        var result =  response[0];
                        return (typeof result === "undefined" || result === null) ? '' : result;
                    });
                }
            }else{
                var result = this.clickedRowValues()[field];
                return (typeof result === "undefined" || result === null) ? '' : result;
            }
        }
    }); 
});
