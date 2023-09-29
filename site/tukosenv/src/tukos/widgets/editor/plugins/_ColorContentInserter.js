define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/string", "dojo/json", "tukos/TukosTooltipDialog", "tukos/PageManager"], 
function(declare, lang, dct, domStyle, string, JSON, TooltipDialog, Pmg) {
    var templateType = 'colorContent', templateClass = templateType + 'Template', instanceClass = templateType + 'Instance', templateIdPrefix = templateType + '_',
		inserterCell = '<div class="' + instanceClass + '" style="display: inline;"><textarea onblur="${textAreaBlurAction}" style="display: inline; height: 20px">${placeHolder}</textarea><span ondblclick="${spanClickAction}" style="display: none;"></span></div> ',
		spanClickAction = "var inserter=this; while((inserter=inserter.parentNode) && inserter.className!='" + instanceClass + "');var textArea=inserter.children[0];" +
    				  "this.style.display='none'; textArea.style.display='inline';textArea.focus();textArea.value=this.innerHTML",
	    textAreaColorBlurAction =
	    	"var parent=this.parentNode,span=parent.children[1], color;" +
	    	"${colorSetter}" +
	    	"span.style.display='inline';" +
	    	"this.style.display='none';";

	var _colorContentInserter = declare(null, {

        constructor: function(args){
		    lang.mixin(this, args);
	    },
        inserterDialog: function(){
        	return this.cirDialog || (this.cirDialog = new TooltipDialog(this.cirDialogDescription()));
        },

        cirDialogDescription: function(){
            var inserter = this.inserter, loadColorPicker = lang.hitch(inserter, inserter.loadColorPicker), insertNameToWidgets = lang.hitch(this, this.insertNameToWidgets), i = 0,
            	widgetsList = ['labelLabel', 'oprLabel', 'valueLabel', 'colorLabel'],
            	widgetsDescription = {
                    insertName: {type: 'StoreComboBox', atts: {label: Pmg.message('inserterName', 'tukos'), onChange: insertNameToWidgets, storeArgs: {data: inserter.templateNames(templateType)}}},
                    //nanLabel: {type: 'HtmlContent', atts: {value: Pmg.message('nanLabel', 'tukos'), style: {textAlign: 'center'}}},
                    nanCheckBox: {type: 'CheckBox', atts: {label: Pmg.message('nanLabel', 'tukos')}},
                    nanColor: {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker('nanColor', 'cirDialog')).startup(); callback();}}},
                    //elseLabel: {type: 'HtmlContent', atts: {value: Pmg.message('elseLabel', 'tukos'), style: {textAlign: 'center'}}},
                    elseColor: {type: 'DropDownButton', atts: {title: Pmg.message('elseLabel', 'tukos'), iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker('elseColor', 'cirDialog')).startup(); callback();}}},
                    colorParentCheckBox: {type: 'CheckBox', atts: {label: Pmg.message('colorParentLabel', 'tukos'), checked: false}},
                    labelLabel: {type: 'HtmlContent', atts: {value: '', style: {textAlign: 'center', minWidth: '5em'}}},
                    oprLabel: {type: 'HtmlContent', atts: {value: Pmg.message('oprLabel', 'tukos')}, style: {textAlign: 'center'}},
                    valueLabel: {type: 'HtmlContent', atts: {value: Pmg.message('valueLabel', 'tukos')}, style: {textAlign: 'center'}},
                    colorLabel: {type: 'HtmlContent', atts: {value: Pmg.message('colorLabel', 'tukos')}, style: {textAlign: 'center'}}
            };
            ['one', 'two', 'three', 'four', 'five'].forEach(function(row){
            	i += 1;
            	var rowLabel = row + 'Label', rowOpr = row + 'Opr', rowValue = row + 'Value', rowColor = row + 'Color';
            	widgetsList.push(rowLabel, rowOpr, rowValue, rowColor);
            	widgetsDescription[rowLabel] = {type: 'HtmlContent', atts: {value: Pmg.message('criteria', 'tukos') + ' ' + i, style: {textAlign: 'center'}}};
            	widgetsDescription[rowOpr] = {type: 'StoreSelect', atts: {style: {width: '8em'}, storeArgs: {data: [{id: '', name: ''},{id: '==', name: '='},{id: '&lt;', name: '<'}, {id: 'contains', name: Pmg.message('contains', 'tukos')}]}}};
            	widgetsDescription[rowValue] = {type: 'TextBox', atts: {}};
            	widgetsDescription[rowColor] = {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker(rowColor, 'cirDialog')).startup(); callback();}}};                  
            });
            ['save', 'insert', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: Pmg.message(action, 'tukos'), onClick: lang.hitch(this, this[action], templateType)}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                        contents: {
                        	row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true}, widgets: ['insertName']},
                        	row3: {tableAtts: {cols: 4, showLabels: true}, widgets: ['nanCheckBox', 'nanColor', 'elseColor', 'colorParentCheckBox']},
                        	row4: {tableAtts: {cols: 4, showLabels: false}, widgets: widgetsList},
                        	row5: {tableAtts: {cols: 4, showLabels: false}, widgets: ['save', 'insert', 'remove', 'close']}
                        }
                    }
                },
                onOpen: function(){console.log('opening tooltip');}
            };
        	
        },

        close: function(){
			this.cltDialog.close();
			this.inserter.close();
		},
        remove: function(templateType){
			this.inserter.remove(this, templateType);
		},
        save: function(){
            var pane = this.cirDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), insertNameWidget = paneGetWidget('insertName'), insertName = insertNameWidget.get('value');
            if (insertName){
	            var insertId = templateIdPrefix + insertName, content = JSON.stringify(this.widgetsToInserter()), existingInsert = this.editor.document.getElementById(insertId);
	            if (existingInsert){
	            	existingInsert.innerHTML = content;
	            }else{
	            	dct.create('div', {id: insertId, className: templateClass, innerHTML: content, style: {display: 'none'}}, this.editor.document.getElementById('dijitEditorBody'));
	            	insertNameWidget.store.setData(this.inserter.templateNames(templateType)) ;
	            }
            }else{
            	Pmg.addFeedback(messages.mustprovideaname);
            }
	    },
	    insert: function(){
	    	var pane = this.cirDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), name = valueOf('insertName');
	    	if (name){
	    		var inserter = this.widgetsToInserter(), colorSetter = this.cirColorSetter(name, inserter), selection = this.editor.selection, selectedElement = selection.getSelectedElement(), placeHolder;
	    		this.save();
	    		this.inserter.remove(templateType);
	    		placeHolder = (selectedElement || {}).tagName === 'TD' && selectedElement.innerHTML ? selectedElement.innerHTML : (selection.getSelectedHtml() || '{' + name + '}');
	    		toInsert = string.substitute(inserterCell, {textAreaBlurAction: string.substitute(textAreaColorBlurAction, {colorSetter: colorSetter, placeHolder: placeHolder}), spanClickAction: spanClickAction, placeHolder: placeHolder});
	            this.editor.execCommand("inserthtml", this.inserter.visualTag + toInsert + this.inserter.visualTag);
	    	}else{
            	Pmg.addFeedback(messages.mustprovideaname);
	    	}
	    },

	    htmlToRestore: function(selectedInstance){
	    	return (((selectedInstance || {}).childNodes || [])[1] || {}).innerHTML || '';
	    },

		cirColorSetter: function(name, args){// e.g {nan: 'white', options: [['&lt;', '160', 'red'], ...], elseColor: 'limegreen', colorParent: true}
			var closingParentheses = '', colorSetter = (args.nan !== undefined)? "var value = parseFloat(this.value);color = isNaN(value) ? '" + args.nan + "' : " : "var value = parseFloat(this.value);color = ";
			colorSetter += '((value === \'{' + name + '}\'|| !value) ? \'white\' : '; closingParentheses += ')';
			args.options.forEach(function(option){
				var opr = option[0];
				if (opr === 'contains'){
					colorSetter += '(value.search(\'' + option[1] + '\') &gt; -1  ? \'' + option[2] + '\' : ';
				}else{
					colorSetter += '(value ' + opr + '\'' + option[1] + '\' ? \'' + option[2] + '\' : ';
				}
				closingParentheses += ')';					
			});
			colorSetter +=  "'" + args.elseColor + "'" + closingParentheses + ';';

			colorSetter +=  (args.colorParent 
								? "var pNode = parent.parentNode; if (pNode.getAttribute('data-backgroundColor')===null){pNode.setAttribute('data-backgroundColor', pNode.style.backgroundColor);} pNode.style.backgroundColor=color;" 
								: "parent.style.backgroundColor=color;") +
							"span.innerHTML=this.value.trim() || '{" + name + "}';" ;

			return colorSetter;
	    },
	    
	    widgetsToInserter: function(){
            var pane = this.cirDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf),
            isNumeric = paneGetWidget('nanCheckBox').get('checked'), elseColor = valueOf('elseColor'), colorParent = paneGetWidget('colorParentCheckBox').get('checked'),
            inserter = {options: [], elseColor: elseColor, colorParent: colorParent}, options = inserter.options;
            if (isNumeric){inserter.nan = valueOf('nanColor')}
            ['one', 'two', 'three', 'four', 'five'].forEach(function(row){
            	var opr = valueOf(row + 'Opr');
            	if (opr){
            		options.push([opr, valueOf(row + 'Value'), valueOf(row + 'Color')]);
            	}
            });
	    	return inserter;
	    },
	    insertNameToWidgets: function(insertName){
            var pane = this.cirDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf), setValueOf = lang.hitch(pane, pane.setValueOf),
            	inserterDiv = this.editor.document.getElementById(templateIdPrefix + insertName), inserter = inserterDiv ? JSON.parse(inserterDiv.innerHTML): {options: [], elseColor: '', colorParent: true},
            	nanColorWidget = paneGetWidget('nanColor'), elseColorWidget = paneGetWidget('elseColor'), nanColor = inserter.nan || '', elseColor = inserter.elseColor;
            paneGetWidget('nanCheckBox').set('checked', inserter.nan ? true : false);
        	nanColorWidget.set('value', nanColor);
        	domStyle.set(nanColorWidget.iconNode, 'backgroundColor', nanColor)
        	elseColorWidget.set('value', elseColor);
        	domStyle.set(elseColorWidget.iconNode, 'backgroundColor', elseColor)
            paneGetWidget('colorParentCheckBox').set('checked', inserter.colorParent);
            var options = inserter.options;
        	['one', 'two', 'three', 'four', 'five'].forEach(function(row){
            	var option = options.shift() || ['', '', ''];
        		setValueOf(row + 'Opr', option[0]);
        		setValueOf(row + 'Value', option[1]);
        		setValueOf(row + 'Color', option[2]);
                domStyle.set(paneGetWidget(row + 'Color').iconNode, 'backgroundColor', option[2]);
            });
	    }
    });
    _colorContentInserter.translations = {tukos: ['nanLabel', 'elseLabel', 'oprLabel', 'valueLabel', 'colorLabel', 'criteria', 'contains']};
    return _colorContentInserter;
});
