define (["dojo/_base/lang", "dojo/dom-class", "dojo/dom-attr", "dojo/keys", "dojo/string", "tukos/utils",  "tukos/evalutils"], 
    function(lang, dcl, domAttr, keys, string, utils, eutils){
	var expressionReferenceRegExp = /(\b)([a-z][a-z0-9]*)![$]?([A-Z]+)[$]?(\d+)|(\b)([a-z][\w]*)(\b[^.(]|$)|[$]?([A-Z]+)[$]?([0-9]+)/g,
		expressionNameRegExp = /(\w*)!?([A-Z]*)(\d*)/,
		cellReferenceRegExp = /([^\w])([$]?)([A-Z]+)([$]?)(\d+)\b/g,
		expressionReferenceRegExpTemplate = '(\\b)(${name})![$]?(${col})[$]?(${row})|(\\b)(${name})(\\b[^.(]|$)|[$]?(${col})[$]?(${row})\\b',
    	expressionTemplate =
    		'<div class="tukosExpression" id="e_${name}" style="display: inline;" title="${name}" onclick="parent.tukos.onExpClick(this);">${visualPreTag}' +
    		'<textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">${formula} </textarea>' +
    		'<span style="display: inline;">${value}</span>${visualPostTag}</div>',
    	lastKeyDown;
	return {
    	template: function(){
    		return expressionTemplate;
    	},
		eval: function(formulaExpression){
    		if (!formulaExpression.hasAttribute('data-formulaCache')){
    			try{
        			formulaExpression.setAttribute('data-formulaCache', eutils.eval(this.parseFormula(formulaExpression)));
        			this.refreshReferencingFormulaes(formulaExpression);    				
    			}
    			catch(error){
    				formulaExpression.setAttribute('data-formulaCache', '%error: ' + error);
    			}
    		}
			return formulaExpression.getAttribute('data-formulaCache');
    	},
        parseFormula: function(formulaExpression){
            if (formulaExpression.getAttribute('data-formulaCache') ===  '%inprocess%'){
                throw 'circular reference';
            }
            formulaExpression.setAttribute('data-formulaCache', '%inprocess%');
            return this.referencesToValues(formulaExpression);
        },
        referencesToValues: function(formulaExpression){
            var formula = this.formulaOf(formulaExpression).slice(1), expressionName = formulaExpression.id.slice(2).split('!')[0], doc = formulaExpression.ownerDocument, eval = lang.hitch(this, this.eval),
            	valueOf = lang.hitch(this, this.valueOf);
            formulaExpression.className = formulaExpression.className.replace(/\be_[^ ]*/g, '');
        	return formula.replace(expressionReferenceRegExp, lang.hitch(this, function(match, fPre, fName, fCol, fRow, pre, name, post, col, row){
        		var expressionId = 'e_' + (fName === undefined ? (name === undefined ? expressionName : name) : fName) + (fCol === undefined ? (col === undefined ? '' : '!' + col + row) : '!' + fCol + fRow), 
        			referencedExpression = doc.getElementById(expressionId);
                dcl.add(formulaExpression, expressionId);
        		return (this.isFormula(referencedExpression) ? eval(referencedExpression) : valueOf(referencedExpression)) + (post === undefined ? "" : post);
            }));
        },
    	refreshAllFormulaes: function(node){
    		var doc = node.ownerDocument, expressions = Array.apply(null, doc.getElementsByClassName('tukosFormula'));
    		this.refreshFormulaes(expressions);
    	},
    	refreshFormulaes: function(expressions){
    		expressions.forEach(function(expression){
    			expression.removeAttribute('data-formulaCache');
    		});
    		expressions.forEach(lang.hitch(this, function(expression){
    			this.span(expression).innerHTML = this.eval(expression);
    		}));    		
    	},
    	refreshReferencingFormulaes: function(expression){
    		this.refreshFormulaes(this.referencingFormulaes(expression));
    	},
    	referencingFormulaes: function(expression){
    		return Array.apply(null, expression.ownerDocument.getElementsByClassName(expression.id));
    	},
    	textArea: function(expression){
    		var children = expression.children;
    		return children.length === 4 ? expression.children[1] : expression.children[0];
    	},
    	span: function(expression){
    		var children = expression.children;
    		return children.length === 4 ? expression.children[2] : expression.children[1];
    	},
    	setValue: function(expression, value){
    		var textArea = this.textArea(expression), span = this.span(expression);
    		textArea.innerHTML = textArea.value = '';
    		expression.removeAttribute('data-formulaCache');
			dcl.toggle(expression, 'tukosFormula', false);
            expression.className = expression.className.replace(/\be_[^ ]*/g, '');
			span.innerHTML = value;
    		this.refreshReferencingFormulaes(expression);
    	},
    	setFormula: function(expression, formula){
    		var textArea = this.textArea(expression), span = this.span(expression);
    		expression.removeAttribute('data-formulaCache');
			dcl.toggle(expression, 'tukosFormula', true);
			textArea.innerHTML = textArea.value = formula;
			span.innerHTML = this.eval(expression);		
    	},
    	setExpression: function(expression, content){
    		var textArea = this.textArea(expression), span = this.span(expression);
    		if (content.formula){
				textArea.innerHTML = textArea.value = content.formula;
				dcl.toggle(expression, 'tukosFormula', true)
    			if (content.value){
    				expression.setAttribute('data-formulaCache', content.value);
    				span.innerHTML = content.value;
    			}else{
    				expression.removeAttribute('data-formulaCache');
    				span.innerHTML = this.eval(expression);
    			}
    		}else{
    			this.setValue(expression, content.value);
    		}
    	},
    	copyExpression: function(source, target, rowOffset, colOffset){
    		if (this.isFormula(source)){
    			var formula = this.formulaOf(source), targetFormula = formula;
    			if (rowOffset || colOffset){
        			targetFormula = formula.replace(cellReferenceRegExp, function(match, pre, colDollar, col, rowDollar, row){
        				return pre + colDollar + (colDollar === '$' || colOffset === 0 ? col : utils.alphabet(utils.fromAlphabet(col)+colOffset)) + rowDollar + (rowDollar === '$' ? row : parseInt(row) + rowOffset);
        			});
    			}
    			this.setFormula(target, targetFormula);
    		}else{
    			this.setValue(target, this.valueOf(source));
    		}
    	},
    	valueOf: function(expression){
    		return this.span(expression).innerHTML;
    	},
    	formulaOf: function(expression){
    		return this.textArea(expression).innerHTML;//.trim();
    	},
        isExpression: function(node){
        	return node && dcl.contains(node, 'tukosExpression');
        },
        isFormula: function(expression){
        	return dcl.contains(expression, 'tukosFormula');
        },
        nameObject: function(expression){
        	var nameAndCellReference = expression.title, expressionNameObject = {name: nameAndCellReference[0], col: nameAndCellReference[1] || 0}
        },
        setName: function(expression, newNameObject, updateReferencingFormulaes){
        	var currentNameArray = expression.title.match(expressionNameRegExp), regExp = new RegExp(string.substitute(expressionReferenceRegExpTemplate, {name: currentNameArray[1], col: currentNameArray[2], row: currentNameArray[3]}), 'g'),
    		newName = (newNameObject.name ? newNameObject.name : expression.id.slide(2).split('!')[0]) + (newNameObject.col ? '!' + newNameObject.col + newNameObject.row : ''), newId = 'e_' + newName;
        	if (updateReferencingFormulaes){
        		var referencingFormulaes = this.referencingFormulaes(expression), textArea;
            	referencingFormulaes.forEach(function(referencingFormula){
            		textArea = this.textArea(referencingFormula);
            		textArea.innerHTML = textArea.innerHTML.replace(
            				regExp, 
            				function(match, fPre, fName, fCol, fRow, pre, name, postName, col, row){
            					dcl.replace(referencingFormula, newId, expression.id);
            					return  (fName === undefined && name === undefined ? '' : newNameObject.name) + 
            							fCol === undefined  && col === undefined 
            									? postName === undefined ? '' : postName
            									: fCol === undefined
        											? newNameObject.col + newNameObject.row
            										: '!' + newNameObject.col + newNameObject.row;
            		});
            	});
        	}
        	domAttr.set(expression, {id: newId, title: newName});
        },
    	onBlur: function(textArea){
    		console.log('entering expressions.onBlur');
    		var expression = textArea.parentNode, span = this.span(expression), newValue = textArea.value, oldValue = textArea.oldValue;//textArea.innerHTML ||span.innerHTML;
    		if (lastKeyDown !== keys.ESCAPE/* && newValue !== oldValue*/){
	    		if ((newValue || ' ').charAt(0) === '='){
	    			this.setFormula(expression, newValue)
	    		}else{
	    			this.setValue(expression, newValue);
	    		}
    		}
    		lastKeyDown = undefined;
    		span.style.display = 'inline';
    		textArea.style.display = 'none';
    	},
    	onClick: function(expression, stringToAppend){
    		console.log('is onclick');
    		var textArea = this.textArea(expression), span = this.span(expression);
    		textArea.value = textArea.oldValue = textArea.innerHTML.trim() || span.innerHTML;
    		textArea.style.display = 'inline';
    		span.style.display = 'none';
    		if (stringToAppend){
    			textArea.value += stringToAppend;
    		}
    		textArea.focus();
    	},
    	checkLastKeyDown: function(evt, keyCode, editor){
    		var target = evt.target, parent = target.parentNode;
    		console.log('checkLastKeyDown - keyCode: ' + keyCode + ' evt.target.tagName: ' + target.tagName);
    		if (this.isExpression(parent)){
    			lastKeyDown = keyCode;
    			if (keyCode === keys.ENTER){
    				evt.preventDefault();
    				editor.endEdit();
    			}
    			editor.selectElement(editor.selection.getParentOfType(parent, ['TD']) || parent);
    		}else{
    			var selectedElement = editor.selection.getSelectedElement(), selectedElementChild;
    			if(selectedElement && this.isExpression(selectedElementChild = selectedElement.children[0])){
        			evt.preventDefault();
        			editor.begEdit();
        			this.onClick(selectedElementChild);
        		}else{
        			if (keyCode === keys.ESCAPE){
        				editor.selectElement(editor.selection.getParentElement());
        			}
        		}
    		}
    	}
    }
});
