define(["dojo/_base/lang", "dojo/_base/window", "dojo/_base/config", "dojo/ready", "dojo/dom", "dojo/dom-style", "dojo/dom-construct", "dojo/when",  "dijit/focus", "dojox/mobile/Container", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "dojox/mobile/Tooltip", "tukos/mobile/ObjectPane", 
	"tukos/mobile/TukosPane", "tukos/mobile/PostsManager", "tukos/widgets/WidgetsLoader", "tukos/PageManager"], 
function (lang, win, config, ready, dom, dst, dct, when, focusUtil, Container, Heading, Button, Opener, ObjectPane, TukosPane, PostsManager, widgetsLoader, Pmg) {
	return {
		initialize: function(){
			var self = this, appLayout = new Container({style: {backgroundColor: '#d0e9fc'}}), button, actionWidgets = Pmg.cache.rightPaneDescription.paneContent.widgetsDescription, searchPane, searchButton;
        	this.heading = new Heading({/*label: Pmg.cache.headerTitle, */style: {height: '60px'}});
			this.heading.addChild(new Button({label: actionWidgets.recentposts.atts.title, style: {fontSize: '9px', whiteSpace: 'normal', lineHeight: '12px', padding: '2px', margin: '2px', maxWidth: '50px'}, onClick: function(){
					self.heading.recentpostsOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(self.heading.recentpostsFocusNode);
					}, 100);
				}}));
        	this.heading.addChild(new Button({label: actionWidgets.categories.atts.title, style: {'float': 'left', fontSize: '9px', padding: '2px', margin: '2px'}, onClick: function(){
					self.heading.categoriesOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(self.heading.categoriesFocusNode);
					}, 100);
			}}));
        	this.heading.addChild(searchButton = new Button({icon: config.baseUrl + "../dojox/editor/plugins/resources/icons/editorIconsFindReplaceEnabled.png", iconPos: '0,0,18,18', style: {'float': 'left', padding: '2px', margin: '2px'}, onClick: function(){
					self.heading.searchOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(searchPane.domNode);
					}, 100);
			}}));
			this.heading.startup();
			document.body.appendChild(this.heading.domNode);
			document.body.appendChild(appLayout.domNode);
			this.heading.recentpostsOpener = new Opener();
        	win.body().appendChild(this.heading.recentpostsOpener.domNode);
			this.heading.categoriesOpener = new Opener();
        	win.body().appendChild(this.heading.categoriesOpener.domNode);
			this.heading.searchOpener = new Opener();
        	win.body().appendChild(this.heading.searchOpener.domNode);
			Pmg.tabs = Pmg.mobileViews = new PostsManager({container: appLayout, viewsDescription: Pmg.cache.tabsDescription});
			var form = Pmg.tabs.currentPane().form;
			['recentposts', 'categories'].forEach(function(widgetName, i){
				when(widgetsLoader.instantiate(actionWidgets[widgetName].type, lang.mixin(lang.clone(actionWidgets[widgetName].atts), {pane: {form: form}, onBlur: function(){self.heading[widgetName+'Opener'].hide();}})), function (Widget){
					self.heading[widgetName + 'Opener'].domNode.appendChild(Widget.domNode);
					self.heading[widgetName + 'FocusNode'] = Widget.domNode;
				});
			});
			searchPane = new TukosPane({widgetsDescription: {searchbox: lang.clone(actionWidgets.searchbox), searchresults: lang.clone(actionWidgets.searchresults)}, layout: {tableAtts: {customClass: 'labelsAndValues', showLabels: false},
				widgets: ['searchbox', 'searchresults']}, form: form, tabindex: '0'});
			this.heading.searchOpener.domNode.appendChild(searchPane.domNode);
			//appLayout.addChild(new TukosPane(lang.mixin(Pmg.cache.rightPaneDescription.paneContent, {form: form/*, focusOnResults: true*/})));
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    ready(function(){
			   	Pmg.setFeedback(Pmg.cache.feedback);
			   	appLayout.startup();
				dct.place('<span style="line-height: 25px; display: inline-block;vertical-align: middle;max-width: 130px;white-space: normal;">' + Pmg.cache.headerTitle + '</span>', self.heading.domNode);
				dct.place('<img alt="logo" src="/tukos/images/tukosswissknife.png" style="height: 60px; width:auto; float: right;">', self.heading.domNode);
		    });
			focusUtil.watch("curNode", function(name, oldValue, newValue){
				if (newValue === searchPane.getWidget('searchbox').textbox || newValue === searchPane.getWidget('searchbox').arrowButton || newValue === searchPane.domNode || newValue === searchButton.domNode){
					return;
				}else{
					setTimeout(function(){self.heading.searchOpener.hide();}, 300);
				}
			});
		}
	}
});
