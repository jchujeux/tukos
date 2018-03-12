define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "tukos/utils", "dojoFixes/dojox/editor/plugins/_EditorTableDialog", "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dct, utils, EditorTableDialog, messages){

    var actions = ['merge', 'split', 'apply', 'remove', 'close'];

    return declare(EditorTableDialog, {
       
        dialogAtts: function(){
            var actionsRow = {
                    tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                    contents: {
                        col1: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.forSelectedCells}, widgets: ['merge', 'split']},
                        col2: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.forSelectedAtts}, widgets: ['apply', 'remove']},
                        col3: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false}, widgets: ['close']}
                    }
                },
                extraWidgetsDescription = {
                    verticalAlign: {type: 'StoreSelect', atts: {style: {width: '10em'}, storeArgs: {data: [{id: 'default', name: messages['default']}, {id: 'left', name: messages.left}, {id: 'center', name: messages.center}, {id: 'right', name: messages.right}]}}, attValueModule: domStyle}
                },
                headerRowLayout = {headerRow: {tableAtts: {cols: 1, customClass: 'labelsAndValues', label: messages.modifyTableSelectionTitle}}};
            return this._dialogAtts(extraWidgetsDescription, headerRowLayout, actions, actionsRow, []);
        },
        
        openDialog: function(){
            var selectedTd = this.selectedTds[0], activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                var attWidget = paneGetWidget(att), attValue = attWidget.attValueModule.get(selectedTd, att);
                attWidget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(attWidget.iconNode, att, attValue);
                }
                paneGetWidget(att + 'CheckBox').set('checked', utils.empty(attValue) ? false : true);
            });
        },

        apply: function(){
            var tds = this.selectedTds, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (pane.getWidget(att + 'CheckBox').checked){
                    dojo.forEach(tds, function(td){
                        var attWidget = paneGetWidget(att);
                        attWidget.attValueModule.set(td, att, attWidget.get('value'));
                    });
                }
            });
        },
        
        remove: function(){
            var tds = this.selectedTds, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attValueModule = paneGetWidget(att).attValueModule;
                    dojo.forEach(tds, function(td){
                        paneGetWidget(att).attValueModule.remove(td, att);
                    });
                }
            });
        },
        merge: function(){
            var self = this, tds = this.selectedTds, table = this.table;
            require(["redips/redipsTable"], function(redipsTable){
                dojo.forEach(tds, lang.hitch(this, function(td){
                    redipsTable.mark(true, td);
                }));
                redipsTable.merge('h', false, table);
                redipsTable.merge('v', true, table);
                this.close();
            });
        },
        split: function(){
            var self = this, tds = this.selectedTds, table = this.table;
            require(["redips/redipsTable"], function(redipsTable){
                dojo.forEach(tds, lang.hitch(this, function(td){
                    redipsTable.mark(true, td);
                    redipsTable.split('h', table);
                    redipsTable.mark(true, td);
                    redipsTable.split('v', table);
                }));
                this.close();
            });
        },
        
        getSelectedCells: function(){
            var cells = [];
            var tbl = this.getTableInfo().tbl;
            this.editor._tablePluginHandler._prepareTable(tbl);
            var e = this.editor;
            // Lets do this the way IE originally was (Looking up ids).  Walking the selection is inconsistent in the browsers (and painful), so going by ids is simpler.
            //var text = e._sCall("getSelectedHtml", [null]);
            var text = e.selection.getSelectedHtml([null]);
            var str = text.match(/id="*\w*"*/g);
            dojo.forEach(str, function(a){
                var id = a.substring(3, a.length);
                if(id.charAt(0) == "\"" && id.charAt(id.length - 1) == "\""){
                    id = id.substring(1, id.length - 1);
                }
                var node = e.byId(id);
                if(node && node.tagName.toLowerCase() == "td"){
                    cells.push(node);
                }
            }, this);
            if(!cells.length){// May just be in a cell (cursor point, or selection in a cell), so look upwards. for a cell container.
                var sel = dijit.range.getSelection(e.window);
                if(sel.rangeCount){
                    var r = sel.getRangeAt(0);
                    var node = r.startContainer;
                    while(node && node != e.editNode && node != e.document){
                        if(node.nodeType === 1){
                            var tg = node.tagName ? node.tagName.toLowerCase() : "";
                            if(tg === "td"){
                                return [node];
                            }
                        }
                        node = node.parentNode;
                    }
                }
            }
            return cells;
        }

    });
});
