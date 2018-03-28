define(["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/when", "dojo/Deferred", "dojo/dom-style", "dijit/layout/ContentPane", "tukos/PageManager", "tukos/widgets/WidgetsLoader", 
        "tukos/widgets/HtmlContent", "tukos/widgets/DnDWidget"], 
  function(declare, lang, ready, when, Deferred, domStyle, ContentPane, Pmg, WidgetsLoader, HtmlContent, DnD){
	var editor;
	return declare([ContentPane, DnD], {
		constructor: function(args){
		},
		postCreate: function(){
			this.inherited(arguments);
			this.htmlContent = new HtmlContent({style: {width: '100%', height: this.editorToContentHeight(this.height) || "auto"}, value: this.value||''}, dojo.doc.createElement("div"));
			this.addChild(this.htmlContent);
			this.onClickHandle = this.on('click', this.onClickCallback);
			this.onBlurHandle = this.on('blur', this.onBlurCallback);				
		},
		onClickCallback: function(){
			if (!this.disabled && !this.readOnly){
				console.log('entering onCLickCallback - this.id:' + this.id);
				this.onClickHandle.remove();
				console.log('just removed onClickHandle');
				if (!editor){
					when(WidgetsLoader.loadWidget('Editor'), lang.hitch(this, function(Editor){
						editor = new Editor({style: {width: '100%'}}, dojo.doc.createElement("div"));
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

		placeEditor: function(unhide){
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
			this.removeChild(htmlContent);
			this.addChild(editor);
		},
		
		onBlurCallback: function(){
			console.log('calling onBlurCallback');
			var htmlContent = this.htmlContent;
			if (editor && this.getIndexOfChild(editor) > -1/* && editor.isFullscreen !== true*/){//case where focus not via onClick, e.g. onDrop
				htmlContent.set('style', {height: this.editorToContentHeight(editor.get('height'))});
				this.set('serverValue', editor.get('serverValue'));
				this.set('value', editor.get('value'));
				this.removeChild(editor);
				this.addChild(htmlContent);
				this.resize();
				this.onClickHandle = this.on('click', this.onClickCallback);
			}
		},
		
		_setValueAttr: function(value){
			if (value !== this.value){
				if (this.htmlContent){
					this.htmlContent.set('value', value);
				}
				this._set('value', value);
			}
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
