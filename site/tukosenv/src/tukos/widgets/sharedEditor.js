define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/Deferred", "dojo/dom-style", "dijit/layout/ContentPane", "tukos/PageManager", "tukos/widgets/WidgetsLoader", "tukos/widgets/HtmlContent", "tukos/widgets/DnDWidget"], 
function(declare, lang, when, Deferred, domStyle, ContentPane, Pmg, WidgetsLoader, HtmlContent, DnD){
	
	sharedEditor = null;
	savedHtmlContentNode = null;
	
	return declare([ContentPane, DnD], {
		postCreate: function(){
			this.inherited(arguments);
			if (this.height){
				this.style.height = (parseInt(this.height) + 78) + 'px';
			}
			this.onClickHandle = this.on('click', lang.hitch(this, this.onClickCallback));
		},
		onClickCallback: function(){
			var self = this;
			if (!sharedEditor){
				when(WidgetsLoader.loadWidget('Editor'), function(Editor){
					sharedEditor = new Editor({style: {width: '100%'}}, dojo.doc.createElement("div"));
					self.placeEditor();
				});
			}else{
				this.placeEditor();
			}
		},

		placeEditor: function(){
			sharedEditor.set('value', this.get('content'));
			sharedEditor.widgetName = this.widgetName;
			sharedEditor.pane = this.pane;
			if (sharedEditor.iframe){
				domStyle.set(sharedEditor.iframe, this.getIframeState());	
				domStyle.set(sharedEditor.iframe.parentNode, 'height', this.height);
				//domStyle.set(sharedEditor.domNode, 'height', this.height);
				sharedEditor.resize();
			}else{
				sharedEditor.height = this.height;
			}
			this.set('content', '');
			domStyle.set(this.domNode, 'height', '');			
			this.addChild(sharedEditor);
			this.onClickHandle.remove();
		},
		
		onBlur: function(){
			this.style.height = dojo.getComputedStyle(this.domNode).height;
			this.height = (parseInt(this.style.height) - 78) + "px",
			domStyle.set(this.domNode, 'height', this.style.height);
			this.saveIframeState();
			this.removeChild(sharedEditor);
			this.set('content', sharedEditor.get('value'));
			this.onClickHandle = this.on('click', lang.hitch(this, this.onClickCallback));
            this.serverValueDeferred = new Deferred();
			when (sharedEditor.get('serverValue'), lang.hitch(this, function(newValue){
                this.serverValueDeferred.resolve(newValue);
			}));
		},
		
		saveIframeState: function(){//patterned after FullScreen plugin 
			var iframe = sharedEditor.iframe,
			iframeStyle = iframe && iframe.style || {};
			var bc = domStyle.get(iframe, "backgroundColor");
			this._origiFrameState = {backgroundColor: bc || "transparent", width: iframeStyle.width || "auto", height: iframeStyle.height || "auto", zIndex: iframeStyle.zIndex || ""};
		},
		
		getIframeState: function(){
			return this._origiFrameState || {backgroundColor: "transparent", width: "auto", "height": "auto"};
		},
		
        _setValueAttr: function(value){
        	this.set('content', value);
        	if (this.serverValueDeferred){
        		this.serverValueDeferred.cancel('');
        	}
        },
        _getValueAttr: function(){
        	return this.get('content');
        },
        _getServerValueAttr: function(){
            return this.serverValueDeferred || this.serverValue;
        },
        startup: function(){
        	this.inherited(arguments);
			domStyle.set(this.domNode, 'height', this.style.height);
        }
	});
}); 
