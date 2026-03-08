define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojoFixes/dojox/html/format", "tukos/TukosTooltipDialog", "tukos/PageManager"], 
function(declare, lang, dct, htmlFormat, TooltipDialog, Pmg) {
	//Pmg.serverTranslations({tukos: ['htmlSource', 'ancestorTag', 'sourceLang', 'targetLang', 'srcAncestor', 'srcTranslate', 'srcInsert', 'srcRemove']});
	var _htmlSourceInserter =  declare(null, {

       constructor: function(args){
		   lang.mixin(this, args);
	   },
       inserterDialog: function(){
        	return this.srcDialog || (this.srcDialog = new TooltipDialog(this.srcDialogDescription()));
       },
       srcDialogDescription: function(){
            var widgetsDescription = {
                    content: {type: 'Textarea', atts: {label: Pmg.message('htmlSource', 'tukos'), style: {width: '1000px', maxHeight: '600px'}}},
                    ancestor: {type: 'TextBox', atts:{label: Pmg.message('ancestorTag', 'tukos')}},
                   sourcelang: {type: 'StoreComboBox', atts: {label: Pmg.message('sourceLang', 'tukos'), style: {width: '5em'}, storeArgs: {data: [{id: 'en', name: 'english'}, {id: 'es', name: 'Español'}, {id: 'fr', name: 'français'}]}}},
                   targetlang: {type: 'StoreComboBox', atts: {label: Pmg.message('targetLang', 'tukos'), style: {width: '5em'}, storeArgs: {data: [{id: 'en', name: 'english'}, {id: 'es', name: 'Español'}, {id: 'fr', name: 'français'}]}}}
            };
            ['srcAncestor', 'srcGeminiResponse', 'srcLeChatResponse', 'svgForeignObject', 'svgClean', 'srcTranslate', 'srcInsert', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: Pmg.message(action, 'tukos'), onClick: lang.hitch(this, this[action])}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
                        contents: {
                        	row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['content']},
                        	row2: {
                        		tableAtts: {cols: 6, customClass: 'labelsAndValues', showLabels: false},
                        		contents: {
                        			col1: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['ancestor']},
                        			col2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['srcAncestor']},
                        			col3: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['sourcelang', 'targetlang']},
                        			col4: {tableAtts: {cols: 5, showLabels: false}, widgets: ['srcTranslate']},
                        			col6: {tableAtts: {cols: 2, showLabels: false}, widgets: ['srcGeminiResponse', 'srcLeChatResponse', 'svgForeignObject', 'svgClean']}
                        		}
                        	},
                        	row3: {tableAtts: {cols: 3, showLabels: false}, widgets: ['srcInsert', 'remove', 'close']}
                        }
                    }
                },
                onOpen: lang.hitch(this, function(){
                    var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), contentWidget = paneGetWidget('content'), selection = this.editor.selection, ancestorTag = paneGetWidget('ancestor').get('value');
                	contentWidget.set('value', selection.getSelectedHtml());
                })
            };
        	
        },

	    srcInsert: function(){
	    	var pane = this.srcDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), content = valueOf('content'), editor = this.editor, selection = editor.selection;
	    	if (content){
		    	selection.remove();	    		
		    	editor.pasteHtmlAtCaret(content, true);
		    	editor.set('value', editor.get('value'));
	    	}
	    },

        close: function(){
			this.srcDialog.close();
			this.inserter.close();
		},
        remove: function(){
        	this.editor.selection.remove();
        },
        
        srcAncestor: function(){
        	var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), tagName = paneGetWidget('ancestor').get('value'),
        		selection = this.editor.selection, ancestorElement = tagName ? selection.getAncestorElement(tagName) : selection.getParentElement();
    
        	if (ancestorElement.id === 'dijitEditorBody' /*|| ancestorElement.tagName === 'TD'*/){
        		selection.selectElementChildren(ancestorElement);
        	}else{
            	selection.selectElement(ancestorElement);       		
        	}
    
        	paneGetWidget('content').set('value', htmlFormat.prettyPrint(ancestorElement/*.outerHTML*/, 2));
        },
		svgForeignObject: function(){
			var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget),  contentWidget = paneGetWidget('content'), content = contentWidget.get('value');
			if (content){
				if (content.includes('<math ') && !content.includes('xmlns')){
					content = content.replace(/(<math [^>]*)(>)/, '$1 xmlns="http://www.w3.org/1998/Math/MathML"$2');
				}
				content = '<foreignObject x="10" y="10" width="60" height="40">' + content + '</foreignObject>';
			}
			contentWidget.set('value', content);
		},
		svgClean: function(){
			var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget),  contentWidget = paneGetWidget('content'), content = contentWidget.get('value');
			if (content){
				content = content.replace('&nbsp;', ' ');
			}
			contentWidget.set('value', content);
		},
        srcAIResponse: function(serverAction){
	    	const  pane = this.srcDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), sourceContent = valueOf('content'); 
			let targetContent = 'to be done';
			Pmg.serverDialog({object: 'users', view: 'NoView', action: serverAction}, {data: {request: sourceContent}}).then(function(response){
				if (response.generatedContent){
					pane.setValueOf('content', response.generatedContent);
				}else{
					Pmg.alert('No content from ' + serverAction);
				}
			});
            
		},
		srcGeminiResponse: function(){
			this.srcAIResponse('getGeminiResponse');
		},
		srcLeChatResponse: function(){
			this.srcAIResponse('getLeChatResponse');
		},
        srcTranslate: function(){//thanks to https://www.googlecloudcommunity.com/gc/AI-ML/Can-the-Google-Translate-API-v2-be-used-in-the-frontend/m-p/598403
	    	var pane = this.srcDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), content = valueOf('content'), sourceLang = (pane.getWidget('sourcelang').get('item') || {}).id, targetLang = (pane.getWidget('targetlang').get('item') || {}).id;
	    	if (content && sourceLang && targetLang){
				if (this.translationKey){
					this.fetchTranslation(content, sourceLang, targetLang, this.translationKey);
				}else{
					if (this.translationKey === false){
						Pmg.setFeedback(Pmg.message('YouneedatranslationKey', 'tukos'), null, null, true);
					}else{
						const self = this;
						Pmg.serverDialog({object: 'users', view: 'NoView', action: 'GetTranslationKey'}).then(function(response){
							if (response.key){
								self.translationKey = response.key;
								self.fetchTranslation(content, sourceLang, targetLang, self.translationKey);
							}else{
								self.translationKey = false;
							}
						});
					}
				}
	    	}
		},
		fetchTranslation: function(content, sourceLang, targetLang, key){
			const pane = this.srcDialog.pane;
			const url = 'https://translation.googleapis.com/language/translate/v2?key=' + key;
			const headers = {
			  'Content-Type': 'application/json; charset=utf-8',
			};
			
			fetch(url, {
			  method: 'POST',
			  headers: headers,
			  body: JSON.stringify({
			    "q": [content],
			    "source": sourceLang,
			    "target": targetLang
			  }),
			})
			  .then((response) => response.json())
			  .then((data) => {pane.setValueOf('content', data.data.translations[0].translatedText);})
			  .catch((error) => console.error(error));
		}  
    });
    
    _htmlSourceInserter.translations = {tukos: ['ancestorTag', 'sourceLang', 'targetLang', 'srcAncestor', 'srcTranslate', 'srcInsert', 'srcGeminiResponse']};
    return _htmlSourceInserter;
});
