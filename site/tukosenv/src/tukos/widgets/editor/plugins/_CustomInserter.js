define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/string", "dojo/json", "tukos/TukosTooltipDialog", "tukos/utils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, dct, domStyle, string, JSON, TooltipDialog, utils, Pmg, messages) {
    var inserterCell = '<div class="inserterCell" style="display: inline;"><textarea onblur="${textAreaBlurAction}" style="display: inline; height: 20px">${placeHolder}</textarea><span ondblclick="${spanClickAction}" style="display: none;"></span></div> ',
    spanClickAction = "var inserter=this; while((inserter=inserter.parentNode) && inserter.className!='inserterCell');var textArea=inserter.children[0];" +
    				  "this.style.display='none'; textArea.style.display='inline';textArea.focus();textArea.value=this.innerHTML",
    textAreaColorBlurAction =
    	"var parent=this.parentNode,span=parent.children[1], color;" +
    	"${colorSetter}" +
    	"span.style.display='inline';" +
    	"this.style.display='none';";

	return declare(null, {

        customInserter: function(){
        	return this.cirDialog || (this.cirDialog = new TooltipDialog(this.cirDialogDescription()));
        },

        cirDialogDescription: function(){
            var loadColorPicker = lang.hitch(this, this.loadColorPicker), insertNameToWidgets = lang.hitch(this, this.insertNameToWidgets), i = 0,
            	widgetsList = ['labelLabel', 'oprLabel', 'valueLabel', 'colorLabel'],
            	widgetsDescription = {
                    insertName: {type: 'StoreComboBox', atts: {label: messages.inserterName, onChange: insertNameToWidgets, storeArgs: {data: this.inserterNames()}}},
                    placeHolder: {type: 'TextBox', atts: {label: messages.defaultContent, value: '', style: {width: '20em'}}},
                    nanLabel: {type: 'HtmlContent', atts: {value: messages.nanLabel, style: {textAlign: 'center'}}},
                    nanCheckBox: {type: 'CheckBox', atts: {}},
                    nanColor: {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker('nanColor', 'cirDialog')).startup(); callback();}}},
                    elseLabel: {type: 'HtmlContent', atts: {value: messages.elseLabel, style: {textAlign: 'center'}}},
                    elseColor: {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker('elseColor', 'cirDialog')).startup(); callback();}}},
                    colorParentLabel: {type: 'HtmlContent', atts: {value: messages.colorParentLabel, style: {textAlign: 'center'}}},
                    colorParentCheckBox: {type: 'CheckBox', atts: {checked: true}},
                    labelLabel: {type: 'HtmlContent', atts: {value: ''}, style: {textAlign: 'center'}},
                    oprLabel: {type: 'HtmlContent', atts: {value: messages.oprLabel}, style: {textAlign: 'center'}},
                    valueLabel: {type: 'HtmlContent', atts: {value: messages.valueLabel}, style: {textAlign: 'center'}},
                    colorLabel: {type: 'HtmlContent', atts: {value: messages.colorLabel}, style: {textAlign: 'center'}}
            };
            ['one', 'two', 'three', 'four', 'five'].forEach(function(row){
            	i += 1;
            	var rowLabel = row + 'Label', rowOpr = row + 'Opr', rowValue = row + 'Value', rowColor = row + 'Color';
            	widgetsList.push(rowLabel, rowOpr, rowValue, rowColor);
            	widgetsDescription[rowLabel] = {type: 'HtmlContent', atts: {value: messages.criteria + ' ' + i, style: {textAlign: 'center'}}};
            	widgetsDescription[rowOpr] = {type: 'StoreSelect', atts: {style: {width: '8em'}, storeArgs: {data: [{id: '', name: ''},{id: '==', name: '='},{id: '&lt;', name: '<'}, {id: 'contains', name: messages.contains}/*, {id: 'doesnotcontain', name: messages.doesnotcontain}*/]}}};
            	widgetsDescription[rowValue] = {type: 'TextBox', atts: {}};
            	widgetsDescription[rowColor] = {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker(rowColor, 'cirDialog')).startup(); callback();}}};                  
            });
            messages['cirSave'] = messages['save'];
            messages['cirInsert'] = messages['insert'];
            ['cirSave', 'cirInsert', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: messages[action], onClick: lang.hitch(this, this[action])}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                        contents: {
                        	row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true}, widgets: ['insertName']},
                        	row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true}, widgets: ['placeHolder']},
                        	row3: {tableAtts: {cols: 7, showLabels: false}, widgets: ['nanLabel', 'nanCheckBox', 'nanColor', 'elseLabel', 'elseColor', 'colorParentLabel', 'colorParentCheckBox']},
                        	row4: {tableAtts: {cols: 4, showLabels: false}, widgets: widgetsList},
                        	row5: {tableAtts: {cols: 4, showLabels: false}, widgets: ['cirSave', 'cirInsert', 'remove', 'close']}
                        }
                    }
                },
                onOpen: function(){console.log('opening tooltip');}
            };
        	
        },

        cirSave: function(){
            var pane = this.cirDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), insertNameWidget = paneGetWidget('insertName'), insertName = insertNameWidget.get('value');
            if (insertName){
	            var insertId = 'inserter_' + insertName, content = JSON.stringify(this.widgetsToInserter()), existingInsert = this.editor.document.getElementById(insertId);
	            if (existingInsert){
	            	existingInsert.innerHTML = content;
	            }else{
	            	dct.create('div', {id: insertId, className: 'inserterTemplate', innerHTML: content, style: {display: 'none'}}, this.editor.document.getElementById('dijitEditorBody'));
	            	insertNameWidget.store.setData(this.inserterNames()) ;
	            }
            }else{
            	Pmg.addFeedback(messages.mustprovideaname);
            }
	    },
	    cirInsert: function(){
	    	var inserter = this.widgetsToInserter(), colorSetter = this.cirColorSetter(inserter), selectedElement = this.editor.selection.getSelectedElement();
	    	if (selectedElement){
	    		placeHolder = utils.trimExt(selectedElement.innerHTML || selectedElement.textContent) || inserter.placeHolder || messages.entervalue;
	    	}else{
    			var selectedHtml = utils.trimExt(this.editor.selection.getSelectedHtml());
	    		if (selectedHtml){
	    			placeHolder = selectedHtml;
	    		}else{
	    			var parent = this.editor.selection.getParentElement();
	    			placeHolder = utils.trimExt(parent.innerHTML) || inserter.placeHolder || messages.entervalue;
	    			parent.innerHTML = '';
	    		}
    		}
    		toInsert = string.substitute(inserterCell, {textAreaBlurAction: string.substitute(textAreaColorBlurAction, {colorSetter: colorSetter, placeHolder: placeHolder}), spanClickAction: spanClickAction, placeHolder: placeHolder});
            this.editor.execCommand("inserthtml", toInsert);
	    },

		cirColorSetter: function(args){// e.g {nan: 'white', options: [['&lt;', '160', 'red'], ...], elseColor: 'limegreen', colorParent: true}
			var closingParentheses = '', colorSetter = args.nan ? "var value = parseFloat(this.value);color = isNaN(value) ? 'white' : " : "var value = this.value;color = ";
			colorSetter += '((value === \'' + args.placeHolder + '\'|| !value) ? \'white\' : '; closingParentheses += ')';
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
								? "var pNode = parent.parentNode; if (!pNode.getAttribute('data-backgroundColor')){pNode.setAttribute('data-backgroundColor', pNode.style.backgroundColor);} pNode.style.backgroundColor=color;" 
								: "parent.style.backgroundColor=color;") +
							"span.innerHTML=this.value.trim() || '" + (args.placeHolder || messages.entervalue) + "';" ;

			return colorSetter;
	    },
	    
	    widgetsToInserter: function(){
            var pane = this.cirDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf),
            isNumeric = paneGetWidget('nanCheckBox').get('checked'), elseColor = valueOf('elseColor'), colorParent = paneGetWidget('colorParentCheckBox').get('checked'), placeHolder = valueOf('placeHolder'),
            inserter = {options: [], elseColor: elseColor, colorParent: colorParent, placeHolder: placeHolder || messages.entervalue}, options = inserter.options;
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
            	inserterDiv = this.editor.document.getElementById('inserter_' + insertName), inserter = inserterDiv ? JSON.parse(inserterDiv.innerHTML): {options: [], elseColor: '', placeHolder: '', colorParent: true},
            	nanColorWidget = paneGetWidget('nanColor'), elseColorWidget = paneGetWidget('elseColor'), nanColor = inserter.nan || '', elseColor = inserter.elseColor;
            paneGetWidget('nanCheckBox').set('checked', inserter.nan ? true : false);
        	nanColorWidget.set('value', nanColor);
        	domStyle.set(nanColorWidget.iconNode, 'backgroundColor', nanColor)
        	elseColorWidget.set('value', elseColor);
        	domStyle.set(elseColorWidget.iconNode, 'backgroundColor', elseColor)
            paneGetWidget('colorParentCheckBox').set('checked', inserter.colorParent);
            setValueOf('placeHolder', inserter.placeHolder);
            var options = inserter.options;
        	['one', 'two', 'three', 'four', 'five'].forEach(function(row){
            	var option = options.shift() || ['', '', ''];
        		setValueOf(row + 'Opr', option[0]);
        		setValueOf(row + 'Value', option[1]);
        		setValueOf(row + 'Color', option[2]);
                domStyle.set(paneGetWidget(row + 'Color').iconNode, 'backgroundColor', option[2]);
            });
	    },
        inserterNames: function(){
            var inserterDivs = Array.apply(null, this.editor.document.getElementsByClassName('inserterTemplate')), storeData = [{id: '', name: ''}];
            inserterDivs.forEach(function(div){
            	storeData.push({id: div.id, name: div.id.substring(9)});
            });
            return storeData;
        }
    });
});
