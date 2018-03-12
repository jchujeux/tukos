define (["dojo/_base/declare",  "dojo/_base/lang", "dojo/when", "dijit/layout/ContentPane", "dijit/layout/BorderContainer", "dijit/registry", "tukos/widgetUtils", "tukos/_TukosLayoutMixin", "tukos/_ObjectPaneMixin", "tukos/widgets/WidgetsHider"], 
    function(declare, lang, when, ContentPane, BorderContainer, registry, wutils, _TukosLayoutMixin,  _ObjectPaneMixin, WidgetsHider){
    return declare([BorderContainer, _TukosLayoutMixin, _ObjectPaneMixin], {

        postCreate: function(){
            this.inherited(arguments);
            this.widgetType = "ObjectPane";
            this.widgetsName = [];
            this.customization = {};
            var dataPane = new ContentPane({region: "center", 'class': "centerPanel", style: "padding: 0px;  overflow: auto; width: 100%; height: 100%; "}, dojo.doc.createElement("div"));
            var dataTable = this.tableLayout(this.dataLayout, dataPane, lang.hitch(wutils, wutils.setWatchers));
            this.addChild(dataPane);
            var actionPane = new ContentPane({region: "top", 'class': "edgePanel", style: "padding: 0px; overflow: auto;"},  dojo.doc.createElement("div"));
            var actionTable = this.tableLayout(this.actionLayout, actionPane);
            this.addChild(actionPane);
            if (this.summaryLayout != undefined){
                var summaryPane = new ContentPane({region: "bottom", 'class': "edgePanel", style: "padding: 0px; overflow: auto;"},  dojo.doc.createElement("div"));
                var summaryTable = this.tableLayout(this.summaryLayout, summaryPane);
                this.addChild(summaryPane);
            }
            this.changedWidgets = {};
            this.userChangedWidgets = {};
            if (this.forceMarkIfChanged){
                this.markIfChanged = true;
                this.forceMarkIfChanged = false;
            }else{
                this.markIfChanged = false;
            }
            this.watchOnChange = true;
            this.watchContext = 'server';
            this.onInstantiated(lang.hitch(this, function(){
                var widgetsHider = new WidgetsHider({form: this}, dojo.doc.createElement("div"));
                actionPane.addChild(widgetsHider);
                if (this.data && this.data.value && !this.data.value.id){
                    this.markIfChanged = true;
                }
                when (this.setWidgets(this.data), lang.hitch(this, function(result){
                    if (this.onOpenAction){
                        this.openAction(this.onOpenAction);
                    }
                    this.markIfChanged = true;
                    this.watchContext = 'user';
                    this.setUserContextPaths(); 
                }));
            }));
            this.onClose = function(){
                if (this.hasChanged()){
                    return confirm("Some fields have been modified on the tab. Are you sure you want to close it ?");
                }else{
                    return true;
                }
            }
        },

        setUserContextPaths: function(){
            var userContextWidget = registry.byId('tukos_userContextcontextid');
            if (userContextWidget){
                userContextWidget.set('paths',  this.contextPaths);
            }
        }
    }); 
});
