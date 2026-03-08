define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-class", "dojo/ready", "dojo/when", "dojo/string", "dojo/keys", "dijit/_editor/_Plugin", "dijit/form/DropDownButton", "dijit/layout/ContentPane",
	"dijit/registry", "dijit/popup", "tukos/TukosTooltipDialog", "tukos/utils", "tukos/hiutils", "tukos/PageManager", 	"dojo/i18n!dojox/editor/plugins/nls/latinEntities"
], 
function(declare, lang, dct, dcl, ready, when, string, keys, _Plugin, Button, ContentPane, registry, popup, TooltipDialog, utils, hiutils, Pmg) {
	var textTags = ['mi','mo', 'mn', 'mtext'], openingParenthesis = {')': '(', ']': '[', '}': '{', '|': '|'},
		isTextTag = function(tag){
			return textTags.indexOf(tag) > -1;
		},
		placeHolderTag = '<mtext class="tukosPlaceHolder">??</mtext>',
		htmlTagTemplate =  "<${tagName}${atts}>${innerHTML}</${tagName}>",
		htmlTag = function(tagName, atts, innerHTML){
			return string.substitute(htmlTagTemplate, {tagName: tagName, atts: atts ? (' ' + atts) : '', innerHTML: innerHTML || placeHolderTag});
		},
		htmlTagN = function(tagName, n, atts, innerHTML){
			var tags = '';
			for (var i = 1; i<= n; i++){
				tags += htmlTag(tagName, atts, innerHTML);
			}
			return tags;
		},
		html = function(html, key, label){
			return {s: html, k: typeof key === 'string' ? (key.length === 1 ?[key, false, false, true] : []) : [key.chr, (key.ctrl === undefined ? false : key.ctrl), (key.shift === undefined ? false : key.shift), (key.alt === undefined ? true : key.alt)],
					label: label || html};
		},
		tag = function(tagName, key, atts, innerHTML, label){
			var htmlFragment = htmlTag(tagName, atts, innerHTML);
			return html(htmlFragment, key, label || '<math display="inline">' + htmlFragment + '</math>');
		},
		tagN = function(tagName, n, key, atts, innerHTML, label){
			return tag(tagName, key, atts, htmlTagN('mrow', n, atts, innerHTML), label);
		},
		mfenced = function(open, close, key, atts, innerHTML, label){
			//return tag('mfenced', key || {chr: open}, string.substitute("open='${open}' close='${close}'", {open: open, close: close}) + (atts ? (' ' + atts) : ''), htmlTag('mrow', null, innerHTML));
			if (open){
				return tag('mrow', key || {chr: open}, atts, htmlTag('mo', null, open) + placeHolderTag + (close ? htmlTag('mo', null, close) : ''));
			}else{
				return tag('mo', {chr: close}, atts, close, label);
			}
		},
		/*mfrac = function(key, atts, innerHTML, label){
			return tag('mfrac', key || 'f', atts, htmlTag('mrow', null, innerHTML) + htmlTag('mrow', null, innerHTML), label);
		},*/
		modownup = function(tagName, operator, key, atts, innerHTML, label){
			return html(htmlTag(tagName, null, '<mo>' + operator + '</mo>' + htmlTagN('mrow', 2)) + htmlTag('mrow'), key, atts, innerHTML, label);
		},
		/*midownup = function(tagName, identifier, key, atts, innerHTML, label){
			return html(htmlTag(tagName, null, '<mi>' + identifier + '</mi>' + htmlTagN('mrow', 2)) + htmlTag('mrow'), key, atts, innerHTML, label);
		},*/
		braket = function(key, atts, innerHTML, label){
			return mfenced('&langle;', '&rangle;', key || 'b', null, htmlTag('mrow', null, innerHTML) + '<mo>&verbar;</mo>' + htmlTag('mrow', null, innerHTML), label);
		},
		table = function(rows, columns, key, atts, innerHTML, label){
			var tableInnerHTML = '', rowInnerHTML;
			for (var r = 1; r <= rows; r++){
				rowInnerHTML = '';
				for (var c = 1; c <= columns; c++){
					rowInnerHTML += htmlTag('mtd', null);
				}
				tableInnerHTML += htmlTag('mtr', null, rowInnerHTML);
			}
			return tag('mtable', key, atts, tableInnerHTML, label);
		};
	var mls = {
			block: tag('math', {chr: 'b', ctrl: true, shift: true, alt: false}, "display='block'", placeHolderTag, 'block'),
			inline: tag('math', {chr: 'l', ctrl: true, shift: true, alt: false}, "display='inline'", placeHolderTag, 'inline'),
			mrow: tag('mrow', 'r', null, placeHolderTag, 'mrow'), mi: tag('mi', 'i', null, placeHolderTag, 'mi'), mo: tag('mo', 'o', null, placeHolderTag, 'mo'), mn: tag('mn', 'n', null, placeHolderTag, 'mn'), mtext: tag('mtext', 't', null, placeHolderTag, 'mtext'),
			mtable: tag('mtable', {chr: 't', ctrl: true, shift: true, alt: false}, null, htmlTag('mtr'), 'mtable'),
			mtr: tag('mtr', {chr: 'r', ctrl: true, shift: true, alt: false}, null, htmlTag('mtd'), 'mtr'),
			mtd: tag('mtd', {chr: 'd', ctrl: true, shift: true, alt: false}, null, placeHolderTag, 'mtd'),
			parentheses:  mfenced('(', ')'), sqBrackets: mfenced('[', ']'), clBrackets: mfenced('{', '}'), leftClBrackets: mfenced('{', ''), doubleVertBrackets: mfenced('&Verbar;', '&Verbar;', {chr: '|', shift: true}),
			angleBrackets: mfenced('&langle;', '&rangle;', {chr: '<', ctrl: true}), ket: mfenced('&verbar;', '&rangle;',{chr: '<', shift: true}), bra: mfenced('&langle;', '&verbar;', '<'), braket: braket(),
			frac: tagN('mfrac', 2, 'f'), msup: tagN('msup', 2, {chr: 's', shift: true}), msub: tagN('msub', 2, 's'), mover: tagN('mover', 2, {chr: 'o', shift: true}), sqrt: tagN('msqrt', 1, {chr: 'r', shift: true}),
			msubsup: tagN('msubsup', 3, {chr: 's', ctrl: true, shift: true}), 'int': modownup('msubsup', '&int;', {chr: 'i', shift: true}),
			sum: modownup('msubsup', '&sum;', 'z'),
			table21: table(2, 1, {chr: '1', shift: true}, "rowalign='center'"), table22: table(2, 2, {chr: '2', shift: true}, "rowalign='center'"),
			hamilt: tag("mi", {chr: 'h'}, null, "&hamilt;"), lagran: tag("mi", {chr: 'l'}, null, "&lagran;"), planckh: tag("mi", {chr: 'h'}, null, "&planckh;"), hbar: tag("mi", {chr: 'h', shift: true}, null, "&hbar;"),
			reals: tag("mi", {chr: 'r'}, null, "&reals;"), rationals: tag("mi", {chr: 'q'}, null, "&rationals;"), naturals: tag("mi", {chr: 'n'}, null, "&naturals;"),
			part: tag("mi", 'p', null, '&part;'), kro: tag("mi", {chr: 'p', shift: true}, null, '&delta;'),thinsp: tag('mi', {chr: ' ', shift: true, alt: false}, null, '&thinsp;', 'thinsp')
		},
		symbols = [['block', 'inline'],['mrow', 'mi', 'mo', 'mn', 'mtext', 'mtable', 'mtr', 'mtd'], ['parentheses', 'sqBrackets', 'clBrackets', 'leftClBrackets', 'doubleVertBrackets', 'angleBrackets', 'ket', 'bra', 'braket'], 
					['frac', 'msup', 'msub', 'msubsup', 'mover', 'sum', 'int', 'sqrt'], ['table21', 'table22'], ['reals', 'rationals', 'naturals'], ['hamilt', 'lagran', 'planckh', 'hbar', 'part', 'kro', 'thinsp']];

    var MathMLEdit = declare([_Plugin], {
        setKeyHandlers: function(){
        	var editor = this.editor, self = this;
    		editor.addKeyHandler("c", true, true, lang.hitch(this, function(){
    			this.button.openDropDown();
    			this.button.dropDown.focus();
    		}));
    		editor.addKeyHandler("e", false, false, lang.hitch(this, function(){
    			var symbolsPalette = this.editor.symbolsPalette;
    			if (!this.button.dropDown){
    				this.button.loadAndOpenDropDown();
    			}
				if (this.button._popupStateNode.popupActive){
					popup.close(symbolsPalette);
					this.button._popupStateNode.popupActive = false;
				}else{
					popup.open({parent: this.button, popup: symbolsPalette, around: this.button.domNode, 
						/*onExecute: function(){
							popup.close(symbolsPalette);
						}, */
						onCancel: function(){
							popup.close(symbolsPalette);
							this.button._popupStateNode.popupActive = false;
						}
					});
					this.button._popupStateNode.popupActive = true;
				}
    		}), true);
    		editor.addKeyHandler(keys.RIGHT_ARROW, false, false, lang.hitch(this, function(){
    			var eSelection = editor.selection, wSelection = editor.document.getSelection(), parentNode = wSelection.anchorNode.parentNode;
    			if (wSelection.type === 'Caret'){
        			eSelection.selectElement(parentNode);
    			}
    			eSelection.collapse();
    		}), true);
    		editor.addKeyHandler(keys.LEFT_ARROW, false, false, lang.hitch(this, function(){
    			var eSelection = editor.selection, wSelection = editor.document.getSelection();
    			if (wSelection.type === 'Caret'){
    				eSelection.selectElement(wSelection.anchorNode.parentNode);
    			}
    			eSelection.collapse(true);
    		}), true);
        	utils.forEach(mls, function(ml){
        		editor.addKeyHandler(ml.k[0], ml.k[1], ml.k[2], lang.hitch(self, self._insert, ml.s), ml.k[3]);
        	});
        },
        shortCutLabel: function(k){
        	return k.length == 0 ? Pmg.message('noShortCut') : ((k[1] ? 'Ctrl+' : '') + (k[2] ? 'Shift+' : '') + (k[3] ? 'Alt+' : '') + k[0].toUpperCase());
        },
        buildDropDownItems: function(items){
        	var description = [], self = this, insert = lang.hitch(this, this._insert);
        	items.forEach(function(item){
        		var ml = mls[item];
        		description.push({atts: {label: Pmg.message(item) + '  ' + self.shortCutLabel(ml.k), onClick: function(){insert(ml.s);}}});
        	});
        	return description;
        },
	    buildSymbolsPalette: function(items){
	    	var self = this, insert = lang.hitch(this, this._insert), pane = new ContentPane({style: {backgroundColor: 'white'}}), div = dct.create('div', null, pane.domNode), table = dct.create('table', {'class': 'dijitPaletteTable'}, div);
	    	items.forEach(function(row){
	    		var tr = dct.create('tr', null, table);
	    		row.forEach(function(item){
	    			var ml = mls[item];
	    			dct.create('td', {"class": "dojoxEntityPaletteCell", style: {width: 'auto'}, onclick: function(){insert(ml.s);self.button.closeDropDown();}, innerHTML: ml.label || ml.s, title: self.shortCutLabel(ml.k)}, tr);
	    		});
	    	});
			table = dct.create('table', {'class': 'dijitPaletteTable'}, div);
			const maxCols = 21;
			let  colCount = maxCols, tr;
			for (let i = 119964; i <= 120067; i++){
				if (colCount === maxCols){
					tr = dct.create('tr', null, table);
					colCount = 1;
				}
				const entityHtml =  '<mi>&#' + i + ';</mi>';
				dct.create('td', {"class": "dojoxEntityPaletteCell", style: {width: 'auto'}, onclick: function(){
					insert(entityHtml);}, innerHTML: entityHtml, title: 'decimal ' + i}, tr);
				colCount += 1;
			}
			/*var tr = dct.create('tr', null, table), hexArray = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
			hexArray.forEach(function(code){
				const entityHtml = '&#' + Number('0x1D4D' + code) + ';'
				dct.create('td', {"class": "dojoxEntityPaletteCell", style: {width: 'auto'}, onclick: function(){insert(entityHtml);}, innerHTML: entityHtml, title: 'cursive' + code}, tr);
			});
			tr = dct.create('tr', null, table), hexArray = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
			hexArray.forEach(function(code){
				const entityHtml = '&#' + Number('0x1D4E' + code) + ';'
				dct.create('td', {"class": "dojoxEntityPaletteCell", style: {width: 'auto'}, onclick: function(){insert(entityHtml);}, innerHTML: entityHtml, title: 'cursive' + code}, tr);
			});*/
			table = dct.create('table', {'class': 'dijitPaletteTable'}, div);
			const entities = Object.getPrototypeOf(dojo.i18n.getLocalization("dojox.editor.plugins", "latinEntities")), numberOfEntities = Object.keys(entities).length, entitiesPerRow = 21;//Math.floor(Math.sqrt(numberOfEntities));
			let currentCol = 0, currentEntityIndex = 0;
			for (let entity in entities){
				if (currentCol > entitiesPerRow){
					currentCol = 0;
					tr = dct.create('tr', null, table);
				}
				currentCol +=1;
				currentEntityIndex +=1;
				if (currentEntityIndex === numberOfEntities){// to eliminate &locale;
					continue;
				}
				if (currentCol === 1){
					tr = dct.create('tr', null, table);
				}
				let entityHtml = '<mi>&' + entity + ';</i>';
				dct.create('td', {"class": "dojoxEntityPaletteCell", style: {width: 'auto'}, onclick: function(){insert(entityHtml);}, innerHTML: entityHtml, title: entities[entity]}, tr);
			}
	    	return pane;
	    },
        iconClassPrefix: "dijitAdditionalEditorIcon",
        _initButton: function(){
            this.button = new Button({
                label: Pmg.message("MathMLEdit"),
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "MathMLEdit",
                tabIndex: "-1",
                loadDropDown: lang.hitch(this, "_loadDropDown")
            });
        	this.setKeyHandlers();
            this.editor.handleMathML = lang.hitch(this, this.handleMathML);
        	this.editor.selectNextMathPlaceHolder = lang.hitch(this, this.selectNextMathPlaceHolder);
			symbols.push();
			this.editor.symbolsPalette = this.buildSymbolsPalette(symbols);
			this.editor.symbolsPalette.parent = this.button;
        },
        updateState: function(){
            this.button.set("disabled", this.get("disabled"));
        },
        setEditor: function(editor){
            this.editor = editor;
            this._initButton();
        },
        _loadDropDown: function(callback){
			this.button.dropDown = this.editor.symbolsPalette;
			ready(callback);
        },
        handleMathML: function(e){
        	var key = e.key, handled = false;
        	if (e.ctrlKey || e.altKey || e.metaKey){
        		return;
        	}else{
        		var editor = this.editor, eSelection = editor.selection, wSelection = editor.document.getSelection(), anchorNode = wSelection.anchorNode, parent = eSelection.getParentElement(anchorNode), enclosingTagName = parent.tagName,
        			_insert = lang.hitch(this, this._insert);
        			insert = function(key, tagName){
        				if (isTextTag(tagName) && enclosingTagName !== tagName){
        					_insert(htmlTag(tagName, null, key));
        					eSelection.selectElementChildren(eSelection.getSelectedElement());
							eSelection.collapse();
        					handled = true;
        				}
        			},
        			openFence = function(key){
        				_insert(mfenced(key, '').s);
        				handled = true;
        			},
        			closeParentFence = function(key){
        				var parentNode = parent;
        				while(parentNode){
        					if(parentNode.tagName === 'mrow' && (parentNode.firstChild ||{}).tagName === 'mo' && parentNode.firstChild.textContent === openingParenthesis[key] && 
        						(parentNode.childNodes.length === 1 || parentNode.lastChild.tagName !== 'mo' || parentNode.lastChild.textContent !== key)){
        						dct.create('mo', {innerHTML: key}, parentNode, 'last');
        						eSelection.selectElement(parentNode);
        						eSelection.collapse();
        						handled = true;
        						return true;
        					}
        					parentNode = parentNode.parentNode;
        				}
    					return false;
        			},
        			closeFence = function(key){
    					if (!closeParentFence(key)){
            				_insert(mfenced('', key).s);
            				handled = true;
    					}
        			},
					openOrCloseFence = function(key){
    					if (!closeParentFence(key)){
            				_insert(mfenced(key, '').s);
            				handled = true;
    					}
					};
        		if (eSelection.hasAncestorElement('math') && enclosingTagName !== 'mtext'){
                	if (key.length === 1){
                    	if (key >= '0' && key <= '9'){
                    		insert(key, 'mn');
                    	}else if ((key >= 'A' && key <= 'Z') || (key >= 'a' && key <= 'z')){
                    		insert(key, 'mi');
                    	}else if('([{'.indexOf(key) > -1){
                    		openFence(key);
                    	}else if (')]}'.indexOf(key) > -1){
                    		closeFence(key);
                    	}else{
                    		switch (key){
                    			case '|': openOrCloseFence(key); break;
                    			case ' ': /*insert('&nbsp;', 'mtext'); */break;
                    			default: insert(key, 'mo');
                    		}
                    	}
                	}else{
                		console.log('key: ' + key + ' has more than one character');
                	}
                	if (handled){
                		e.preventDefault();
                	}
        		}
        	}
        },
        _insert: function(htmlFragment){
            var isTag = (htmlFragment[0] === '<'), editor = this.editor;
            if (isTag){
            	var tagName = htmlFragment.match(/^<([^ >]*)[ >]/)[1], eSelection = editor.selection, wSelection = editor.document.getSelection(), anchorNode = wSelection.anchorNode, parent,
            		isFullTextSelection = wSelection.anchorOffset === 0 && wSelection.focusOffset === wSelection.focusNode.length, selectedHtml,
            		isMath = function(){
            			if (eSelection.hasAncestorElement('math')){
            				return true;
            			}else{
                        	if (wSelection.type === 'Caret' && wSelection.anchorOffset === 0 && (anchorNode.previousSibling || {}).tagName === 'math'){
                        		eSelection.selectElementChildren(anchorNode.previousSibling);
                        		eSelection.collapse();
                        		wSelection = editor.document.getSelection();
                        		anchorNode = wSelection.anchorNode;
                        		isFullTextSelection = false;
                        		return true;
                        	}else{
                        		return false;
                        	}
            			}
            		};
                if (isMath()){
                	if (wSelection.type === 'Range'){
                		if ((selectedHtml = eSelection.getSelectedHtml()) !== '??'){
                			htmlFragment = htmlFragment.replace('<mtext class="tukosPlaceHolder">??</mtext>', selectedHtml);
                		}
                		eSelection.remove();
                		hiutils.removeEmptyDescendants(wSelection.anchorNode);
                	}
            		//if (['mi','mo', 'mn'/*, 'mfenced'*/, 'mtext'].indexOf((parent = eSelection.getParentElement(anchorNode)).tagName) > -1){
                	if (isTextTag((parent = eSelection.getParentElement(anchorNode)).tagName)){
        	            eSelection.selectElement(parent);
        	            if (isFullTextSelection){
        	            	eSelection.remove();
        	            }else{
        	            	eSelection.collapse();
        	            }
                	}
                	editor.pasteAndRefresh(htmlFragment);
                }else if(tagName === 'math'){
            		editor.pasteAndRefresh(htmlFragment);
            	}else{
            		Pmg.setFeedback(Pmg.message('notinmathtag'), null, null, true);
            	}
            }else{
            	editor.pasteHtmlAtCaret(htmlFragment);
            }
			editor.focus();
        },
        selectNextMathPlaceHolder: function(){
			var eSelection = this.editor.selection, mathNode = eSelection.getAncestorElement('math'), placeHolders;// = Array.apply(null, mathNode.getElementsByClassName('tukosPlaceHolder'));
			if (mathNode){
				if ((placeHolders = Array.apply(null, mathNode.getElementsByClassName('tukosPlaceHolder'))).length > 0){
					eSelection.selectElement(placeHolders[0]);
				}else{
					eSelection.selectElementChildren(mathNode);
					eSelection.collapse();
				}
			}else{
				Pmg.setFeedback(Pmg.message('nomoreplaceholders'), null, null, true);
			}
        },
        replacePlaceHolder: function(htmlFragment, replacingHtml){
        	return htmlFragment.replace('<mtext class="tukosPlaceHolder">??</mtext>', replacingHtml);
        },
	    close: function(){
	    	popup.close(this.button.dropDown);
	    }
    });
	_Plugin.registry['MathMLEdit'] = function(){return new MathMLEdit({});};
	
    return MathMLEdit;

});
