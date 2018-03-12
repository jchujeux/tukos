define([
	"dojo/_base/declare",
	"dojo/_base/array",
    "dojo/_base/lang",
	"dojo/_base/Color",
	"dojo/aspect",
    "dojo/ready",
    "dojo/dom-construct",
	"dojo/dom-attr",
	"dojo/dom-style",
	"dijit/_editor/_Plugin",
	"dijit/_WidgetBase",
	"dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"
], function(declare, array, lang, Color, aspect, ready, dct, domAttr, domStyle, _Plugin, _WidgetBase, messages) {

    dojo.experimental("dojox.editor.plugins.TablePlugins");

    var TableHandler = declare(_Plugin, {
        // summary:
        //  A global object that handles common tasks for all the plugins. Since there are several plugins that are all calling common methods, it's preferable that they call a centralized location
        // that either has a set variable or a timeout to only repeat code-heavy calls when necessary.
        //
	tablesConnected:false, currentlyAvailable: false, alwaysAvailable:false, availableCurrentlySet:false, initialized:false, tableData: null, shiftKeyDown:false, editorDomNode: null, undoEnabled: true,  refCount: 0,
	
    doMixins: function(){
        dojo.mixin(this.editor,{
            getAncestorElement: function(tagName){
                return this._sCall("getAncestorElement", [tagName]);
            },
            hasAncestorElement: function(tagName){
                return this._sCall("hasAncestorElement", [tagName]);
            },
            selectElement: function(elem){
                this._sCall("selectElement", [elem]);
            },
            byId: function(id){
                return dojo.byId(id, this.document);
            },
            query: function(arg, scope, returnFirstOnly){
                // this shortcut is dubious - not sure scoping is necessary
                var ar = dojo.query(arg, scope || this.document);
                return (returnFirstOnly) ? ar[0] : ar;
            }
        });
    },

    initialize: function(editor){
        // Initialize the global handler upon a plugin's first instance of setEditor
        this.refCount++;
        editor.customUndo = true;
       if(this.initialized){
         return;
        }else{
            this.initialized = true;
            this.editor = editor;
            this.editor._tablePluginHandler = this;
            
            editor.onLoadDeferred.addCallback(dojo.hitch(this, function(){
                this.editorDomNode = this.editor.editNode || this.editor.iframe.document.body.firstChild;
                // RichText should have a mouseup connection to recognize drag-selections. Example would be selecting multiple table cells
                this._myListeners = [
                    dojo.connect(this.editorDomNode , "mouseup", this.editor, "onClick"),
                    dojo.connect(this.editor, "onDisplayChanged", this, "checkAvailable"),
                    dojo.connect(this.editor, "onBlur", this, "checkAvailable"),
                    dojo.connect(this.editor, "_saveSelection", this, function(){
                        // because on IE, the selection is lost when the iframe goes out of focus
                        this._savedTableInfo = this.getTableInfo();
                    }),
                    dojo.connect(this.editor, "_restoreSelection", this, function(){
                        delete this._savedTableInfo;
                    })
                ];
                this.doMixins();
                this.connectDraggable();
            }));
        }
    },
	
    getTableInfo: function(forceNewData){
        // Gets the table in focus. Collects info on the table - see return params
        if(this._savedTableInfo){
            return this._savedTableInfo;            // Avoid trying to query the table info when the iframe is blurred; doesn't work on IE.
        }else{
            if(forceNewData){ this._tempStoreTableData(false); }
            if(this.tableData){
                // tableData is set for a short amount of time, so that all plugins get the same return without doing the method over
                return this.tableData;
            }else{
                var tr, trs, td, tds, tbl, cols, tdIndex, trIndex, o;
                td = this.editor.getAncestorElement("td");
                if(td){ tr = td.parentNode; }
                tbl = this.editor.getAncestorElement("table");
                if(tbl){
                    tds = dojo.query("td", tbl);
                    tds.forEach(function(d, i){
                        if(td==d){tdIndex = i;}
                    });
                    trs = dojo.query("tr", tbl);
                    trs.forEach(function(r, i){
                        if(tr==r){trIndex = i;}
                    });
                    cols = tds.length/trs.length;           
                    o = {
                        tbl:tbl,		// focused table
                        td:td,			// focused TD
                        tr:tr,			// focused TR
                        trs:trs,		// rows
                        tds:tds,		// cells
                        rows:trs.length,// row amount
                        cols:cols,		// column amount
                        tdIndex:tdIndex,// index of focused cell
                        trIndex:trIndex,	// index of focused row
                        colIndex:tdIndex%cols
                    };
                }else{
                    o = {};                    // there's no table in focus.   Use {} not null so that this._savedTableInfo is non-null
                }
                this.tableData = o;
                this._tempStoreTableData(500);
                return this.tableData;
            }
        }
    },
	
    connectDraggable: function(){
        // Detects drag-n-drop in the editor (could probably be moved to there). Currently only checks if item dragged was a TABLE, and removes its align attr. DOES NOT WORK IN FF - it could - but FF's drag detection is a monster
        if(!dojo.isIE){
            return;
        }else{
            this.editorDomNode.ondragstart = dojo.hitch(this, "onDragStart");
            this.editorDomNode.ondragend = dojo.hitch(this, "onDragEnd");
        }
    },
    onDragStart: function(){
        var e = window.event;
        if(!e.srcElement.id){
            e.srcElement.id = "tbl_"+(new Date().getTime());
        }
    },

    onDragEnd: function(){
        // Detects that an object has been dragged into place. Currently, this code is only used for when a table is dragged and clears the "align" attribute, so that the table will look to be more in the place that the user expected.
        var e = window.event;
        var node = e.srcElement;
        var id = node.id;
        var doc = this.editor.document;
        if(node.tagName.toLowerCase()=="table"){
            setTimeout(function(){
                var node = dojo.byId(id, doc);
                dojo.removeAttr(node, "align");
            }, 100);
        }
    },

    checkAvailable: function(){
        // For table plugs Checking if a table or part of a table has focus so that Plugs can change their status
        if(this.availableCurrentlySet){// availableCurrentlySet is set for a short amount of time, so that all  plugins get the same return without doing the method over
            return this.currentlyAvailable;
        }else{
            if(!this.editor) {
                return false;
            }
            if(this.alwaysAvailable) {
                return true;
            }else{
                this.currentlyAvailable = this.editor.focused && (this._savedTableInfo ? this._savedTableInfo.tbl :
                    this.editor.hasAncestorElement("table"));
                if(this.currentlyAvailable){
                    this.connectTableKeys();
                }else{
                    this.disconnectTableKeys();
                }		
                this._tempAvailability(500);
                dojo.publish(this.editor.id + "_tablePlugins", [ this.currentlyAvailable ]);
                return this.currentlyAvailable;
            }
        }
    },
	
    _prepareTable: function(tbl){
        // For IE's sake, we are adding IDs to the TDs if none is there We go ahead and use it for other code for convenience
        var tds = this.editor.query("td", tbl);
        console.log("prep:", tds, tbl);
        //if(!tds[0].id){
            tds.forEach(function(td, i){
                if(!td.id){
                    td.id = "tdid"+i+this.getTimeStamp();
                }
            }, this);
        //}
        return tds;
    },
	
    getTimeStamp: function(){
        return new Date().getTime(); 
    },
	
    _tempStoreTableData: function(type){
        // caching or clearing table data, depending on the arg
        if(type===true){//store indefinitely
        }else if(type===false){// clear object
            this.tableData = null;
        }else if(type===undefined){
            console.warn("_tempStoreTableData must be passed an argument");
        }else{// type is a number/ms
            setTimeout(dojo.hitch(this, function(){
                this.tableData = null;
            }), type);
        }
    },
	
    _tempAvailability: function(type){
            // caching or clearing availability, depending on the arg
        if(type===true){//store indefinitely
            this.availableCurrentlySet = true;
        }else if(type===false){// clear object
            this.availableCurrentlySet = false;
        }else if(type===undefined){
            console.warn("_tempAvailability must be passed an argument");
        }else{// type is a number/ms
            this.availableCurrentlySet = true;
            setTimeout(dojo.hitch(this, function(){
                this.availableCurrentlySet = false;
            }), type);
        }
    },
	
    connectTableKeys: function(){
        // When a table is in focus, start detecting keys. Mainly checking for the TAB key so user can tab through a table (blocking the browser's desire to tab away from the editor completely)
        if(this.tablesConnected){
            return;
        }else{
            this.tablesConnected = true;
            var node = (this.editor.iframe) ? this.editor.document : this.editor.editNode;
            this.cnKeyDn = dojo.connect(node, "onkeydown", this, "onKeyDown");
            this.cnKeyUp = dojo.connect(node, "onkeyup", this, "onKeyUp");
            this._myListeners.push(dojo.connect(node, "onkeypress", this, "onKeyUp"));
        }
    },
	
    disconnectTableKeys: function(){
        dojo.disconnect(this.cnKeyDn);
        dojo.disconnect(this.cnKeyUp);
        this.tablesConnected = false;
    },
	
    onKeyDown: function(evt){
        var key = evt.keyCode;
        if(key == 16){ this.shiftKeyDown = true;}
        if(key == 9) {
            var o = this.getTableInfo();
            // modifying the o.tdIndex in the tableData directly, because we may save it
            // FIXME: tabTo is a global
            o.tdIndex = (this.shiftKeyDown) ? o.tdIndex-1 : tabTo = o.tdIndex+1;
            if(o.tdIndex>=0 && o.tdIndex<o.tds.length){
                this.editor.selectElement(o.tds[o.tdIndex]);
                // we know we are still within a table, so block the need to run the method
                this.currentlyAvailable = true;
                this._tempAvailability(true);
                this._tempStoreTableData(true);
                this.stopEvent = true;
            }else{ //tabbed out of table
                this.stopEvent = false;
                this.onDisplayChanged();
            }
            if(this.stopEvent) {
                dojo.stopEvent(evt);
            }
        }
    },
	
    onKeyUp: function(evt){
        var key = evt.keyCode;
        if(key == 16){ this.shiftKeyDown = false;}
        if(key == 37 || key == 38 || key == 39 || key == 40 ){
            // user can arrow or tab out of table - need to recheck
            this.onDisplayChanged();
        }
        if(key == 9 && this.stopEvent){ dojo.stopEvent(evt);}
    },
	
    onDisplayChanged: function(){
        this.currentlyAvailable = false;
        this._tempStoreTableData(false);
        this._tempAvailability(false);
        this.checkAvailable();
    },

    uninitialize: function(editor){
        // Function to handle cleaning up of connects and such.  It only finally destroys everything once all 'references' to it have gone.  As in all plugins that called init on it destroyed their refs in their cleanup calls.
        if(this.editor == editor){
            this.refCount--;
            if(!this.refCount && this.initialized){
                if(this.tablesConnected){
                    this.disconnectTableKeys();
                }
                this.initialized = false;
                dojo.forEach(this._myListeners, function(l){
                    dojo.disconnect(l);
                });
                delete this._myListeners;
                delete this.editor._tablePluginHandler;
                delete this.editor;
            }
            this.inherited(arguments);
        }
    }
});

var TablePlugins = declare("dojox.editor.plugins.TablePlugins", _Plugin, {
		//  A collection of Plugins for inserting and modifying tables in the Editor. See end of this document for all available plugs and dojox/editorPlugins/tests/editorTablePlugs.html for an example
 		
        iconClassPrefix: "editorIcon", useDefaultCommand: false, buttonClass: dijit.form.DropDownButton, commandName:"", label:"", alwaysAvailable:false, undoEnabled:true,
        
        onDisplayChanged: function(withinTable){
            // subscribed to from the global object's publish method
                        if(!this.alwaysAvailable){
                this.available = withinTable;
                this.button.set('disabled', !this.available);
            }
        },
        
        setEditor: function(editor){
            this.editor = editor;
            this.editor.customUndo = true;
            this.inherited(arguments);
            this._availableTopic = dojo.subscribe(this.editor.id + "_tablePlugins", this, "onDisplayChanged");
            this.onEditorLoaded();
        },
        onEditorLoaded: function(){
            if(!this.editor._tablePluginHandler){
                var tablePluginHandler = new TableHandler();
                tablePluginHandler.initialize(this.editor);
            }else{
                this.editor._tablePluginHandler.initialize(this.editor);
            }
        },
        
        selectTable: function(){
            // selects table that is in focus
            var o = this.getTableInfo();
            if(o && o.tbl){
                this.editor._sCall("selectElement", [o.tbl]);
            }
        },
        
        _initButton: function(){
            this.command = this.name;
            this.label = this.editor.commands[this.command] = messages[this.command + "Title"];//this._makeTitle(this.command);
            this.inherited(arguments);
            delete this.command;
            this.onDisplayChanged(false);
        },
        
        getTableInfo: function(forceNewData){
            return this.editor._tablePluginHandler.getTableInfo(forceNewData);
        },
/*
        _makeTitle: function(str){
            // Uses the commandName to get the localized Title
            this._strings = dojo.i18n.getLocalization("dojox.editor.plugins", "TableDialog");
            var title = this._strings[str+"Title"] || this._strings[str+"Label"] || str;
            return title;
        },
*/
        updateState: function(){
            // Over-ride for button state control for disabled to work.
            if(this.button){
                if((this.available || this.alwaysAvailable) && !this.get("disabled")){
                    this.button.set("disabled",false);
                }else{
                    this.button.set("disabled",true);
                }
            }
        },

        destroy: function(){
            this.inherited(arguments);
            dojo.unsubscribe(this._availableTopic);
            this.editor._tablePluginHandler.uninitialize(this.editor);
        }
    }
);

var InsertTable = declare("dojox.editor.plugins.InsertTable", TablePlugins, {
        alwaysAvailable: true,
        _initButton: function(){
            this.inherited(arguments);
            var editor = this.editor;
            this.button.loadDropDown = function(callback){
                require(["dojoFixes/dojox/editor/plugins/_EditorInsertTableDialog"], lang.hitch(this, function(InsertTableDialog){
                    var dropDown = (this.dropDown = new InsertTableDialog({editor: editor}));
                    ready(function(){
                        dropDown.startup();
                        callback();
                    });
                }));
            };
        }
});
var ModifyTable = declare("dojox.editor.plugins.ModifyTable", TablePlugins, {
        _initButton: function(){
            this.inherited(arguments);
            var editor = this.editor, getTableInfo = lang.hitch(this, this.getTableInfo);
            this.button.loadDropDown = function(callback){
                require(["dojoFixes/dojox/editor/plugins/_EditorModifyTableDialog"], lang.hitch(this, function(ModifyTableDialog){
                    var dropDown = this.dropDown = new ModifyTableDialog({editor: editor, getTableInfo: getTableInfo}); 
                    ready(function(){
                        dropDown.startup();
                        callback();
                    });
                }));
            };
        }
});

var ModifyTableSelection = declare("dojox.editor.plugins.ModifyTableSelection", TablePlugins, {

        _initButton: function(){
            this.inherited(arguments);
            var editor = this.editor, getTableInfo = lang.hitch(this, this.getTableInfo);
            this.button.loadDropDown = function(callback){
                require(["dojoFixes/dojox/editor/plugins/_EditorModifyTableSelectionDialog"], lang.hitch(this, function(ModifyTableSelectionDialog){
                    var dropDown = this.dropDown = new ModifyTableSelectionDialog({editor: editor, getTableInfo: getTableInfo}); 
                    ready(function(){
                        dropDown.startup();
                        callback();
                    });
                }));
            };
        }
});

_Plugin.registry["modifyTableSelection"] = function(args) {
	return new ModifyTableSelection(args);
};
_Plugin.registry["modifyTable"] = function(args) {
	return new ModifyTable(args);
};
_Plugin.registry["insertTable"] = function(args) {
	return new InsertTable(args);
};

return TablePlugins;
});
