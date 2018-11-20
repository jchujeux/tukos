define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/dom-construct", "dojo/string", "tukos/utils", "tukos/hiutils", "tukos/PageManager", "tukos/expressions",
		 "dojoFixes/dojox/editor/plugins/_EditorTableDialog", "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dcl, dct, string, utils, hiutils, Pmg, expressions, EditorTableDialog, messages){

    var actions = ['insert', 'cancel'];

    return declare(EditorTableDialog, {
       
        dialogAtts: function(){
            var actionsRowLayout = {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false/*, label: messages.forSelectedCells*/}, widgets: ['insert', 'cancel']},
            	headerRowWidgetsDescription = {
            		rows: {type: 'TukosNumberBox', atts: {title: messages.rows, style: {width: '5em'}}, attValueModule: domAttr}, 
            		columns: {type: 'TukosNumberBox', atts: {title: messages.columns, style: {width: '5em'}}, attValueModule: domAttr},
            		isWorksheet: {type: 'CheckBox', atts: {title: Pmg.message('is worksheet'), checked: false, onChange: lang.hitch(this, this.onChangeWorksheetCheckBox)}, attValueModule: domAttr},
            		sheetName: {type: 'TextBox', atts: {title: Pmg.message('sheetName'), style: {width: '10em'}, hidden: true}, attValueModule: domAttr},
            	},
                headerRowLayout = {linesAndRowsRow: {tableAtts: {cols: 4, customClass: 'labelsAndValues', label: messages.insertTableTitle, showLabels: true, orientation: 'horiz'}, widgets: ['rows', 'columns', 'isWorksheet', 'sheetName']}};
            return this._dialogAtts(headerRowWidgetsDescription, headerRowLayout,  actions, actionsRowLayout, ['verticalAlign']);
        },
        
        openDialog: function(){
            var activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget),
            	defaultAttsValue = {rows: 2, columns: 2, textAlign: 'default', width: '[100, "%"]', border: 1, cellPadding: 0, cellSpacing: 0};
            utils.forEach(defaultAttsValue, function(attValue, att){
                var widget = paneGetWidget(att);
                widget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(widget.iconNode, att, attValue);
                }
            });
        },

        onChangeWorksheetCheckBox: function(checked){
        	this.pane.getWidget('sheetName').set('hidden', !checked);
        	this.pane.resize();
        },
        insert: function(){
            var table = dct.create('table'), activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), isWorksheet = paneGetWidget('isWorksheet').get('checked'), 
            	rows = paneGetWidget('rows').get('value') || 1, columns = paneGetWidget('columns').get('value') || 1, firstRow = 0, firstCol = 0;
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attWidget = paneGetWidget(att);
                    attWidget.attValueModule.set(table, att, attWidget.get('value'));
                }
            });
            if (isWorksheet){
            	var sheetName = paneGetWidget('sheetName').get('value'), root = this.editor.document;
            	if (this.editor.document.getElementById(sheetName)){
            		Pmg.alert({title: Pmg.message('error - duplicate name'), content: Pmg.message('the chosen sheetName is already in use')});
            		return;
            	}
            	var template = expressions.template();
            	dcl.add(table, "tukosWorksheet");
            	domAttr.set(table, {id: sheetName, style: {tableLayout: 'fixed'}});
            	firstRow = 1;
            	rows += 1;
            	firstCol = 1;
            	columns += 1;
            	var tr = dct.create('tr', null, table);
            	dct.create('td', {contentEditable: false, innerHTML:'<b><i>' + sheetName + '</i></b>', style: {width: '5em', textAlign: 'center'}}, tr);
            	for (var c = firstCol; c < columns; c++){
            		dct.create('td', {contentEditable: false, innerHTML: utils.alphabet(c), style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}, tr);
            	}
            }
            for (var r = firstRow; r < rows; r++){
                var tr = dct.create('tr', null, table), atts;
                if (isWorksheet){
                	dct.create('td', {contentEditable: false, innerHTML: r, style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}, tr);
                }
                for (var c = firstCol; c < columns; c++){
                    if (isWorksheet){
                    	atts = {
                    		innerHTML: string.substitute(template,  {name: sheetName + '!' + utils.alphabet(c) + r, value: ' ', formula: '', visualPreTag: '', visualPostTag: ''}),
                        	onclick: "parent.tukos.onTdClick(this);", ondblclick: "parent.tukos.onTdDblClick(this);"
                    	};
                    }else{
                    	atts = {innerHTML: '&nbsp'};
                    }
                	var innerHTML = isWorksheet ? string.substitute(template,  {name: sheetName + '!' + utils.alphabet(c) + r, value: ' ', formula: '', visualPreTag: '', visualPostTag: ''}): '&nbsp;';
                	dct.create('td', atts, tr);
                }
            }
            this.editor._tablePluginHandler._prepareTable(table);
            this.editor.focus();
            this.editor.pasteHtmlAtCaret('<br>'+ table.outerHTML + '<br>');
            this.close();
        },
        cancel: function(){
            this.close();
        }
    });
});
