define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/on", "tukos/BasicGrid", "tukos/dstore/Request", "tukos/menuUtils", "tukos/widgets/widgetCustomUtils", "tukos/_GridUserFilterMixin", "tukos/utils", "tukos/PageManager"], 
    function(declare, lang, ready, on, BasicGrid, Request, mutils, wcutils, _GridUserFilterMixin, utils, Pmg){
    return declare([BasicGrid, _GridUserFilterMixin], {
        constructor: function(args){
            lang.mixin(args.storeArgs, {storeParam: Pmg.get('sortParam'), target: Pmg.requestUrl(args.storeArgs)});
            args.store = lang.mixin(new Request(args.storeArgs), {postFetchAction: lang.hitch(this, this.postFetchAction)});
            args.collection = args.store.filter({contextpathid: args.form.tabContextId});
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
	    	}
        },
        contextMenuCallback: function(evt){
            evt.preventDefault();
			evt.stopPropagation();
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
        }
    }); 
});
