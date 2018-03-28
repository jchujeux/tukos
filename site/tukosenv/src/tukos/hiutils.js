define(["dojo/_base/lang", "dojo/dom-construct",  "dojo/dom-style", "dojo/string", "dojo/when", "dojo/promise/all", "dijit/Menu", "dijit/MenuItem", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
            function(lang, dct, dstyle, string, when, all, Menu, MenuItem, utils, wutils, Pmg, messages){
      var separator = '|';
      return {
      
    	  trimExt: function(string){
    		  return string.replace(/^[\s(&nbsp;)]+/g,'').replace(/[\s(&nbsp;)]+$/g,'');
    	  },
    	  contextMenu: function(menuArgs, itemsArgs){
            var theMenu = new Menu(menuArgs);
            for (var i in itemsArgs){
                theMenu.addChild(new MenuItem(itemsArgs[i]));
            }
            return theMenu;
        },
        
        create: function(description, atNode){
            if (description){
                if (description.tag){
                    var result = dct.create(description.tag, description.atts || null, atNode);
                    this.create(description.children, result);
                }else if (description.node){
                    atNode.appendChild(description.node);
                }else if (typeof description === "object"){
                    for (var item in description){
                        this.create(description[item], atNode);
                    }
                }
                return result;
            }else{
                return undefined;
            }
        },
        
        objectTable: function(object, hasCheckboxes, selectedLeaves, atts){
            var checkboxPath = [],
                    keyToHtml = lang.hitch(this, function(key){
                        return (atts.keyToHtml) ?  this[atts.keyToHtml](key) : key;
                    });
            var addTableRows = function(object){
                var rowToReturn = {count: 0};
                for (var key in object){
                    checkboxPath.push(key);
                    if (typeof object[key] === 'object'){
                        var row = addTableRows(object[key]), rowCount = row.count;
                        if (rowCount > 0){
                            var keyTd = dct.create('td', {innerHTML: keyToHtml(key), style: "border: 1px solid black; padding: 5px; vertical-align:top;", rowspan: rowCount});
                            row.tr.insertBefore(keyTd, row.tr.firstChild);
                            rowToReturn.count += rowCount;
                        }
                    }else{
                        var row = {tr:dct.create('tr', null, table)};
                        var keyTd   = dct.create('td', {innerHTML: keyToHtml(key), style: "border: 1px solid black;padding: 5px; vertical-align:top;"} , row.tr);
                        var valueTd = dct.create('td', {innerHTML: object[key], style: "border: 1px solid black;padding: 5px; vertical-align:top;font-style: italic"}, row.tr);
                        if (hasCheckboxes){
                            var stringPath = checkboxPath.join('.');
                            var checkboxTd = dct.create('td', {style: "border: 1px solid black;padding: 5px;"}, row.tr);
                            var checkbox = dct.create(
                                'input', 
                                {type: 'checkbox', onclick: lang.partial(
                                        function(stringPath, key, evt){
                                            lang.setObject(stringPath, this.checked, selectedLeaves);
                                        },
                                        stringPath,
                                        key
                                    )
                                },
                                checkboxTd
                            );
                        }
                        rowToReturn.count += 1;
                    }
                    if (rowToReturn.tr === undefined){
                        rowToReturn.tr = row.tr;
                    }
                    checkboxPath.pop(key);
                }
                return rowToReturn;
            }
            var table = dct.create('table', {style: "border: 1px solid black; border-collapse: collapse; background-color: lightblue;"});
            addTableRows(object, selectedLeaves);
            return table;  
        },

        getSelectedSpan: function(selectedHtml){
            var result = /<span .*id="([2-9a-z]*Span)/g.exec(selectedHtml);
            return (result && result[1]) || undefined;
        },

        processTemplate: function(content, panes){
            //console.log('calling processTemplate: ' + content);
        	if (content){
            	return when(content, lang.hitch(this, function(content){
	                try {
	                	var newContent = this._inProcess(content, ['checkboxTemplate'], ['menuTemplate',  'visualTag', 'colorContentTemplate', 'choiceListTemplate']);
	                    if (newContent){
		                	return when(this.substituteParams(newContent, panes), lang.hitch(this, function(newNewContent){
		                        var newContent = this._inProcess(newNewContent, ['autocheckbox']);
		                		if (newContent === content){
		                            return newContent;
		                        }else{
		                            return this.processTemplate(newContent, panes);
		                        }
		                    }));
	                    }else{
	                    	return newContent;
	                    }
		            }catch(err){
		                return messages.errorprocessingtemplate + ': ' + err + '<br >' + content;
		            }
		        }));
	        }else{
	        	return content;
	        }
        },
/*        
        menuTemplateNode: function(node){
            node.parentNode.removeChild(node);
        },
*/        
        checkboxTemplateNode: function(node){
            if (!node.childNodes[0].innerText){
            	throw messages.errorcheckboxmalformed;
            }else if (node.childNodes[0].innerText.trim() === "☑"){
                lang.hitch(this, this.removeCheckbox)(node);
            }else{
                var nestedCheckboxes = Array.apply(null, node.getElementsByClassName('checkboxTemplate'));
                nestedCheckboxes.forEach(function(nestedElement){
                    checkboxedNodes.some(function(node, i){
                        if (node === nestedElement){
                            delete checkboxedNodes[i];
                            return true;
                        }
                    });
                });
                this.removeCheckboxAndNode(node);
            }
        	
        }, 
/*
        choiceListTemplateNode: function(node){
        	this.promoteChildNodes(node);
        },
        
*/        autocheckboxNode: function(node){
        	if (node.innerText.search('\\${') !== -1){
        		return;
        	}else{
	        	var parentNode = node.parentNode, content = node.innerText.split('☐'), checkedValue = content[0].trim().toLowerCase(), 
	        	 	 temp = content[1].split('['), newInnerHTML = temp[0], temp1 = temp[1].split(']'), values = temp1[0].split(','), post = temp1[1];
	        	 values.forEach(function(value){
	        		 newInnerHTML += (checkedValue === value.trim().toLowerCase() ? '☑' : '☐') + value;
	        	 });
	        	 newInnerHTML += post;
	        	 parentNode.insertBefore(dct.create('span', {innerHTML: newInnerHTML}), node);
	        	 parentNode.removeChild(node);
        	}
        },
        
        removeNodes: function(fromNode, classes){
    		classes.forEach(function(className){
    			var nodes = Array.apply(null, fromNode.getElementsByClassName(className));
                while (true){
                    var node = nodes.shift();
                    if (node){
                        node.parentNode.removeChild(node);
                    }else{
                        break;
                    }
                }
    		});
        },

        postProcess: function(content){
        	return this._inProcess(content, ['pagebreak', 'pagenumber', 'numberofpages']);
        },

        pagebreakNode: function(node){
            var parentNode = node.parentNode;
            parentNode.insertBefore(dct.create('p', {style: {pageBreakAfter: 'always'}}), node); 
            parentNode.removeChild(node);        	
        },
        pagenumberNode: function(node){
            var parentNode = node.parentNode;
            parentNode.insertBefore(dct.create('span', {'class': "page"}), node); 
            parentNode.removeChild(node);        	
        },
        numberofpagesNode: function(node){
            var parentNode = node.parentNode;
            parentNode.insertBefore(dct.create('span', {'class': "topage"}), node); 
            parentNode.removeChild(node);
        },

        _inProcess: function(content, classesName, classesToRemove){
            var el = document.createElement('html');
            el.innerHTML = '<html><head><title>titleTest</title></head><body>' + content + '</body></html>';
            var body = el.childNodes[1];
            if (body.innerHTML === 'undefined'){
                return '';
            }else{
            	classesName.forEach(lang.hitch(this, function(className){
            		var nodes = Array.apply(null, body.getElementsByClassName(className));
                    while (true){
                        var node = nodes.shift();
                        if (node){
                            lang.hitch(this, this[className + 'Node'])(node);
                        }else{
                            break;
                        }
                    }
            	}));
            	if (classesToRemove){
            		this.removeNodes(body, classesToRemove);
            	}
            }
            return body.innerHTML;
        },
        
        promoteChildNodes: function(node){
            var parentNode = node.parentNode, childNodes = Array.apply(null, node.childNodes);
            childNodes.forEach(function(childNode){
                parentNode.insertBefore(childNode, node);
            });
            parentNode.removeChild(node);
        },
        
        removeCheckbox: function(node){
            var parentNode = node.parentNode;
            if ((node.nextElementSibling || {}).className === "checkboxTemplateEnd"){
                parentNode.removeChild(node.nextElementSibling);
            }
            node.removeChild(node.childNodes[0]);
            this.promoteChildNodes(node);
        },
        
        removeCheckboxAndNode: function(node){
            var parentNode = node.parentNode;
            if ((node.nextSibling || {}).className === "checkboxTemplateEnd"){
                parentNode.removeChild(node.nextSibling);
            }
            parentNode.removeChild(node);
            //console.log('I am here');
            if (parentNode.innerHTML === ''){
                parentNode.parentNode.removeChild(parentNode);
            }
        },
        
        removeNode: function(node){
            var previousSibling = node.previousElementSibling, nextSibling = node.nextElementSibling;
        	if (previousSibling && previousSibling.className == 'visualTag'){
            	node.parentNode.removeChild(previousSibling);
            }
        	if (nextSibling && nextSibling.className == 'visualTag'){
            	node.parentNode.removeChild(nextSibling);
            }
        	node.parentNode.removeChild(node);
        },

        substituteParams: function(paramsString, panes){
            paramsString = this.removeTranslatorFlag(paramsString);
            return when(this.setParams(paramsString, panes), function(params){
                    try{
                        return utils.empty(params) ? paramsString : string.substitute(paramsString, params);
                    }catch(err){
                        return messages.errorcouldnotreplace + '(' + err + ')';
                    }
            });
        },

        setParams: function(paramsString, panes){
            var itemsParams = this.itemsParams(paramsString), items = {}, objects = {}, result = {},
                 params = {}/*itemsParams.params*/, properties = itemsParams.properties, itemsProperties = itemsParams.itemsProperties;
            utils.forEach(panes, function(pane, paneKey){
            	var paneParams = (params[paneKey] = {}), paneProperties = properties[paneKey], paneItemsProperties = itemsProperties[paneKey];
                utils.forEach(paneProperties, function(property, widgetName){
                    var value = pane.displayedValueOf(widgetName, true), key = paneKey + widgetName;
                    result[key] = paneProperties[widgetName] = typeof value === 'undefined' ? key : value;
                });
                utils.forEach(paneItemsProperties, function(paneItemProperties, widgetName){
                    var item = paneParams[widgetName] || (paneParams[widgetName] = pane.valueOf(widgetName, true));
                    if (item !== '' && typeof item !== 'undefined'){
                        if (items[item]){
                            items[item] = utils.array_unique_merge(items[item], paneItemProperties);
                        }else{
                            items[item] = paneItemProperties;
                            objects[item] = pane.getWidget(widgetName).object;
                        }
                    }else{
                    	paneItemProperties.forEach(function(property){
                            result[paneKey + widgetName + separator + property] = item === '' ? '' : paneKey + widgetName + separator + property;
                        });
                    }
                });
            });
            return all(result).then(function(result){
                if (!utils.empty(items)){
                    return Pmg.serverDialog({object: 'users', view: 'noview', mode: 'tab', action: 'getItems'}, {data: {items: items, objects: objects}}, messages.actionDone).then(
                        function (response){
                            var itemsResults = response.data;
                            utils.forEach(panes, function(pane, paneKey){
                                var paneParams = params[paneKey], paneItemsProperties = itemsProperties[paneKey];
                                utils.forEach(paneItemsProperties, function(paneItemProperties, widgetName){
                                    var item = paneParams[widgetName];
                                    paneItemProperties.forEach(function(property){
                                        result[paneKey + widgetName + separator + property] = (itemsResults[item] && itemsResults[item][property] !== null) ? itemsResults[item][property] : '';
                                    });
                                });
                            });
                            return result;
                        }
                    );
                }else{
                    return result;
                }
            });
        },

        itemsParams: function(paramsString){
            var paramRe = /\${([@§)])([^}]*)}/g,
                 result = {params: {'@': {}, '§': {}}, properties: {'@': {}, '§': {}}, itemsProperties: {'@': {}, '§': {}}},
                 params = result.params, properties = result.properties, itemsProperties = result.itemsProperties, matchArray;
            while (matchArray = paramRe.exec(paramsString)) {
                var matchType = matchArray[1], matchedParam = matchArray[2];
                if (!params[matchType][matchedParam]){
                    params[matchType][matchedParam] = true;
                    var paramArray = matchedParam.split(separator),
                         item = paramArray[0];
                    if (paramArray[1]){
                        var itemProperty = paramArray[1];
                        if (itemsProperties[matchType][item]){
                            if (!utils.in_array(itemProperty, itemsProperties[matchType][item])){
                                itemsProperties[matchType][item].push(itemProperty);
                            }
                        }else{
                            itemsProperties[matchType][item] = [itemProperty];
                        }
                    }else{
                        properties[matchType][item] = true;
                    }
                }
            }
            return result;
        },
        
        translateParams: function(paramsString, widget, itemTranslator, serverAction, sourceCharFlag, translatedCharFlag){
            try{
                var toRequest = {}, itemTranslator = itemTranslator || lang.hitch(Pmg, Pmg.widgetNameTranslator), //translate = translate || lang.hitch(Pmg, Pmg.translate), 
                      sourceCharFlag = sourceCharFlag || '$', translatedCharFlag = translatedCharFlag ||  '^', replaceRegExp = new RegExp('\\${\\' + sourceCharFlag + '([@§)])([^}]*)}', 'g'),
                      pane = widget.pane, form = pane.form || pane, objectName = form.object;
                var getObject = function(widgetName, item, matchType){
                    var widget = (matchType === '@' ? form : pane).getWidget(widgetName);
                    if (widget){
                        return widget.object;
                    }else{
                        throw messages.errorwrongwidgetname + ': ' + (widgetName || item);
                    }
                };
                paramsString = paramsString.replace(replaceRegExp, function(match, matchType, matchedParam){
                    var paramArray = matchedParam.split(separator), item = paramArray[0], itemTranslation = itemTranslator(item, objectName, matchType === '@' ? form : pane), itemWidgetName = (sourceCharFlag === '^' ? itemTranslation : item);
                    if (!itemTranslation){
                    	Pmg.addFeedback(messages.couldnotfindtranslateditem + ' ' + item + ' ' + messages.couldnotfindin + ' ' + match);
                    	return match;
                    }else{
	                    if (paramArray[1]){// here, if itemTranslation is undefined, we can conclude that there is an error in spelling of the template parameter, so we should skip - unless we accept an entry which is not a widget name has been made ?
	                        var widgetObjectName = getObject(itemWidgetName, item, matchType), widgetItem = paramArray[1], widgetItemTranslation = itemTranslator(widgetItem, widgetObjectName);
	                        if (itemTranslation && widgetItemTranslation){
	                            return '${' + translatedCharFlag + matchType +  itemTranslation + separator + widgetItemTranslation + '}';
	                        }else{
	                            (toRequest[widgetObjectName] || (toRequest[widgetObjectName] = [])).push(paramArray[1]);
	                            return match;
	                        }
	                    }else{
	                        return '${' + translatedCharFlag + matchType +  itemTranslation + '}';
	                    }
                    }
                });
                if (utils.empty(toRequest)){
                    return paramsString;
                }else{
                    return when (/*lang.hitch(Pmg, */Pmg.serverTranslations(toRequest, serverAction || 'getWidgetsNameTranslations')/*)*/, function(translations){
                        return paramsString.replace(replaceRegExp, function(match, matchType, matchedParam){
                            var paramArray = matchedParam.split(separator), item = paramArray[0], itemTranslation = itemTranslator(item, objectName), itemWidgetName = (sourceCharFlag === '^' ? itemTranslation : item);
                            if (!itemTranslation){
                            	return match;
                            }else{
                                if (paramArray[1]){
                                    var widgetObjectName = getObject(itemWidgetName, item, matchType), widgetItem = paramArray[1], widgetItemTranslation =  itemTranslator(widgetItem, widgetObjectName);
                                    if (widgetItemTranslation){
                                        return '${' + translatedCharFlag + matchType +  itemTranslation + separator + widgetItemTranslation + '}';
                                    }else{
                                        Pmg.addFeedback(messages.couldnotfindtranslateditem + ' ' + widgetItem + ' ' + messages.couldnotfindin + ' ' + match)
                                        return match;
                                    }
                                }else{
                                    return '${' + matchType +  itemTranslation + '}';
                                }
                            }
                        });
                    });
                }
            }catch(err){
                return messages.errortranslatingtemplate + ': ' + err + '<br >' + paramsString;
            }
        },

        untranslateParams: function(paramsString, widget){
            return this.translateParams(paramsString, widget, lang.hitch(Pmg, Pmg.widgetNameUntranslator), 'getWidgetsNameUntranslations', '^', '$');
        },

        removeTranslatorFlag: function(paramsString){
            return paramsString.replace(/\${(\$)([@§)][^}]*)}/g, '${$2}');
        },
        
        hasTranslation: function(paramsString){
            return paramsString ? paramsString.search(/\${(\$)([@§)][^}]*)}/) !== -1 : false;
        },  
        hasUntranslation: function(paramsString){
            return paramsString ? paramsString.search(/\${(\^)([@§)][^}]*)}/) !== -1 : false;
        },

        capitalToBlank: function(string){
            return string.replace(/([A-Z])/g, ' $1');
        }, 
        
        hasClass: function(node, className){
        	return (' ' + node.className + ' ').indexOf(' ' + className + ' ') > -1;
        }
    }
});
