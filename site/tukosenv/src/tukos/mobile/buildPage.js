
define(["dojo/_base/lang", "dojo/dom", "dojo/dom-style", "dojo/ready", "dojox/mobile/Container", "tukos/mobile/ViewsManager", "tukos/PageManager"],
function (lang, dom, domStyle, ready, Container, ViewsManager, Pmg) {
	return {
		initialize: function(){
		    var appLayout = new Container(), self = this;
		    Pmg.set('newPageCustomization', {ignoreCustomOnClose: 'YES'});
		    document.body.appendChild(appLayout.domNode);
			Pmg.tabs = Pmg.mobileViews = new ViewsManager({container: appLayout, viewsDescription: Pmg.cache.tabsDescription});
		    domStyle.set(dom.byId('loadingOverlay'), 'display', 'none');
		    ready(function(){
		        appLayout.startup();
		        //appLayout.resize();
		    });
			Pmg.mayHaveNavigator = function(){
				return false;
			}
		}
	}
});
