define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojo/ready", "dojo/when", "dojo/string", "dijit/_editor/_Plugin", "dijit/form/DropDownButton", "dijit/DropDownMenu", "dijit/MenuItem",
        "dijit/PopupMenuItem", "dijit/ColorPalette", "dijit/popup", "tukos/utils", "tukos/hiutils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, domStyle, ready, when, string, _Plugin, Button, Menu, MenuItem, PopupMenuItem, ColorPicker, popup, utils, hiutils, Pmg, messages) {
    var inserters = {
    	fieldTemplate: '${^@xxx}',
        subfieldTemplate: '${^@xxx|yyy}',
		clickableCheckbox: '&nbsp;<span class="clickablecheckbox" contenteditable="false" onclick="this.innerHTML = (this.innerHTML == \'☐\' ? \'☑\' : \'☐\')" style="cursor: pointer;">☑</span>&nbsp;',
		autoCheckbox: '&nbsp;<span class="autocheckbox" style="background-color: #F3F5F6;">${^@xxx|yyy}☐[value1, value2, ... ]</span>&nbsp;',
		checkboxTemplate: "<span class=\"checkboxTemplate\" style=\"background-color:aliceblue;\"><span contenteditable=\"false\" onclick=\"this.innerHTML = (this.innerHTML == '☐' ? '☑' : '☐')\" style=\"cursor: pointer;\">☑</span>${selectedHtml}</span><span class=\"checkboxTemplateEnd\" contenteditable=\"false\">■</span>",
		figureTemplate: "<figure style=\"display:inline-block;\">${selectedHtml}<figcaption>" + Pmg.message('entercaption') + "</figcaption></figure>",
		pageBreak: '&nbsp;<div class="pagebreak" style="width: 100%; height: 20px; border-bottom: 1px solid black; text-align: center"><span style="font-size: 20px; background-color: #F3F5F6; padding: 0 10px;">' + Pmg.message('pageBreak') + '</span></div>&nbsp;',
        pageNumber: '<span class="pagenumber" style="background-color: #F3F5F6;">xx</span>&nbsp;',
        numberOfPages: '<span class="numberofpages" style="background-color: #F3F5F6">NN</span>&nbsp;',
        tooltip: '<span style="color: blue; cursor: pointer;" title="tooltipcontent">textwithtooltip</span>'},

	    inserterOptions = Object.keys(inserters);
        
    var Inserter = declare([_Plugin], {
    
        iconClassPrefix: "dijitAdditionalEditorIcon",
        
        visualTag: utils.visualTag(),

        _initButton: function(){
            this.button = new Button({
                label: messages["insertHtml"],
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "InsertPageBreak",
                tabIndex: "-1",
                loadDropDown: lang.hitch(this, "_loadDropDown")
            });
        },

        updateState: function(){
            // Over-ride for button state control for disabled to work.
            this.button.set("disabled", this.get("disabled"));
        },

        setEditor: function(editor){
            this.editor = editor;
            this._initButton();
        },
        
        _loadDropDown: function(callback){
        	//when(Pmg.serverTranslations(_HtmlSourceInserter.translations), lang.hitch(this, function(){
				require(["tukos/widgets/editor/plugins/_ExpressionInserter", "tukos/widgets/editor/plugins/WidgetEditor", "tukos/widgets/editor/plugins/_ColorContentInserter", "tukos/widgets/editor/plugins/_ChoiceListInserter", "tukos/widgets/editor/plugins/_HtmlSourceInserter"], 
	        			lang.hitch(this, function(ExpressionInserter, WidgetEditor, _ColorContentInserter, _ChoiceListInserter, _HtmlSourceInserter){
	        		var dropDown = (this.dropDown = this.button.dropDown = new Menu()), editor = this.editor, insert = this._insert, expression = new ExpressionInserter({inserter: this}),
	        			widgetEditor = new WidgetEditor({editor: editor, button: this.button});
	            	inserterOptions.forEach(function(option){
	            		dropDown.addChild(new MenuItem({label: Pmg.message(option), onClick: function(){insert(option, editor);}}));
	            	});
        			when(Pmg.serverTranslations(utils.mergeRecursive(utils.mergeRecursive(utils.mergeRecursive({tukos: ['htmlSource', 'expressionEditor', 'widgetEditor', 'inserterName', 'colorParentLabel', 'mustprovideaname']}, _HtmlSourceInserter.translations, true), _ChoiceListInserter.translations, true), _ColorContentInserter.translations, true)), lang.hitch(this, function(){
		            	const colorContentInserter = new _ColorContentInserter({editor: this.editor, inserter: this}), 
		            		  choiceListInserter = new _ChoiceListInserter({editor: this.editor, inserter: this}), 
		            		  htmlSourceInserter = new _HtmlSourceInserter({editor: this.editor, inserter: this});
		                dropDown.addChild(new PopupMenuItem({label: messages.colorcontentinserter, popup: (this.cirDialog = lang.hitch(colorContentInserter, colorContentInserter.inserterDialog)())}));
		                dropDown.addChild(new PopupMenuItem({label: messages.choicelistinserter, popup: (this.cltDialog = lang.hitch(choiceListInserter, choiceListInserter.inserterDialog)())}));
		                dropDown.addChild(new PopupMenuItem({label: Pmg.message('htmlSource', 'tukos'), popup: lang.hitch(htmlSourceInserter, htmlSourceInserter.inserterDialog)()}));
		                dropDown.addChild(new PopupMenuItem({label: Pmg.message('expressionEditor', 'tukos'), popup: lang.hitch(expression, expression.inserterDialog)()}));
		                dropDown.addChild(new PopupMenuItem({label: Pmg.message('widgetEditor', 'tukos'), popup: lang.hitch(widgetEditor, widgetEditor.inserterDialog)()}));
		            	ready(callback);
					}));
	        	}));
        },
        _insert: function(option, editor){
            var htmlTemplate = inserters[option], match = htmlTemplate.match(/<([^ ]*)/);
            if (match){
	            var tag = match[1], ancestor = editor.getAncestorElement(tag);
	            if (ancestor){
	            	if (option === 'checkboxTemplate'){
	            		if (ancestor.innerHTML === '☐' || ancestor.innerHTML === '☑'){
		                	hiutils.removeCheckbox(ancestor.parentNode)
		                	return;
	            		}
	            	}else if (ancestor.className === option.toLowerCase()){
	            		hiutils.removeNode(ancestor);
	            		return;
	            	}
	            }
            }
	        if(option === 'clipboard'){
	        	editor.execCommand("paste", false, null);
	        }else{
	            editor.execCommand("inserthtml", (['checkboxTemplate', 'figureTemplate'].includes(option) ? string.substitute(htmlTemplate, {selectedHtml: editor.selection.getSelectedHtml() || ' ? '}): htmlTemplate));	        	
	        }
        },
        
        loadColorPicker: function(widgetName, dialogName){
        	return new ColorPicker({onChange: lang.hitch(this, this.onChangeColor, widgetName, dialogName), onBlur: function(){dijit.popup.close(this);}});
        },
        onChangeColor: function(widgetName, dialogName, newColor){
            var pane = this[dialogName].pane, paneGetWidget = lang.hitch(pane, pane.getWidget), widget = paneGetWidget(widgetName);
            domStyle.set(widget.iconNode, 'backgroundColor', newColor);
            widget.set('value', newColor);
        },
	    close: function(){
	    	popup.close(this.button.dropDown);
	    },
        remove: function(subInserter, templateType){
        	var selectedInstance = this.selectedInstance(this.editor.selection, templateType);
        	if (selectedInstance){
        		var contentToRestore = subInserter.htmlToRestore(selectedInstance);
        		if (selectedInstance.parentNode){
                	var node = selectedInstance.parentNode, previousBackgroundColor =  node.getAttribute('data-backgroundColor')
                	if (previousBackgroundColor !== null){
                		domStyle.set(node, 'backgroundColor', previousBackgroundColor);
                    	node.removeAttribute('data-backgroundColor');
                	}
                }
                hiutils.removeNode(selectedInstance);
                //this.editor.execCommand('inserthtml', contentToRestore);
                this.editor.pasteHtmlAtCaret(contentToRestore, true);
        	}
        },
        
        selectedInstance: function(selection, templateType){
        	var instanceClass = templateType + 'Instance', node = selection.getSelectedElement() || selection.getParentElement(), instance = node;
        	while(instance.className !== instanceClass && instance.id !== 'dijitEditorBody' && (instance = instance.parentNode)){};
        	if (instance.className === instanceClass){
        		return instance;
        	}else{
        		var selectedHtml = selection.getSelectedHtml(), children = Array.apply(null, node.children);
	        		if (children.some(function(child){
	        			if (selectedHtml === child.outerHTML){
	        				if (child.className === instanceClass){
	        					instance = child;
	        					selection.selectElement(instance);
	        				}else{
	        					instance = null;
	        				}
	        				return true;
	        			}else{
	        				return false;
	        			}
	        			})){
	        			return instance;
	        		}else{
	        			return null;
	        		}
        	}
        },
	    templateNames: function(templateType){
            var templateClass = templateType + 'Template', templateNodes = Array.apply(null, this.editor.document.getElementsByClassName(templateClass)), storeData = [{id: '', name: ''}], pos = templateType.length + 1;
            templateNodes.forEach(function(node){
            	storeData.push({id: node.id, name: node.id.substring(pos)});
            });
            return storeData;
        }

    });
	_Plugin.registry['Inserter'] = function(){return new Inserter({})};
	
    return Inserter;

});
