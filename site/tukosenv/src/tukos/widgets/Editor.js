define (["dojo/_base/declare", "dojo/_base/array", "dojo/_base/connect", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/has", "dojo/dom", "dojo/dom-style", "dojo/dom-class", "dojo/when", "dojo/aspect", "dijit/Editor",  "dijit/_editor/RichText",
         "tukos/widgets/editor/ShortCutKeys", "tukos/PageManager", "tukos/expressions",
         "dijit/_editor/plugins/FontChoice", "dijit/_editor/plugins/TextColor", "tukos/widgets/editor/plugins/LinkDialog",  "dojoFixes/dijit/_editor/plugins/FullScreen",
         "dijit/_editor/plugins/Print", "dojoFixes/dijit/_editor/plugins/ViewSource",
         "dojox/editor/plugins/StatusBar", "dojox/editor/plugins/FindReplace", 
         "tukos/widgets/editor/plugins/TablePlugins", "tukos/utils", "tukos/hiutils", "tukos/menuUtils", "tukos/widgets/editor/plugins/TukosLinkDialog",
         "tukos/widgets/editor/plugins/TemplateProcess","tukos/widgets/editor/plugins/Inserter","tukos/widgets/editor/plugins/MathMLEdit","tukos/widgets/editor/plugins/SelectionEditor","tukos/widgets/editor/plugins/FitImage",
         "tukos/StoreComboBox", "dojo/domReady!"], 
    function(declare, array, connect, lang, Deferred, has, dom, domStyle, dcl, when, aspect, Editor, RichText, ShortCutKeys, PageManager, expressions, FontChoice, TextColor, LinkDialog, FullScreen, print/*, AlwaysShowToolbar*/, ViewSource, StatusBar, FindReplace,
    		 TablePlugins, utils, hiutils, mutils, TukosLinkDialog, TemplateProcess, Inserter, MathMLEdit, SelectionEditor, FitImage, StoreComboBox){
	return declare([Editor, ShortCutKeys], {

    	constructor: function(args){
            args.plugins = ['undo', 'redo','|','bold','italic','underline','strikethrough','subscript','superscript','removeFormat','|', 'insertOrderedList', 'insertUnorderedList', 'indent', 'outdent',
                            'justifyLeft', 'justifyCenter', 'justifyRight','justifyFull', 'insertHorizontalRule'/*, 'EnterKeyHandling'*/];
            args.extraPlugins = args.extraPlugins ||  
                ['fontName', 'fontSize', 'formatBlock', 'foreColor', 'hiliteColor', 'createLink', 'unlink', 'insertImage', 'fullScreen', {name: 'viewSource', stripScripts: false}, 'TukosLinkDialog'/*, 'ChoiceList', 'TemplateProcess'*/, 
                 'statusBar', 'insertTable', 'modifyTable', 'modifyTableSelection', 'Inserter', 'MathMLEdit', 'print', 'FindReplace', 'SelectionEditor', 'FitImage'];
            if (args.optionalPlugins){
                args.extraPlugins = args.extraPlugins.concat(args.optionalPlugins);
            }
            args.styleSheets = require.toUrl('dijit/themes/claro/claro.css');
        },
        changeStyle: function(property, value){
            this.document.body.style[property] = value;
        },
        postCreate: function(){
            this.inherited(arguments);
            this.customUndo = true;
            this.contentDomPostFilters = [];//jch: don't eliminate empty nodes at end, makes life of the end-user more difficult
            this.watch('value',  lang.hitch(this, function(name, oldValue, newValue){
                if (hiutils.hasUntranslation(newValue)){
                    this.serverValueDeferred = new Deferred();
                    when(hiutils.untranslateParams(newValue, this), lang.hitch(this, function(newValue){
                    	this.serverValue = newValue;
                        this.serverValueDeferred.resolve(newValue);
                    }));
                }else if (hiutils.hasTranslation(newValue)){
                    this.serverValue = newValue;
                    delete this.serverValueDeferred;
                }else{
                    delete this.serverValue;
                    delete this.serverValueDeferred;
                }
            }));
            this.addShortCutKeys();
    		['bold', 'italic', 'underline', 'strikethrough'].forEach(lang.hitch(this, function(command){
    			this['_' + command + 'Impl'] = lang.hitch(this, function(){
          			var selection = this.selection, selectedElement = selection.getSelectedElement(), selectedHtml = selection.getSelectedHtml(), editor = this,
      					commandStyleAtts = {bold: ['fontWeight', 'bold'], italic: ['fontStyle', 'italic'], underline: ['textDecoration', 'underline'], 'strikethrough': ['textDecoration', 'lineThrough']},
      					att = commandStyleAtts[command][0], value = commandStyleAtts[command][1];
	      			if (selectedElement && selectedElement.tagName === 'TD'){
	      				domStyle.set(selectedElement, att, selectedElement.style[att] === value ? '' : value);
	      			}else{
	      				if (!this.prepareModifyTableSelection(function(){
	      					var selectedTds = this.selectedTds;
	      					if (selectedTds.length > 1){
	      						selectedTds.forEach(function(td){
	      		      				domStyle.set(td, att, td.style[att] === value ? '' : value);
	      						});
	      					}else{
	      						editor.document.execCommand(command, false);
	      					}
	      				})){
	      					editor.document.execCommand(command, false);
	      				}
	      			}
				});
    		}));
    	    tukos.expressions = expressions;
    	    tukos.onTdClick = lang.hitch(this, function(td){
    	    	//evt.stopPropagation();
    	    	if (this.focused){
    	    		this.selectElement(td);
    	    	}
    	    });
    	    tukos.onTdDblClick = lang.hitch(this, function(td){
    	    	if (this.focused){
    	    		this.begEdit();
    	    		expressions.onClick(td.children[0]);
    	    	}
    	    });
    	    tukos.onExpClick = lang.hitch(this, function(expression){
    	    	if (this.focused){
    	    		this.begEdit();
    	    		expressions.onClick(expression);
    	    	}
    	    });
    	    tukos.onExpBlur = lang.hitch(this, function(expression){
    	    	expressions.onBlur(expression);
	    		this.endEdit();
    	    });
    	    aspect.after(this, 'onDisplayChanged', lang.hitch(this, function(){
    			var document = this.document, wSelection;
    	    	if (document && (wSelection = document.getSelection())){
    				var eSelection = this.selection, anchorNode = wSelection.anchorNode, parentNode = anchorNode.parentNode, path = parentNode.nodeName + ' > ';
        			while(parentNode && (parentNode.nodeName !== 'BODY')){
        				parentNode = parentNode.parentNode;
        				path = parentNode.nodeName + ' > ' + path;
        			}
    				this.statusBar.set('value', path + anchorNode.nodeName + (anchorNode.nodeType == 1 ? ('(' + (anchorNode.childNodes[wSelection.anchorOffset] || {}).nodeName + ')') : '') + 
    						'(anchorOffset: ' + wSelection.anchorOffset + ' anchorFocus: ' + wSelection.focusOffset + ')');
    			}
    	    }));
        },
		execCommand: function(command, argument){
			var returnValue, editorFocused;
			if(command == 'undo' || command == 'redo'){
				return this[command]();
			}else{
				this.endEditing();
				this._beginEditing();
				editorFocused = this.focused;
				//focus() is required for IE to work
				//In addition, focus() makes sure after the execution of
				//the command, the editor receives the focus as expected
				//if(this.focused){
					// put focus back in the iframe, unless focus has somehow been shifted out of the editor completely
					//this.focus();
				//}
	
				command = this._normalizeCommand(command, argument);
	
				if(argument !== undefined){
					if(command === "heading"){
						throw new Error("unimplemented");
					}else if(command === "formatblock" && (has("ie") || has("trident"))){
						// See http://stackoverflow.com/questions/10741831/execcommand-formatblock-headings-in-ie.
						// Not necessary on Edge though.
						argument = '<' + argument + '>';
					}
				}
	
				//Check to see if we have any over-rides for commands, they will be functions on this
				//widget of the form _commandImpl.  If we don't, fall through to the basic native
				//exec command of the browser.
				var implFunc = "_" + command + "Impl";
				if(this[implFunc]){
					returnValue = this[implFunc](argument);
				}else{
					argument = arguments.length > 1 ? argument : null;
					if(argument || command !== "createlink"){
						returnValue = this.document.execCommand(command, false, argument);
					}
				}
	
				if(editorFocused){
					// put focus back in the iframe, unless focus has somehow been shifted out of the editor completely
					this.focus();
				}
				this.onDisplayChanged();
				this._endEditing();
				return returnValue;
			}
		},
        begEdit: function(){
            this.beginEditing();
        },

        endEdit: function(){
            this.endEditing();
            this.onDisplayChanged();
        },
		setValue: function(value){
			if(!this.isLoaded){
				// try again after the editor is finished loading
				this.onLoadDeferred.then(lang.hitch(this, function(){
					this.setValue(value);
				}));
				return;
			}
            if (hiutils.hasTranslation(value)){
                this.serverValue = value;
                var _arguments = arguments;
                when(hiutils.translateParams(value, this), lang.hitch(this, function(newValue){
                    value = newValue;
                    this.inherited(_arguments);
                }));
            }else{
                if (!hiutils.hasUntranslation(value)){
                    delete this.serverValue;
                    delete this.serverValueDeferred;
                }
                this.inherited(arguments);
            }
        },

        _getValueAttr: function(){
               var value = this.inherited(arguments), forceSpace = '';//this.isInViewSource && this.isInViewSource() ? '' : '&nbsp;';
               return value ? forceSpace + value.replace(/<span><\/span>|colspan="1"|rowspan="1"/g, '').replace(/[\n\t ]+/g, ' ').trim() + forceSpace : value;
        },

        _getServerValueAttr: function(){
            var deferred = this.serverValueDeferred;
            return (deferred ? (deferred.isResolved() ? this.serverValue : deferred) : this.serverValue);
        },
        startup: function(){
        	if (utils.isObject(this.style)){// richtext only supports string notation
                var style = this.style, changeStyle = lang.hitch(this, this.changeStyle);
                delete this.style;
            }
            this.inherited(arguments);
            this.onLoadDeferred.then(lang.hitch(this, function(){
            	if (style){
                    utils.forEach(style, function(value, property){
                        changeStyle(property, value);
                    });
            	}
            	this._plugins.forEach(function(plugin){//JCH so that the toolbar does not disappear below the browser toolbar when the editor is in full screen mode, and triggered from a TooltipDialog
            		if (plugin.button){
            			plugin.button.scrollOnFocus = false;
	            	}
            	});
            }));
            this.statusBar.resizeHandle.on ('resize', lang.hitch(this, function(evt){
                var newHeight = this.height = domStyle.get(this.editingArea, 'height') + "px";
            	lang.setObject((this.itemCustomization || 'customization') + '.widgetsDescription.' + this.widgetName + '.atts.height', newHeight, this.pane);
            }));
        },
        onLoad: function(html){
        	this.inherited(arguments);
            this.onLoadDeferred.then(lang.hitch(this, function(){
            	this.editNode.className = 'claro';
            }));
        },

        destroy: function(){
			array.forEach(this._plugins, function(p){
				if(p && p.destroy){
					p.destroy();
				}
			});
			this._plugins = [];
			if (this.toolbar){
                this.toolbar.destroyRecursive();
                delete this.toolbar;
            }else{
            }
            RichText.prototype.destroy.apply(this, arguments);
        },
		pasteHtmlAtCaret: function(html, selectPastedContent) {// found here: http://stackoverflow.com/questions/6690752/insert-html-at-caret-in-a-contenteditable-div/6691294#6691294
            var sel = dijit.range.getSelection(this.window), range = sel.getRangeAt(0);
            this.endEdit();
            this.begEdit();
            range.deleteContents();
            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            var firstNode = frag.firstChild;
            range.insertNode(frag);

            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                if (selectPastedContent) {
                    range.setStartBefore(firstNode);
                } else {
                    range.collapse(true);
                }
                sel.removeAllRanges();
                sel.addRange(range);
            }
            this.endEdit();
            this.begEdit();
        },
        pasteAndRefresh: function(htmlFragment){
        	var eSelection = this.selection, selectedClass = 'tukosLastSelected';
        	this.pasteHtmlAtCaret(htmlFragment, true);
			var pastedNode = eSelection.getSelectedElement();
            dcl.add(pastedNode, selectedClass);	
            this.set('value', this.get('value'));
            Array.apply(null, this.document.getElementsByClassName(selectedClass)).forEach(function(node){
    			var placeHolders = Array.apply(null, node.getElementsByClassName('tukosPlaceHolder'));
    			if (placeHolders.length > 0){
    				eSelection.selectElement(placeHolders[0]);
    			}else{
    				eSelection.selectElement(node);
    				eSelection.collapse();
    			}
            	dcl.remove(node, selectedClass);
            	if (!node.getAttribute('class')){
            		node.removeAttribute('class');
            	}
            });
        }
    }); 
});
