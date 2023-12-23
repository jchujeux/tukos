define(["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/when", "dojo/dom-style", "dijit/layout/ContentPane", "tukos/PageManager", "tukos/widgets/WidgetsLoader", 
        "tukos/widgets/HtmlContent", "tukos/widgets/DnDWidget", "tukos/widgets/widgetCustomUtils"], 
  function(declare, lang, ready, when, domStyle, ContentPane, Pmg, WidgetsLoader, HtmlContent, DnD, wcutils){
	var editors = {}, isPlaced = false, editorTypes = {normal: 'Editor', simple: 'MobileEditor', basic: 'TukosTextarea'};
	return declare([ContentPane, DnD], {
		postCreate: function(){
			this.inherited(arguments);
        	this.customizableAtts = lang.mixin({height: wcutils.sizeAtt('height')}, this.customizableAtts);
			this.set('style', {padding: 0});    		
			this.htmlContent = new HtmlContent({noMathJax: true, style: {width: '100%', height: this.height || "auto"}, value: this.value || ''});   
			if (this.disabled){
				this.htmlContent.set('style', {backgroundColor: '#F0F0F0'});				
			}        	
        	this.watch('height', function(attr, oldValue, newValue){this.htmlContent.set('style', {height: newValue})});
			this.addChild(this.htmlContent);
			this.onClickHandle = this.on('click', this.onClickCallback);
			this.viewSource = false;
			this.editorType = this.editorType || (Pmg.isMobile() ? 'simple' : 'normal');
		},
		onClickCallback: function(){
			if (!this.disabled && !this.readonly){
				var editorType = this.editorType;
				this.onClickHandle.remove();
				if (!editors[editorType]){
					when(WidgetsLoader.loadWidget(editorTypes[editorType]), lang.hitch(this, function(Editor){
						editors[editorType] = new Editor({style: {width: '100%'/*, minHeight: '150px'*/}, toolbarMode: 'alwaysOn'}, dojo.doc.createElement("div"));
						editors[editorType].startup();//JCH: needed for OverviewDgrid editor instantiation in colValues
						this.placeEditor();
					}));
				}else{
					if (!isPlaced){
						this.placeEditor();
					}
				}
			}
		},
		_setDisabledAttr: function(value){
			this.disabled = value;
			if (this.htmlContent){
				this.htmlContent.set('style', {backgroundColor: value ? '#F0F0F0' : ''});				
			}
		},
		resetEditor: function(){
			editors[this.editorType] = null;
		},
		customContextMenuItems: function(){
			var self = this;    		
			return [{atts: {label: Pmg.message('resetEditor')  , onClick: function(){self.resetEditor()}}}];
		},
		editorToContentHeight: function(editorHeight){
			return editorHeight ? (parseInt(editorHeight) + 78) + 'px' : undefined;
		},
		contentToEditorHeight: function(contentHeight){
			return contentHeight ? (Math.max(parseInt(contentHeight), 150) - 78) + 'px' : undefined;
		},
		placeEditor: function(){
			var htmlContent = this.htmlContent, height = this.contentToEditorHeight(htmlContent.get('style').height), editor = editors[this.editorType];
			editor.widgetName = this.widgetName;
			editor.pane = this.pane;
			editor.lazyEditor = this;
			if (this.editorType === 'basic'){
				domStyle.set(editor.domNode, {maxHeight: htmlContent.get('style').height || 'auto'});
			}else{
				editor.set({height: height || "auto", width: "100%"});
			}
			if (editor.iframe){
				editor.iframe.style.height = height;
				domStyle.set(editor.editingArea, {height: height, width: "auto"});
				domStyle.set(editor.domNode, {height: "auto", width: "auto"});
			}
			editor.set('value', htmlContent.get('value'));
			this.removeChild(htmlContent);
			this.addChild(editor);
			if (editor.isInViewSource && (this.viewSource !== editor.isInViewSource())){
				editor.toggle();
			}
			editor.focus();
			isPlaced = true;
			this.onBlurHandle = editor.on('blur', lang.hitch(this, this.onBlurCallback));
		},
		
		onBlurCallback: function(){
			var htmlContent = this.htmlContent, self = this, newValue, editor = editors[this.editorType];
			this.onBlurHandle.remove();
			if (editor && this.getIndexOfChild(editor) > -1/* && editor.isFullscreen !== true*/){//case where focus not via onClick, e.g. onDrop
				//htmlContent.set('style', {height: this.editorToContentHeight(editor.get('height'))});
				if (editor.isInViewSOurce){
					this.viewSource = editor.isInViewSource();
				}
				editor.set('value', (newValue = editor.get('value')));// to make sure the Editor onWatch is triggered, that untranslates if needed into serverValue
				this.set('value', newValue);
				when(editor.get('serverValue'), function(serverValue){
					if (serverValue){
						self.set('value', serverValue);
					}
				})

				this.removeChild(editor);
				this.addChild(htmlContent);
				this.resize();
				this.onClickHandle = this.on('click', this.onClickCallback);
				isPlaced = false;
			}
		},
		_setValueAttr: function(value){
			if (this.htmlContent){
				this.htmlContent.set('value', value || '');
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
