define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/string", "dojo/json", "tukos/TukosTooltipDialog", "tukos/utils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, dct, domStyle, string, JSON, TooltipDialog, utils, Pmg, messages) {

	var templateType = 'choiceList', templateClass = templateType + 'Template', instanceClass = templateType + 'Instance', templateIdPrefix = templateType + '_',
		cltTemplate = "<span class=\"" + instanceClass + "\" contextmenu=\"" + templateIdPrefix + "${cltName}\" onmousedown=\"getElementById('dijitEditorBody').selectedChoiceSpan=this\">${selection}</span>",
		choices = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    

	return declare(null, {

        choiceListInserter: function(){
        	return this.cltDialog || (this.cltDialog = new TooltipDialog(this.cltDialogDescription()));
        },

        cltDialogDescription: function(){
            var loadColorPicker = lang.hitch(this, this.loadColorPicker), cltNameToWidgets = lang.hitch(this, this.cltNameToWidgets), i = 0,
            	widgetsList = [],
            	widgetsDescription = {
                    cltName: {type: 'StoreComboBox', atts: {label: messages.choiceListName, onChange: cltNameToWidgets, storeArgs: {}}},
                    colorParentLabel: {type: 'HtmlContent', atts: {value: messages.colorParentLabel, style: {textAlign: 'center'}}},
                    colorParentCheckBox: {type: 'CheckBox', atts: {checked: true}}
            };
            choices.forEach(function(row){
            	var rowLabel = row + 'Label', rowValue = row + 'Value', rowColor = row + 'Color';
            	i += 1;
            	widgetsList.push(rowLabel, rowValue, rowColor);
            	widgetsDescription[rowLabel] = {type: 'HtmlContent', atts: {value: messages.choice + ' ' + i, style: {textAlign: 'center'}}};
            	widgetsDescription[rowValue] = {type: 'TextBox', atts: {}};
            	widgetsDescription[rowColor] = {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker(rowColor, 'cltDialog')).startup(); callback();}}};                  
            });
            messages['cltSave'] = messages['save'];
            messages['cltInsert'] = messages['insert'];
            ['cltSave', 'cltInsert', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: messages[action], onClick: lang.hitch(this, this[action], templateType)}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                        contents: {
                        	row1: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['cltName', 'colorParentLabel', 'colorParentCheckBox']},
                        	row2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: widgetsList},
                        	row3: {tableAtts: {cols: 4, customClass: 'labelsAndValues', showLabels: false}, widgets: ['cltSave', 'cltInsert', 'remove', 'close']}
                        }
                    }
                },
                onOpen: lang.hitch(this, function(){
                	console.log('opening tooltip');
                    var pane = this.cltDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), cltNameWidget = paneGetWidget('cltName');
                	cltNameWidget.store.setData(this.templateNames(templateType));
    		    	var selection = this.editor.selection, currentCltInstance = this.selectedInstance(selection, templateType);
    		    	if (currentCltInstance){
    		    		var cltId = currentCltInstance.getAttribute('contextmenu'), cltName = cltId.substr(templateType.length+1);
    		    		cltNameWidget.set('value', cltName);
    		    		this.cltNameToWidgets(cltName);
    		    	}
                })
            };
        	
        },

        cltSave: function(){
            var pane = this.cltDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf), cltNameWidget = paneGetWidget('cltName'), cltName = cltNameWidget.get('value');
            if (cltName){
	            var cltId = templateIdPrefix + cltName, root = this.editor.document, cltNode = root.getElementById(cltId), colorParent = paneGetWidget('colorParentCheckBox').get('checked'), colorSetter = this.cltColorSetter;
	            if (cltNode){
	            	cltNode.innerHTML = '';
	            }else{
	            	cltNode = dct.create('menu', {id: cltId, className: templateClass, type: 'context'}, root.getElementById('dijitEditorBody'), 'last');
	            }            
	            cltNode.setAttribute('data-colorparent', colorParent);
	            choices.forEach(function(row){
	                var value = valueOf(row + 'Value'), color = valueOf(row + 'Color');
	                if (value){
	                	var onClick = "var clt=getElementById('dijitEditorBody').selectedChoiceSpan;clt.innerHTML=\"" + value + "\";" + colorSetter(color, colorParent),
	                		menuItem = dct.create('menuitem', {label: value, onclick: onClick}, cltNode, 'last');
	                	if (color){
	                		menuItem.setAttribute('data-background-color', color);
	                	}
	                }
	            });
            	cltNameWidget.store.setData(this.templateNames(templateType));
            }else{
            	Pmg.addFeedback(messages.mustprovideaname);
            }
	    },
	    
	    cltInsert: function(){
	    	var pane = this.cltDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), cltName = valueOf('cltName');
	    	if (cltName){
		    	var selection = this.editor.selection, currentCltInstance = this.selectedInstance(selection, templateType);
	    		this.cltSave();
	    		if (currentCltInstance){
	    			currentCltInstance.setAttribute('contextmenu', templateIdPrefix + cltName);
	    		}else{
	    			this.editor.execCommand('inserthtml', this.visualTag + string.substitute(cltTemplate, {cltName: cltName, selection: selection.getSelectedHtml() || '{' + cltName + '}'}) + this.visualTag);
	    		}
	    	}else{
            	Pmg.addFeedback(messages.mustprovideaname);
	    	}
	    },
	    
	    choiceListHtmlToRestore: function(selectedInstance){
	    	return selectedInstance.innerHTML;
	    },

		cltColorSetter: function(color, colorParent){
			return ("clt.style.backgroundColor='" + color + "';" + (colorParent 
					? "var pNode=clt.parentNode; if (pNode.getAttribute('data-background-color')=== null){pNode.setAttribute('data-background-color', pNode.style.backgroundColor);} pNode.style.backgroundColor='" + color + "';"
					: '')
				  );
		},
	    
	    cltNameToWidgets: function(cltName){
            var pane = this.cltDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf), setValueOf = lang.hitch(pane, pane.setValueOf),
            	cltNode = this.editor.document.getElementById(templateIdPrefix + cltName);
            choices.forEach(function(row){
        		setValueOf(row + 'Value', '');
        		setValueOf(row + 'Color', '');
                domStyle.set(paneGetWidget(row + 'Color').iconNode, 'backgroundColor', '');
            });
            if (cltNode){
            	var choiceNodes = Array.apply(null, cltNode.children);
            	paneGetWidget('colorParentCheckBox').set('checked', cltNode.getAttribute('data-colorparent') || false);
            	choices.some(function(row){
            		var choiceNode = choiceNodes.shift();
            		if (!choiceNode){
            			return true;
            		}else{
            			var color = choiceNode.getAttribute('data-background-color');
            			setValueOf(row + 'Value', choiceNode.label);
            			if (color){
                			setValueOf(row + 'Color', color);
                            domStyle.set(paneGetWidget(row + 'Color').iconNode, 'backgroundColor', color);            				
            			}
            		}
            	});
            }
	    }
    });
});
