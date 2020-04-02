define(['dojo/_base/declare', 'dojo/_base/lang', 'dojo/dom-construct', "tukos/utils", "tukos/evalutils", "tukos/widgetUtils", "tukos/PageManager"], function (declare, lang, dct, utils, eutils, wutils, Pmg) {
    return declare(null, {
        // summary:
        //      A mixin for dgrid components which renders
        //      a row with summary information (e.g. totals).
         
        // Show the footer area, which will hold the summary row
        showFooter: true,

        setSummary: function(){
        	if (this.summaryRow){
            	//utils.debounce(lang.hitch(this, function(){
        			var oldSummary = this.summary;
        			this.set('summary', this.getStoreSummary(this.collection, this.summaryRow.cols));
        			wutils.watchCallback(this, 'summary', oldSummary, this.summary);
        			console.log('set summary');
        			//}, 100
        		//));
        	}
    	},
    
        buildRendering: function () {
            this.inherited(arguments);
             
            var areaNode = this.summaryAreaNode =
                dct.create('div', {
                    className: 'summary-row',
                    role: 'row',
                    style: { overflow: 'hidden' }
                }, this.footerNode);
             
            // Keep horizontal alignment in sync
            this.on('scroll', lang.hitch(this, function () {
                areaNode.scrollLeft = this.getScrollPosition().x;
            }));
             
            // Process any initially-passed summary data
            if (this.summary) {
                this._setSummary(this.summary);
            }
        },
         
        _updateColumns: function () {
            this.inherited(arguments);
            if (this.summary) {
                // Re-render summary row for existing data,
                // based on new structure
                this._setSummary(this.summary);
            }
        },
         
        _renderSummaryCell: function (item, cell, column) {
            // summary:
            //      Simple method which outputs data for each
            //      requested column into a text node in the
            //      passed cell element.  Honors columns'
            //      get, formatter, and renderCell functions.
            //      renderCell is called with an extra flag,
            //      so custom implementations can react to it.
             
            var summaryCol = this.summaryRow.cols[column.field];
            if (summaryCol){
                var value = item[column.field] || '', atts = summaryCol.atts;
                cell.appendChild(document.createTextNode(atts && atts.formatType ? utils.transform(value, atts.formatType, atts.formatOptions, Pmg) : value));
            }
        },
         
        _setSummary: function (data) {
            // summary:
            //      Given an object whose keys map to column IDs,
            //      updates the cells in the footer row with the
            //      provided data.
             
            var tableNode = this.summaryTableNode;
             
            this.summary = data;
             
            // Remove any previously-rendered summary row
            if (tableNode) {
                dct.destroy(tableNode);
            }
             
            // Render row, calling _renderSummaryCell for each cell
            tableNode = this.summaryTableNode =
                this.createRowCells('td',
                    lang.hitch(this, '_renderSummaryCell', data));
            this.summaryAreaNode.appendChild(tableNode);
            // Force resize processing,
            // in case summary row's height changed
            if (this._started) {
                this.resize();
            }
        },
        getStoreSummary: function (store, summaryCols) {
            var result = {}, expressions, expression, self = this;
            for (var col in summaryCols){
                result[col] = '';
                expressions = summaryCols[col].content;
                for (var i in expressions){
                    expression = expressions[i];
                    if (typeof expression == 'string'){
                        result[col] += expression;
                    }else{
                        var res = (expression.init || 0);
                        var rhs = expression.rhs.replace(/#(.+)#/, "self.cellValue(row,'$1')");
                        var theFunction = eutils.eval(rhs, 'self, row');
                        store.filter(expression.filter).forEach(function(row){
                        	res += theFunction(self, row);
                        });
                        result[col] += res;
                    }
                }
            }
            return result;
        }
    });
});
