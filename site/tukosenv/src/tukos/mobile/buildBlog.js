define(["dojo/_base/lang", "dojo/_base/window", "dojo/_base/config", "dojo/ready", "dojo/dom", "dojo/dom-style", "dojo/dom-construct", "dojo/when",  "dijit/focus", "dojox/mobile/View", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "dojox/mobile/Tooltip", "tukos/mobile/ObjectPane", "tukos/mobile/TukosPane", 
	"tukos/widgets/WidgetsLoader", "tukos/PageManager"], 
function (lang, win, config, ready, dom, dst, dct, when, focusUtil, View, Heading, Button, Opener, ObjectPane, TukosPane, widgetsLoader, Pmg) {
	return {
		initialize: function(){
			var appLayout = new View({style: {backgroundColor: '#d0e9fc'}}, "appLayout"), formContent = Pmg.cache.tabsDescription[0].formContent, actionWidgets = Pmg.cache.rightPaneDescription.paneContent.widgetsDescription, searchPane, searchPaneNode, searchTextNode, searchArrowNode, 
				searchButton, searchButtonNode;
			formContent.viewPane = appLayout;
        	appLayout.addChild(appLayout.heading = new Heading({/*label: Pmg.cache.headerTitle, */style: {height: '60px'}}));
			//dst.set(appLayout.heading.labelNode, {lineHeight: '18px', whiteSpace: 'normal', verticalAlign: 'middle', fontSize: '18px', maxWidth: '130px', display: 'inline-block'});
			appLayout.heading.addChild(new Button({label: actionWidgets.recentposts.atts.title, style: {fontSize: '9px', whiteSpace: 'normal', lineHeight: '12px', padding: '2px', margin: '2px', maxWidth: '50px'}, onClick: function(){
					appLayout.heading.recentpostsOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(appLayout.heading.recentpostsFocusNode);
					}, 100);
				}})
			);
        	appLayout.heading.addChild(new Button({label: actionWidgets.categories.atts.title, style: {'float': 'left', fontSize: '9px', padding: '2px', margin: '2px'}, onClick: function(){
					appLayout.heading.categoriesOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(appLayout.heading.categoriesFocusNode);
					}, 100);
			}}));
        	appLayout.heading.addChild(searchButton = new Button({icon: config.baseUrl + "../dojox/editor/plugins/resources/icons/editorIconsFindReplaceEnabled.png", iconPos: '0,0,18,18', style: {'float': 'left', padding: '2px', margin: '2px'}, onClick: function(){
					appLayout.heading.searchOpener.show(this.domNode);
					setTimeout(function(){
						focusUtil.focus(searchPaneNode);
					}, 100);
			}}));
			searchButtonNode = searchButton.domNode;
			appLayout.heading.recentpostsOpener = new Opener();
        	win.body().appendChild(appLayout.heading.recentpostsOpener.domNode);
			appLayout.heading.categoriesOpener = new Opener();
        	win.body().appendChild(appLayout.heading.categoriesOpener.domNode);
			appLayout.heading.searchOpener = new Opener();
        	win.body().appendChild(appLayout.heading.searchOpener.domNode);
			var form = new ObjectPane(formContent);
			appLayout.addChild(form);
			['recentposts', 'categories'].forEach(function(widgetName, i){
				when(widgetsLoader.instantiate(actionWidgets[widgetName].type, lang.mixin(lang.clone(actionWidgets[widgetName].atts), {pane: {form: form}, onBlur: function(){appLayout.heading[widgetName+'Opener'].hide();}})), function (Widget){
					appLayout.heading[widgetName + 'Opener'].domNode.appendChild(Widget.domNode);
					appLayout.heading[widgetName + 'FocusNode'] = Widget.domNode;
				});
			});
			searchPane = new TukosPane({widgetsDescription: {searchbox: lang.clone(actionWidgets.searchbox), searchresults: lang.clone(actionWidgets.searchresults)}, layout: {tableAtts: {customClass: 'labelsAndValues', showLabels: false},
				widgets: ['searchbox', 'searchresults']}, form: form, tabindex: '0'});
			appLayout.heading.searchOpener.domNode.appendChild(searchPane.domNode);
			appLayout.addChild(new TukosPane(lang.mixin(Pmg.cache.rightPaneDescription.paneContent, {form: form, focusOnResults: true})));
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    Pmg.tabs = {currentPane: function(){return {form: form, resize: function(){}};}};
		    ready(function(){
			   	Pmg.setFeedback(Pmg.cache.feedback);
			   	appLayout.startup();
				searchPaneNode = searchPane.domNode;
				searchTextNode = searchPane.getWidget('searchbox').textbox;
				searchArrowNode = searchPane.getWidget('searchbox').arrowButton;
				dct.place('<span style="line-height: 25px; display: inline-block;vertical-align: middle;max-width: 130px;white-space: normal;">' + Pmg.cache.headerTitle + '</span>', appLayout.heading.domNode);
				dct.place('<img alt="logo" src="/tukos/images/tukosswissknife.png" style="height: 60px; width:auto; float: right;">', appLayout.heading.domNode);
		    });
			focusUtil.watch("curNode", function(name, oldValue, newValue){
				if (newValue === searchTextNode || newValue === searchArrowNode || newValue === searchPaneNode || newValue === searchButtonNode){
					return;
				}else{
					setTimeout(function(){appLayout.heading.searchOpener.hide();}, 300);
				}
			});
		}
	}
});
