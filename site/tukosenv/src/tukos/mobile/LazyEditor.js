define(["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/when", "dojo/Deferred", "dojo/dom-style", "dojox/mobile/ContentPane", "tukos/mobile/ScrollableContainer",  "tukos/PageManager", "tukos/widgets/WidgetsLoader", 
        "tukos/widgets/HtmlContent", "tukos/widgets/DnDWidget"], 
  function(declare, lang, ready, when, Deferred, domStyle, ContentPane, ScrollableContainer, Pmg, WidgetsLoader, HtmlContent, DnD){
	var editor;
	return declare([ScrollableContainer], {
		constructor: function(args){
		},
		postCreate: function(){
			this.inherited(arguments);
    		this.container = new ContentPane(); 
    		this.addChild(this.container);
			this.htmlContent = new HtmlContent({style: {width: '100%', height: this.editorToContentHeight(this.height) || "auto"}, value: this.value || ''});           	
			this.container.addChild(this.htmlContent);
			this.onClickHandle = this.on('click', lang.hitch(this, this.onClickCallback));
			this.onBlurHandle = this.on('blur', lang.hitch(this, this.onBlurCallback));	
		},
		
		onClickCallback: function(){
			if (!this.disabled && !this.readOnly){
				console.log('entering onCLickCallback - this.id:' + this.id);
				if (this.onClickHandle){
					this.onClickHandle.remove();
				}
				if (!editor){
					when(WidgetsLoader.loadWidget('MobileEditor'), lang.hitch(this, function(Editor){
						editor = new Editor({style: "width: 100%;"}, dojo.doc.createElement("div"));
						editor.startup();//JCH: needed for OverviewDgrid editor instantiation in colValues
						this.placeEditor();
				}));
				}else{
					this.placeEditor();
				}
			}
		},
				editorToContentHeight: function(editorHeight){
			return editorHeight ? (parseInt(editorHeight) + 78) + 'px' : undefined;
		},
		contentToEditorHeight: function(contentHeight){
			return contentHeight ? (parseInt(contentHeight) - 78) + 'px' : undefined;
		},
		placeEditor: function(){
			console.log('calling placeEditor');
			var htmlContent = this.htmlContent, height = this.contentToEditorHeight(htmlContent.get('style').height);
			editor.widgetName = this.widgetName;
			editor.pane = this.pane;
			editor.set({height: height || "auto", width: "100%"});
			if (editor.iframe){
				editor.iframe.style.height = height;
				domStyle.set(editor.editingArea, {height: height, width: "auto"});
				domStyle.set(editor.domNode, {height: "auto", width: "auto"});
			}
			editor.set('value', htmlContent.get('value'));
			this.container.removeChild(htmlContent);
			this.container.addChild(editor);
		},
		onBlurCallback: function(){
			console.log('calling onBlurCallback');
			var htmlContent = this.htmlContent;
			setTimeout(lang.hitch(this, function(){// to let time for editor html onblur to completre before getting the resulting html, e.g. in expressions.onBlur
				if (editor && this.getIndexOfChild(editor) > -1/* && editor.isFullscreen !== true*/){//case where focus not via onClick, e.g. onDrop
					htmlContent.set('style', {height: this.editorToContentHeight(editor.get('height'))});
					this.set('serverValue', editor.get('serverValue'));
					this.set('value', editor.get('value'));
					this.container.removeChild(editor);
					this.container.addChild(htmlContent);
					this.resize();
					this.onClickHandle = this.on('click', this.onClickCallback);
				}
			}), 100)
		},
		_setValueAttr: function(value){
			if (this.htmlContent){
				this.htmlContent.set('value', value);
			}
			this._set('value', value);
		},		
		_setStyleAttr: function(value){
			this.inherited(arguments);
			if (this.htmlContent){
				this.htmlContent.set('style', value);
			}
		},		
		_getServerValueAttr: function(){
			return this.serverValue;
		},		
		_getDisplayedValueAttr: function(){
			var result = this.inherited(arguments);
			return result;
		}
	});
}); 
