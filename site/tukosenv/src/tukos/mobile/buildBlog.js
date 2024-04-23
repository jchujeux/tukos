define(["dojo/_base/lang", "dojo/_base/window", "dojo/_base/config", "dojo/ready", "dojo/dom", "dojo/dom-style", "dojo/dom-construct", "dojo/when",  "dojo/topic", "dijit/focus", "dojox/mobile/Container", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "dojox/mobile/Tooltip",
	"tukos/mobile/TukosPane", "tukos/mobile/PostsManager", "tukos/widgets/WidgetsLoader", "tukos/TabOnClick",  "tukos/PageManager"], 
function (lang, win, config, ready, dom, dst, dct, when, topic, focusUtil, Container, Heading, Button, Opener, TukosPane, PostsManager, widgetsLoader, TabOnClick, Pmg) {
	return {
		initialize: function(){
			var self = this, buttonsContainer = new Container({style: {maxWidth: '150px'}}), postsContainer = new Container({style: {backgroundColor: '#d0e9fc'/*, touchAction: 'none'*/}}), 
				actionWidgets = Pmg.cache.rightPaneDescription.paneContent.widgetsDescription, searchPane, searchButton, homeUrlArgs = {action: 'Tab', mode: 'Tab', object: 'backoffice', view: 'edit', query: {form: 'Show', object: 'blog', name: Pmg.message('blogwelcome', 'backoffice')}};
        	this.heading = new Heading({/*label: Pmg.cache.headerTitle, */style: {height: '65px'}});
			this.heading.addChild(buttonsContainer);
			buttonsContainer.addChild(new Button({label: actionWidgets.recentposts.atts.title, style: {fontSize: '9px', whiteSpace: 'normal', lineHeight: '12px', padding: '1px', margin: '2px', maxWidth: '40px'}, onClick: function(){
					self.heading.recentpostsOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(self.heading.recentpostsFocusNode);
					}, 100);
				}}));
        	buttonsContainer.addChild(new Button({label: actionWidgets.categories.atts.title, style: {'float': 'left', fontSize: '9px', padding: '1px', margin: '2px'}, onClick: function(){
					self.heading.categoriesOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(self.heading.categoriesFocusNode);
					}, 100);
			}}));
        	buttonsContainer.addChild(searchButton = new Button({icon: config.baseUrl + "../dojox/editor/plugins/resources/icons/editorIconsFindReplaceEnabled.png", iconPos: '0,0,18,18', style: {'float': 'left', padding: '1px', margin: '2px'}, onClick: function(){
					self.heading.searchOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(searchPane.domNode);
					}, 100);
			}}));
			buttonsContainer.addChild(homeButton = new Button({icon: require.toUrl('tukos/resources/images/home27.png'), style: {'float': 'left', padding: '1px', margin: '0px'}, onClick: function(){
				Pmg.mobileViews.gotoTab({action: 'Tab', mode: 'Tab', object: 'backoffice', view: 'edit', query: {form: 'Show', object: 'blog', name: Pmg.message('blogwelcome', 'backoffice')}});
			}}));
			buttonsContainer.addChild(postsContainer.previousButton = new Button({icon: require.toUrl('tukos/resources/images/left24.png'), style: {display: 'none', 'float': 'left', padding: '1px', margin: '0px'}, onClick: function(){
				Pmg.mobileViews.selectPreviousPane();
			}}));
			buttonsContainer.addChild(postsContainer.nextButton = new Button({icon: require.toUrl('tukos/resources/images/right24.png'), style: {display: 'none', 'float': 'left', padding: '1px', margin: '0px'}, onClick: function(){
				Pmg.mobileViews.selectNextPane();
			}}));
			this.heading.startup();
			document.body.appendChild(this.heading.domNode);
			document.body.appendChild(postsContainer.domNode);
			this.heading.recentpostsOpener = new Opener();
        	win.body().appendChild(this.heading.recentpostsOpener.domNode);
			this.heading.categoriesOpener = new Opener();
        	win.body().appendChild(this.heading.categoriesOpener.domNode);
			this.heading.searchOpener = new Opener();
        	win.body().appendChild(this.heading.searchOpener.domNode);
			Pmg.tabs = Pmg.mobileViews = new PostsManager({container: postsContainer, viewsDescription: Pmg.cache.tabsDescription, pageWidget: this});
			/*topic.subscribe("/dojox/mobile/viewChanged", function(view){//only useful if View is SwapView!
				Pmg.tabs.container.selectChild(view, false);
			});*/
			/*topic.subscribe("/dojox/mobile/viewChanged", function(view){
				Pmg.tabs.selectedChildWidget = view;
				view.set('style', {height: 'auto'});
			});*/
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
			var bottomLayout = this.bottomContainer = new Container({style: {backgroundColor: '#d0e9fc'}}), bottomPane = new TukosPane(lang.mixin(Pmg.cache.rightPaneDescription.paneContent, {form: form/*, focusOnResults: true*/}));
			bottomLayout.addChild(bottomPane);
			document.body.appendChild(bottomLayout.domNode);
			
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    ready(function(){
			   	Pmg.setFeedback(Pmg.cache.feedback);
			   	postsContainer.startup();
				bottomLayout.startup();
				setTimeout(function(){
					postsContainer.selectedChildWidget.set('style', {height: 'auto'});
				}, 100);
				dct.place('<span style="line-height: 25px; display: inline-block;vertical-align: middle;max-width: 130px;white-space: normal;">' + Pmg.cache.headerTitle + '</span>', self.heading.domNode);
				new TabOnClick({url: homeUrlArgs}, dct.place('<img alt="logo" src="/tukos/images/tukosswissknife.png" style="height: 60px; width:auto; float: right;margin-top: -50px">', self.heading.domNode));
				dct.place(Pmg.cache.contactSpan, self.heading.domNode);
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
