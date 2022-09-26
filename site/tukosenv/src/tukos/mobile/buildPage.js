
define(["dojo/_base/lang", "dojo/dom-construct", "dojo/dom", "dojo/dom-style", "dojo/ready", "dojox/mobile/Container", "tukos/mobile/ViewsManager", "tukos/PageManager"],
function (lang, dct, dom, domStyle, ready, Container, ViewsManager, Pmg) {
	return {
		initialize: function(){
		    var appLayout = new Container();
		    Pmg.set('newPageCustomization', {ignoreCustomOnClose: 'YES'});
			document.body.appendChild(appLayout.domNode);
			Pmg.tabs = Pmg.mobileViews = new ViewsManager({container: appLayout, viewsDescription: Pmg.cache.tabsDescription});
		    domStyle.set(dom.byId('loadingOverlay'), 'display', 'none');
		    ready(function(){
		        appLayout.startup();
		    });
			Pmg.mayHaveNavigator = function(){
				return false;
			}
		}
	}
});
