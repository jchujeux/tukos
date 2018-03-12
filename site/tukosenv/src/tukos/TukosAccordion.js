/*
 *    Provides a tab  object and associated methods
 */
 define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/dom-construct", "dojo/dom-style", "dijit/layout/ContentPane", "dijit/registry", "tukos/TukosPane", "tukos/ObjectPane", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, ready, dct, domStyle, ContentPane, registry, TukosPane, ObjectPane){
    return declare(ContentPane, {
        constructor: function(args){
        },
    	postCreate: function (){    
    		this.widgetType = "TukosAccordion";
            this.inherited(arguments);
        },
        refresh: function(formContent){
            var self = this;
        	if (this.form){
            	this.form.destroyRecursive();
            }
            this.form = new ObjectPane(formContent);
            this.form.parent = this;
            ready(function(){
                self.addChild(self.form);            	
            })
        },
        onShow: function(){
        	console.log('TukosAccordion - onShow ');
        	if (!this.form){
        		//var self = this, theForm = this.form = this.formContent ? new ObjectPane(this.formContent) : new TukosPane(this.paneContent);
        		//var self = this, title = this.title, loadingId = title + 'loadingOverlay';
        		//this.set('title', '<div id="' + loadingId + '" class="loadingOverlay pageOverlay"><div class="loadingMessage">Loading ... </div></div>');
        		//this.set('title', 'Loading ...');
        		if (this.formContent){
                	this.accordionsManager.refresh('tab', [], false, this);
                }else{
                	var self = this;
                	this.form = new TukosPane(this.paneContent);
            		ready(function(){
                        //domStyle.set(loadingId, 'display', 'none');
            			self.addChild(self.form);               
                        self.resize();
                    })
                }
        	}
        },
        isObjectPane: function(){
        	return typeof(this.form.object) !== "undefined";
        }, 
        
        isAccordion: function(){
        	return true;
        },
            
        isTab: function(){
        	return false;
        }
    })
});
