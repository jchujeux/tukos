define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/dom-construct", "dojo/string", "tukos/utils", "tukos/hiutils", "tukos/PageManager", "tukos/expressions",
	"tukos/widgets/editor/plugins/_TagEditDialog"], 
    function(declare, lang, domAttr, domStyle, dcl, dct, string, utils, hiutils, Pmg, expressions, _TagEditDialog){

    return declare(_TagEditDialog, {
       
        dialogAtts: function(){
            var actions = ['insert', 'cancel'], actionsRowLayout = {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false}, widgets: actions},
            	headerRowWidgetsDescription = {
            		rows: {type: 'TukosNumberBox', atts: {title: Pmg.message('rows'), style: {width: '5em'}}, attValueModule: domAttr}, 
            		columns: {type: 'TukosNumberBox', atts: {title: Pmg.message('columns'), style: {width: '5em'}}, attValueModule: domAttr},
            		isWorksheet: {type: 'CheckBox', atts: {title: Pmg.message('is worksheet'), checked: false, onChange: lang.hitch(this, this.onChangeWorksheetCheckBox)}, attValueModule: domAttr},
            		sheetName: {type: 'TextBox', atts: {title: Pmg.message('sheetName'), style: {width: '10em'}, hidden: true}, attValueModule: domAttr}
            	},
                headerRowLayout = {linesAndRowsRow: {tableAtts: {cols: 4, customClass: 'labelsAndValues', label: Pmg.message('insertTable'), showLabels: true, orientation: 'horiz'}, widgets: ['rows', 'columns', 'isWorksheet', 'sheetName']}};
            return this._dialogAtts(headerRowWidgetsDescription, headerRowLayout,  actions, actionsRowLayout, this.editableAtts);
        },
        defaultAttsValue: {rows: 2, columns: 2, pageBreakInside: 'avoid', textAlign: 'default', width: '100%', border: 1, cellPadding: 0, cellSpacing: 0}, 
        insert: function(){
            var table = dct.create('table'), tbody = dct.create('tbody', null, table), includedAtts = this.includedAtts, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget),
            	isWorksheet = paneGetWidget('isWorksheet').get('checked'), rows = paneGetWidget('rows').get('value') || 1, columns = paneGetWidget('columns').get('value') || 1, firstRow = 0, firstCol = 0;
            includedAtts.forEach(function(att){
                var attWidget = paneGetWidget(att);
                attWidget.attValueModule.set(table, att, attWidget.get('value'));
            });
            if (isWorksheet){
            	var sheetName = paneGetWidget('sheetName').get('value').replace(/ /g, '_'), root = this.editor.document;
            	if (!sheetName){
            		Pmg.alert({title: Pmg.message('errorEmptyName'), content: Pmg.message('You need to enter a new sheet name')});
            	}else if (this.editor.document.getElementById(sheetName)){
            		Pmg.alert({title: Pmg.message('errorDuplicateName'), content: Pmg.message('the chosen sheetName is already in use')});
            		return;
            	}
            	var template = expressions.template();
            	dcl.add(table, "tukosWorksheet");
            	domAttr.set(table, {id: sheetName, style: {tableLayout: 'fixed'}});
            	firstRow = 1;
            	rows += 1;
            	firstCol = 1;
            	columns += 1;
            	var tr = dct.create('tr', null, tbody);
            	dct.create('td', {contentEditable: false, innerHTML:'<b><i>' + sheetName + '</i></b>', style: {width: '5em', textAlign: 'center'}}, tr);
            	for (var c = firstCol; c < columns; c++){
            		dct.create('td', {contentEditable: false, innerHTML: utils.alphabet(c), style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}, tr);
            	}
            }
            for (var r = firstRow; r < rows; r++){
                var tr = dct.create('tr', null, tbody), atts;
                if (isWorksheet){
                	dct.create('td', {contentEditable: false, innerHTML: r, style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}, tr);
                }
                for (var c = firstCol; c < columns; c++){
                    if (isWorksheet){
                    	atts = {
                    		innerHTML: string.substitute(template,  {name: sheetName + '!' + utils.alphabet(c) + r, value: '', formula: '', visualPreTag: '', visualPostTag: ''}),
                        	onclick: "parent.tukos.onTdClick(this);", ondblclick: "parent.tukos.onTdDblClick(this);"
                    	};
                    }else{
                    	atts = {innerHTML: '&nbsp'};
                    }
                	//var innerHTML = isWorksheet ? string.substitute(template,  {name: sheetName + '!' + utils.alphabet(c) + r, value: '', formula: '', visualPreTag: '', visualPostTag: ''}): '&nbsp;';
                	dct.create('td', atts, tr);
                }
            }
            this.editor._tablePluginHandler._prepareTable(table);
            this.editor.focus();
            this.editor.pasteHtmlAtCaret('<br>'+ table.outerHTML + '<br>');
            this.close();
        }
    });
});
