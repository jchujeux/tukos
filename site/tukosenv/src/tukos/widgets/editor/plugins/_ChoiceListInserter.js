define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/string", "tukos/TukosTooltipDialog", "tukos/PageManager"], 
function(declare, lang, dct, domStyle, string, TooltipDialog, Pmg) {

	var templateType = 'choiceList', templateClass = templateType + 'Template', instanceClass = templateType + 'Instance', templateIdPrefix = templateType + '_',
		cltTemplate = "<span class=\"" + instanceClass + "\" choicelistmenu=\"" + templateIdPrefix + "${cltName}\" onclick=\"getElementById('" + templateIdPrefix + "${cltName}" + "').style.display='none';\" " +
					"onblur=\"getElementById('" + templateIdPrefix + "${cltName}" + "').style.display='none';\" " + "onmousedown=\"(getElementById('dijitEditorBody') || {}).selectedChoiceSpan=this\"" +
					"oncontextmenu=\"event.preventDefault();var menu=getElementById(this.getAttribute('choicelistmenu'));menu.style.display='block';menu.style.left=event.pageX+'px';menu.style.top = event.pageY+'px';\">${selection}</span>",
		choices = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    

	var _choiceListInserter =  declare(null, {

        constructor: function(args){
			declare.safeMixin(this,args);
	    },
        inserterDialog: function(){
        	return this.cltDialog || (this.cltDialog = new TooltipDialog(this.cltDialogDescription()));
        },

        cltDialogDescription: function(){
            var inserter = this.inserter, loadColorPicker = lang.hitch(inserter, inserter.loadColorPicker), cltNameToWidgets = lang.hitch(this, this.cltNameToWidgets), i = 0,
            	widgetsList = [],
            	widgetsDescription = {
                    cltName: {type: 'StoreComboBox', atts: {label: Pmg.message('inserterName', 'tukos'), onChange: cltNameToWidgets, storeArgs: {}}},
                    colorParentCheckBox: {type: 'CheckBox', atts: {label: Pmg.message('colorParentLabel', 'tukos'), checked: false}}
            };
            choices.forEach(function(row){
            	var rowLabel = row + 'Label', rowValue = row + 'Value', rowColor = row + 'Color';
            	i += 1;
            	widgetsList.push(rowLabel, rowValue, rowColor);
            	widgetsDescription[rowLabel] = {type: 'HtmlContent', atts: {value: Pmg.message('choice', 'tukos') + ' ' + i, style: {textAlign: 'center', minWidth: '5em'}}};
            	widgetsDescription[rowValue] = {type: 'TextBox', atts: {}};
            	widgetsDescription[rowColor] = {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = loadColorPicker(rowColor, 'cltDialog')).startup(); callback();}}};                  
            });
            ['save', 'insert', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: Pmg.message(action), onClick: lang.hitch(this, this[action] || this.inserter[action], templateType)}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                        contents: {
                        	row1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: true}, widgets: ['cltName', 'colorParentCheckBox']},
                        	row2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: widgetsList},
                        	row3: {tableAtts: {cols: 4, customClass: 'labelsAndValues', showLabels: false}, widgets: ['save', 'insert', 'remove', 'close']}
                        }
                    }
                },
                onOpen: lang.hitch(this, function(){
                    var pane = this.cltDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), cltNameWidget = paneGetWidget('cltName');
                	cltNameWidget.store.setData(inserter.templateNames(templateType));
    		    	var selection = this.editor.selection, currentCltInstance = inserter.selectedInstance(selection, templateType);
    		    	if (currentCltInstance){
    		    		var cltId = currentCltInstance.getAttribute('choicelistmenu'), cltName = cltId.substr(templateType.length+1);
    		    		cltNameWidget.set('value', cltName);
    		    		this.cltNameToWidgets(cltName);
    		    	}
                })
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
            var pane = this.cltDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), valueOf = lang.hitch(pane, pane.valueOf), cltNameWidget = paneGetWidget('cltName'), cltName = cltNameWidget.get('value');
            if (cltName){
	            var cltId = templateIdPrefix + cltName, root = this.editor.document, cltNode = root.getElementById(cltId), colorParent = paneGetWidget('colorParentCheckBox').get('checked'), colorSetter = this.cltColorSetter;
	            if (cltNode){
	            	cltNode.remove();
	            }
	            cltNode = dct.create('menu', {id: cltId, className: templateClass, type: 'context', style: {position: 'absolute', listStyle: 'none', display: 'none'}}, root.getElementById('dijitEditorBody'), 'last');
	            cltNode.setAttribute('data-colorparent', colorParent);
	            choices.forEach(function(row){
	                var value = valueOf(row + 'Value'), color = valueOf(row + 'Color');
	                if (value){
	                	var onClick = "var clt=getElementById('dijitEditorBody').selectedChoiceSpan;clt.innerHTML=\"" + value + "\";" + colorSetter(color, colorParent) + "this.parentNode.style.display='none'",
	                		menuItem = dct.create('li', {label: value, innerHTML: value, onclick: onClick}, cltNode, 'last');
	                	if (color){
	                		menuItem.setAttribute('data-background-color', color);
	                	}
	                }
	            });
            	cltNameWidget.store.setData(this.inserter.templateNames(templateType));
            }else{
            	Pmg.addFeedback(Pmg.message('mustprovideaname', 'tukos'));
            }
	    },
	    
	    insert: function(){
	    	var pane = this.cltDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), cltName = valueOf('cltName');
	    	if (cltName){
		    	var selection = this.editor.selection, currentCltInstance = this.inserter.selectedInstance(selection, templateType);
	    		this.save();
	    		if (currentCltInstance){
	    			currentCltInstance.setAttribute('choicelistmenu', templateIdPrefix + cltName);
	    		}else{
	    			this.editor.execCommand('inserthtml', this.inserter.visualTag + string.substitute(cltTemplate, {cltName: cltName, selection: selection.getSelectedHtml() || '{' + cltName + '}'}) + this.inserter.visualTag);
	    		}
	    	}else{
            	Pmg.addFeedback(Pmg.message('mustprovideaname', 'tukos'));
	    	}
	    },
	    
	    htmlToRestore: function(selectedInstance){
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
            	paneGetWidget('colorParentCheckBox').set('checked', cltNode.getAttribute('data-colorparent') === 'false' ? false : true);
            	choices.some(function(row){
            		var choiceNode = choiceNodes.shift();
            		if (!choiceNode){
            			return true;
            		}else{
            			var color = choiceNode.getAttribute('data-background-color');
            			setValueOf(row + 'Value', choiceNode.innerHTML);
            			if (color){
                			setValueOf(row + 'Color', color);
                            domStyle.set(paneGetWidget(row + 'Color').iconNode, 'backgroundColor', color);            				
            			}
            		}
            	});
            }
	    }
    });
    _choiceListInserter.translations = {tukos: ['choice']};
    return _choiceListInserter;
});
