define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "tukos/utils", "dojoFixes/dojox/editor/plugins/_EditorTableDialog", "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dct, utils, EditorTableDialog, messages){

    var actions = ['insert', 'cancel'];

    return declare(EditorTableDialog, {
       
        dialogAtts: function(){
            var actionsRowLayout = {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false/*, label: messages.forSelectedCells*/}, widgets: ['insert', 'cancel']},
                 headerRowWidgetsDescription = {rows: {type: 'TextBox', atts: {title: messages.rows, style: {width: '5em'}}, attValueModule: domAttr}, columns: {type: 'TextBox', atts: {title: messages.columns, style: {width: '5em'}}, attValueModule: domAttr}},
                 headerRowLayout = {linesAndRowsRow: {tableAtts: {cols: 2, customClass: 'labelsAndValues', label: messages.insertTableTitle, showLabels: true, orientation: 'horiz'}, widgets: ['rows', 'columns']}};
            return this._dialogAtts(headerRowWidgetsDescription, headerRowLayout,  actions, actionsRowLayout, ['verticalAlign']);
        },
        
        openDialog: function(){
            var activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), defaultAttsValue = {rows: 2, columns: 2, align: 'default', width: '[100, "%"]', border: 1, cellPadding: 0, cellSpacing: 0};
            utils.forEach(defaultAttsValue, function(attValue, att){
                var widget = paneGetWidget(att);
                widget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(widget.iconNode, att, attValue);
                }
            });
        },

        insert: function(){
            var table = dct.create('table'), activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attWidget = paneGetWidget(att);
                    attWidget.attValueModule.set(table, att, attWidget.get('value'));
                }
            });
            var rows = paneGetWidget('rows').get('value') || 1, columns = paneGetWidget('columns').get('value') || 1;
            for (var r = 0; r < rows; r++){
                var tr = dct.create('tr', null, table);
                for (var c = 0; c < columns; c++){
                    dct.create('td', {innerHTML: '&nbsp;'}, tr);
                }
            }
            this.editor.focus();
            this.editor.pasteHtmlAtCaret('<br>'+ table.outerHTML + '<br>');
            this.close();
        },
        cancel: function(){
            this.close();
        }
        
    });
});
