define(["dojo/ready", "dojo/dom", "dojo/dom-style", "dojo/dom-construct", "dojox/mobile/View", "dojox/mobile/Heading", "tukos/mobile/ObjectPane", "tukos/mobile/TukosTextarea", "tukos/PageManager"], 
function (ready, dom, dst, dct, View, Heading, ObjectPane, TukosTextarea, Pmg) {
	return {
		initialize: function(){
			var appLayout = new View(null, "appLayout"), formContent = Pmg.cache.formDescription[0].formContent;
			formContent.viewPane = appLayout;
        	appLayout.addChild(appLayout.heading = new Heading({fixed: 'top'}));
        	appLayout.addChild(appLayout.actionsHeading = new Heading());
			var form = new ObjectPane(formContent);
    		appLayout.addChild(new TukosTextarea({id: form.id + 'feedback', style: {
    			backgroundColor: 'DarkGrey', width: '100%', color: 'White', fontStyle: 'italic', fontSize: '14px'}, pane: form, form: form, widgetType: 'TukosTextarea', widgetName: 'feedback', disabled: true}));
			appLayout.addChild(form);
		    dst.set(dom.byId('loadingOverlay'), 'display', 'none');
		    Pmg.tabs = {currentPane: function(){return {form: form, resize: function(){}};}};
		    ready(function(){
			   	Pmg.setFeedback(Pmg.cache.feedback);
			   	appLayout.startup();
			   	appLayout.heading.set('label', formContent.title);
		    });
		}
	}
});
