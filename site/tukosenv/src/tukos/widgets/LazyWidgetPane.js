define (["dojo/_base/declare", "dojo/on", "dijit/layout/ContentPane", "dijit/form/Button", "dojo/domReady!"], 
    function(declare, on, ContentPane, Button){
    return declare(ContentPane, {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;    
            self.lazyCreate = function(){
                if (self.theWidget == undefined){
                    self.widgetArgs.id = self.pane.id + self.widgetArgs.id;
                    self.widgetArgs.paneId = self.paneId;
                    self.theWidget = new self.widgetClass(self.widgetArgs, dojo.doc.createElement("div"));
                    self.set('content', self.postClickContent);
                    self.resetButton = new Button(self.resetButtonArgs, dojo.doc.createElement("div"));
                    self.addChild(self.resetButton);
                    on (self.resetButton, "click", function(evt){
                        self.theWidget.destroy();
                        self.theWidget = new self.widgetClass(self.widgetArgs, dojo.doc.createElement("div"));
                        self.addChild(self.theWidget);
                    });
                    self.addChild(self.theWidget);
                }
            }
            on(this.domNode, "click", function(evt){
                self.lazyCreate();
            });
        },
        setPaths: function(paths){
            this.lazyCreate();
            this.theTree.set('paths', paths);
        }

    }); 
});
