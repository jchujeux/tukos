define(["dojo/ready", "dojo/dom", "dojo/dom-style", "dijit/layout/BorderContainer", "dijit/layout/ContentPane", "dijit/layout/TabContainer", "tukos/ObjectPane", "tukos/TukosPane", "tukos/TabsManager",  "tukos/PageManager"], 
function (ready, dom, dst, BorderContainer, ContentPane, TabContainer, ObjectPane, TukosPane, TabsManager, Pmg) {
	return {
		initialize: function(){
			//var appLayout = new ObjectPane(Pmg.cache.blogDescription[0].formContent, "appLayout");
			var obj = Pmg.cache, appLayout = new BorderContainer({design: 'sidebar', gutters: false, style: {padding: '0px'}}, 'appLayout'), 
				 contentHeader = new ContentPane({id: 'tukosHeader', region:'top', 'class': 'edgePanel', style: "padding: 0px;border: 1px;", content: obj.headerContent}), 
				 //contentCenter = new ContentPane({id: 'tukosCenter', region:'center', 'class': 'centerPanel', style: "padding: 0px;"}),
			     contentCenter = new TabContainer({id: "centerPanel", region: "center", tabPosition: "top", 'class': "centerPanel", style: "width: 100%; height: 100%; padding: 0px"});
				 contentRight = new ContentPane({id: 'tukosRightPane', region: 'right', 'class': 'right', splitter: true, style: {width: '25%', padding: '0px'}, content: obj.rightPaneContent}),
				 //centerPane= new ObjectPane(obj.blogDescription[0].formContent);
				 rightPane = new TukosPane(obj.rightPaneDescription.paneContent);
			Pmg.tabs = new TabsManager({container: contentCenter, tabsDescription: obj.tabsDescription});
			//contentCenter.addChild(centerPane);
			contentRight.addChild(rightPane);
			appLayout.addChild(contentHeader);
			appLayout.addChild(contentCenter);
			appLayout.addChild(contentRight);
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    //Pmg.tabs = {currentPane: function(){return {form: appLayout, resize: function(){}};}};
		    ready(function(){
			   	//Pmg.setFeedback(Pmg.cache.feedback);
			   	//appLayout.getWidget('feedback').set('value', Pmg.cache.feedback.join("\n"));
		    	appLayout.startup();
		    });
		}
	}
});
