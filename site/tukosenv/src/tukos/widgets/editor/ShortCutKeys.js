/*
 *  loads plugins required by tukos & a few enhancements
 */
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/keys", "dojo/sniff", "dojo/dom-style", "tukos/expressions"], 
function(declare, lang, keys, has, domStyle, expressions){
    var cache = {}, modifyTableSelection; 
	
	return declare(null, {
	    shiftKeyDown: function(keyboardEvent){
	    	return keyboardEvent.shiftKey;
	    },
	    isCommandKey: function(keyboardEvent){
	    	return keyboardEvent.ctrlKey || keyboardEvent.altKey || keyboardEvent.metaKey;
	    },
    	addShortCutKeys: function(){
            this.addKeyHandler('c', false, false, lang.hitch(this, this.copySelection), true);
            this.addKeyHandler('v', false, false, lang.hitch(this, this.pasteCopiedSelection), true);
		}, 
    	prepareModifyTableSelection: function(callbackOrString){
    		if (!modifyTableSelection){
    			this._plugins.some(function(plugin){
    				if (plugin.name === "modifyTableSelection"){
    					modifyTableSelection = plugin;
    					return true;
    				}
    			})
    		}
    		if (modifyTableSelection.available){
    			modifyTableSelection.prepareTable();
    			this.begEdit();
    			lang.hitch(modifyTableSelection, typeof callbackOrString === 'string' ? modifyTableSelection[callbackOrString] : callbackOrString)();
    			this.endEdit();
    			return true;
    		}else{
    			return false;
    		}
    	},
    	copySelection: function(){
    		if (!this.prepareModifyTableSelection('copySelected')){
        		var selection = this.selection, element = selection.getSelectedElement();
        		cache = {backgroundColor: (element && (element.getAttribute('data-backgroundColor') !== null) ? domStyle.get(element, 'backgroundColor') : null), html: selection.getSelectedHtml()};
    		}
    		//console.log('new cache in copySelection: ' + cache.toString());
    	},
    	pasteCopiedSelection: function(){
    		if (!this.prepareModifyTableSelection('pasteAtSelected')){
        		//console.log('cache in pasteCache: ' + cache.toString());
        		if (typeof cache === 'object'){
        			var selection = this.selection, element = selection.getSelectedElement();
        			if (element && cache.backgroundColor !== null){
        				if (!element.getAttribute('data-backgroundColor')){
        					element.setAttribute('data-backgroundColor', domStyle.get(element, 'backgroundColor'));
        				}
        				domStyle.set(element, 'backgroundColor', cache.backgroundColor);
        			}
        			//this.execCommand('inserthtml', cache.html);
        			//this.onDisplayChanged();
        			this.pasteAndRefresh(cache.html);
        		}
    		}
    	}, 
    	addKeyHandler: function(/*String|Number*/ key, /*Boolean*/ ctrl, /*Boolean*/ shift, /*Function*/ handler, alt){
			//override RichText to support ALt key shortcut 

			if(typeof key == "string"){
				// Something like Ctrl-B.  Since using keydown event, we need to convert string to a number.
				key = key.toUpperCase().charCodeAt(0);
			}

			if(!lang.isArray(this._keyHandlers[key])){
				this._keyHandlers[key] = [];
			}

			this._keyHandlers[key].push({
				shift: shift || false,
				ctrl: ctrl || false,
				alt: alt || false,
				handler: handler
			});
		},
		onKeyDown: function(/* Event */ e){
			// override RichText to support Alt key
			var keyCode = e.keyCode, key = e.key, ancestorContext = (this.selection.getAncestorElement('math', 'table') || {tagName: ''}).tagName.toLowerCase(), handled;
			console.log('ShortCutKeys - keyCode: ' + keyCode + ' key: ' + e.key);
			switch(keyCode){
				case keys.SHIFT:
				case keys.ALT: //e.preventDefault();
				case keys.META:
				case keys.CTRL: return true;
				case keys.TAB:
					if (!e.ctrlKey && !e.altkey){
						switch (ancestorContext){
							case 'math':
								this.selectNextMathPlaceHolder(); break;
							case 'table':
								this.previousOrNextCell(e); break;
							default:
								if (this.isTabIndent){
									if(this.queryCommandEnabled((e.shiftKey ? "outdent" : "indent"))){
										this.execCommand((e.shiftKey ? "outdent" : "indent"));
									}
								}else if(e.shiftKey){
									// focus the <iframe> so the browser will shift-tab away from it instead
									this.beforeIframeNode.focus();
								}else{
									// focus node after the <iframe> so the browser will tab away from it instead
									this.afterIframeNode.focus();
								}
						}
						handled = true;
					}
					break;
				case keys.BACKSPACE:
					this.execCommand("delete");
					handle = true;
					break;
				case keys.PAGE_UP:
				case keys.PAGE_DOWN:
					if (has("ff")){
						if(this.editNode.clientHeight >= this.editNode.scrollHeight){
							// Stop the event to prevent firefox from trapping the cursor when there is no scroll bar.
							e.preventDefault();
						}						
					}
					break;
	        	case keys.RIGHT_ARROW:
					if (!this.isCommandKey(e) && ancestorCOntext === 'table'){
		  			  handled = this.rightOrLeftCell(e, true);
					}
		  			 break;
	        	case keys.LEFT_ARROW:
					if (!this.isCommandKey(e) && ancestorCOntext === 'table'){
		  			  	handled = this.rightOrLeftCell(e, false);
					}
		  			break;
	        	case keys.DOWN_ARROW:
					if (!this.isCommandKey(e) && ancestorCOntext === 'table'){
						handled = this.upOrDownCell(e, true);
					}
		  			break;
	        	case keys.UP_ARROW:
					if (!this.isCommandKey(e) && ancestorCOntext === 'table'){
						handled = this.upOrDownCell(e, false);
					}
		  			break;
				case keys.ESCAPE:
				case keys.ENTER:
					expressions.checkLastKeyDown(e, keyCode, this);
					break;
				default: 
					console.log('ShortCutKeys - keyCode: ' + keyCode);
				switch(ancestorContext){
					case 'math':
						this.handleMathML(e); break;
					case 'table':
						this.onExpressionCellFocus(e);
						break;
				}	
			}
			if (handled){
				e.preventDefault();
				e.stopPropagation();
			}
			var handlers = this._keyHandlers[keyCode], args = arguments;
			if(handlers){
				handlers.some(function(h){
					// treat meta- same as ctrl-, for benefit of mac users
					if(!(h.shift ^ e.shiftKey) && !(h.ctrl ^ (e.ctrlKey || e.metaKey)) && !(h.alt ^ e.altKey)){
						if(!h.handler.apply(this, args)){
							e.preventDefault();
						}
						e.stopPropagation();
						e.preventDefault();
						return true;
					}
				}, this);
			}

			// function call after the character has been inserted
			this.defer("onKeyPressed", 1);

			return true;
		}
    }); 
});
