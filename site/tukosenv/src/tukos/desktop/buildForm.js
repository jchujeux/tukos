define(["dojo/ready", "dojo/dom", "dojo/dom-style", "tukos/ObjectPane", "tukos/PageManager"], 
function (ready, dom, dst, ObjectPane, Pmg) {
	return {
		initialize: function(){
			var appLayout = new ObjectPane(Pmg.cache.formDescription[0].formContent, "appLayout");
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    Pmg.tabs = {currentPane: function(){return {form: appLayout, resize: function(){}};}};
		    ready(function(){
			   	Pmg.setFeedback(Pmg.cache.feedback);
			   	//appLayout.getWidget('feedback').set('value', Pmg.cache.feedback.join("\n"));
		    	appLayout.startup();
		    });
		}
	}
});
