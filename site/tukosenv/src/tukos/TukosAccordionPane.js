/*
 *    Provides a tab  object and associated methods
 */
 define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/dom-construct", "dojo/dom-style", "dijit/layout/ContentPane", "dijit/registry", "tukos/TukosPane", "tukos/ObjectPane", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, ready, dct, domStyle, ContentPane, registry, TukosPane, ObjectPane){
    return declare(ContentPane, {
        constructor: function(args){
        },
    	postCreate: function (){    
    		this.widgetType = "TukosAccordionPane";
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
        	console.log('TukosAccordionPane - onShow ');
        	this.createPane();
        },
        createPane: function(){
        	if (!this.form){
        		if (this.formContent){
                	this.accordionManager.refresh('Tab', [], false, this);
                }else{
                	var self = this;
                	this.form = new TukosPane(this.paneContent);
            		ready(function(){
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
