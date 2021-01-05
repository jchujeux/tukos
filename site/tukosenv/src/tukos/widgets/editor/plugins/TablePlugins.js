define(["dojo/_base/declare", "dojo/_base/array", "dojo/_base/lang", "dojo/_base/Color", "dojo/aspect", "dojo/ready", "dojo/dom-construct",	"dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/keys", "dijit/_editor/_Plugin",
	"dijit/_WidgetBase", "tukos/expressions", "tukos/PageManager"], function(declare, array, lang, Color, aspect, ready, dct, domAttr, domStyle, dcl, keys, _Plugin, _WidgetBase, expressions, Pmg) {

    dojo.experimental("dojox.editor.plugins.TablePlugins");

    var tableAtts = ['backgroundColor', 'borderColor', 'pageBreakInside', 'display', 'textAlign', 'width', 'border', 'cellPadding', 'cellSpacing'],
    	cellAtts = tableAtts.concat('verticalAlign');
    var TableHandler = declare(_Plugin, {
        // summary:
        //  A global object that handles common tasks for all the plugins. Since there are several plugins that are all calling common methods, it's preferable that they call a centralized location
        // that either has a set variable or a timeout to only repeat code-heavy calls when necessary.
        //
	tablesConnected:false, currentlyAvailable: false, alwaysAvailable:false, availableCurrentlySet:false, initialized:false, tableData: null, editorDomNode: null, undoEnabled: true,  refCount: 0,
	
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
       if(this.initialized){
         return;
        }else{
            this.initialized = true;
            this.editor = editor;
            this.editor._tablePluginHandler = this;
            this.editor.previousOrNextCell = lang.hitch(this, this.previousOrNextCell);
            this.editor.rightOrLeftCell = lang.hitch(this, this.rightOrLeftCell);
            this.editor.upOrDownCell = lang.hitch(this, this.upOrDownCell);
            this.editor.onExpressionCellFocus = lang.hitch(this, this.onExpressionCellFocus);
            
            editor.onLoadDeferred.addCallback(dojo.hitch(this, function(){
                this.editorDomNode = this.editor.editNode || this.editor.iframe.document.body.firstChild;
                // RichText should have a mouseup connection to recognize drag-selections. Example would be selecting multiple table cells
                this._myListeners = [
                	aspect.after(this.editor, 'onDisplayChanged', lang.hitch(this, this.checkAvailable)),
                    dojo.connect(this.editor, "onBlur", this, "checkAvailable"),
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
                var tr, trs, td, tds=[], tbl, cols = 0, rCols = [], colIndex, tdIndex, trIndex, o;
                td = this.editor.getAncestorElement("td");
                if(td){ tr = td.parentNode; }
                tbl = this.editor.getAncestorElement("table");
                if(tbl){
                    trs = Array.apply(null, tbl.children[0].children);
                    trs.forEach(function(r, i){
                        var tdrs = [];
						tds = tds.concat(Array.apply(null, r.children));
						rCols[i] = r.children.length;
                    	if(tr==r){
							trIndex = i;
							tdrs = Array.apply(null, r.children);
							tdrs.forEach(function(c, j){
								cols += c.colSpan;
								if (td == c){
									colIndex = j;
								}
							})
						}
                    });
                    tds.forEach(function(d, i){
                        if(td==d){tdIndex = i;}
                    });
                    o = {
                        tbl:tbl,		// focused table
                        td:td,			// focused TD
                        tr:tr,			// focused TR
                        trs:trs,		// rows
                        tds:tds,		// cells
                        rows:trs.length,// row amount
                        cols:cols,		// column amount
						rCols: rCols,	// cols per row (account for colspan)
                        tdIndex:tdIndex,// index of focused cell
                        trIndex:trIndex,	// index of focused row
                        colIndex:colIndex
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
        //console.log('calling checkAvailable');
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
                //console.log('has table ancestor: ' + this.editor.hasAncestorElement("table"));
                //console.log('currentlyavailable: ' + this.currentlyAvailable);
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
    _prepareTable: function(tbl, forceIds){
        var trs = Array.apply(null, tbl.children[0].children), timeStamp = this.getTimeStamp(), self = this;
        if (forceIds || !trs[0].children[0].id || trs[0].children[0].id.split('_').length !== 4){
            trs.forEach(function(r, rIndex){
                var tds = Array.apply(null, r.children);
                tds.forEach(function(td, cIndex){
					td.id = self.getTdId(rIndex, cIndex, timeStamp);
                });
            });
        }
    },
    getTimeStamp: function(){
        return new Date().getTime(); 
    },
	getRowColStamp: function(td){
		var splittedId = td.id.split('_');
		return [Number(splittedId[1]), Number(splittedId[2]), splittedId[3]];
	},
	getTdId: function(r, c, timeStamp){
		return ['tdid', r, c, timeStamp].join('_');
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
            //this.cnKeyDn = dojo.connect(node, "onkeydown", this, "onKeyDown");
            //this.cnKeyUp = dojo.connect(node, "onkeyup", this, "onKeyUp");
            //this._myListeners.push(dojo.connect(node, "onkeypress", this, "onKeyUp"));
            this._prepareTable(this.editor.getAncestorElement('table'));
        }
    },
	
    disconnectTableKeys: function(){
        //dojo.disconnect(this.cnKeyDn);
        //dojo.disconnect(this.cnKeyUp);
        this.tablesConnected = false;
    },
    previousOrNextCell: function(evt){
		this.rightOrLeft(evt, editor.shiftKeyDown(evt) ? false : true); 
    },
    rightOrLeftCell: function(evt, right){
		  var editor = this.editor, selectedElement = editor.selection.getSelectedElement();
		  if (selectedElement && selectedElement.tagName === 'TD'){
			var previousOrNext = right ? 'nextElementSibling' : 'previousElementSibling', nextSelected = selectedElement[previousOrNext];
			if (!nextSelected){
				var newRow = selectedElement.parentNode[previousOrNext];
				if (newRow){
					editor.selectElement(newRow.children[right ? 0 : newRow.children.length - 1]);
				}else{
					Pmg.addFeedback(Pmg.message(right ? 'endoftable' : 'beginningoftable'), '', true);
				}
			}else{
				editor.selectElement(nextSelected);
			}
			return true;
		  }
		  return false;
  },
  upOrDownCell: function(evt, down){
	  var editor = this.editor, selectedElement = editor.selection.getSelectedElement();
	  if (selectedElement && selectedElement.tagName === 'TD'){
		var rcs = this.getRowColStamp(selectedElement), sRow = rcs[0], sCol = rcs[1], stamp = rcs[2], tableInfo = this.getTableInfo(), cols = tableInfo.cols, rCols = tableInfo.rCols, rows = tableInfo.rows;
		  if (down){
			  if (sRow+1 === rows){
				  if (sCol+1 === cols){
					  Pmg.addFeedback(Pmg.message('end of table'), '', true);
				  }else{
					  tRow = 0;
					  tCol = Math.min(sCol + 1, rCols[tRow]);
				  }
			  }else{
				  tRow = sRow + 1;
				  tCol = Math.min(sCol, rCols[tRow]-1);
			  }
		  }else{
			  if (sRow === 0){
				  if (sCol === 0){
					  Pmg.addFeedback(Pmg.message('beginning of table'), '', true);
				  }else{
					  tRow = rows-1;
					  tCol = Math.min(sCol - 1, rCols[tRow]);
				  }
			  }else{
				  tRow = sRow -1;
				  tCol = Math.min(sCol, rCols[tRow]-1);
			  }
		  }
		  editor.selectElement(editor.byId(this.getTdId(tRow, tCol, stamp)));
		  return true;
	  }
	  return false;
  	},
  	onExpressionCellFocus: function(evt){
		var editor = this.editor;
  		if (!editor.isCommandKey(evt)){
    		var selectedElement = this.editor.selection.getSelectedElement(), node;
	  		if (selectedElement && selectedElement.tagName && expressions.isExpression(node = selectedElement.tagName === 'TD' ? selectedElement.children[0]: selectedElement)){
  				evt.preventDefault();
  				evt.stopPropagation();
	  			this.editor.begEdit();
  				expressions.onClick(node, evt.key);
  			}	
		}
  		
  	},
/*
  	onKeyUp: function(evt){
        var key = evt.keyCode;
        switch (key){
	    	case keys.SHIFT	: 
	    	case keys.CTRL  :
	    	case keys.ALT   : 
	    		//evt.preventDefault();
	    	case keys.META  : 
	    		break;
	    	case keys.RIGHT_ARROW:
	    	case keys.LEFT_ARROW :
	    	case keys.DOWN_ARROW :
	    	case keys.UP_ARROW   : 
	    		this.onDisplayChanged(); break;
	    	case keys.TAB : 
	    		dojo.stopEvent(evt);//if (this.stopEvent){dojo.stopEvent(evt);}; break;
        }
    },
*/
    onDisplayChanged: function(){
        console.log('onDisplayChanged');
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
            this.label = this.editor.commands[this.command] = Pmg.message(this.command);
            this.inherited(arguments);
            delete this.command;
            this.onDisplayChanged(false);
        },
        
        getTableInfo: function(forceNewData){
            return this.editor._tablePluginHandler.getTableInfo(forceNewData);
        },
		getRowColStamp: function(td){
			return this.editor._tablePluginHandler.getRowColStamp(td);
		},
		getTdId: function(r, c, timeStamp){
			return this.editor._tablePluginHandler.getTdId(r, c, timeStamp);
		},
        prepareTable: function(){
            this.tableInfo = this.getTableInfo(true);
            this.table = this.tableInfo.tbl;
            if (this.pane){
            	this.pane.table = this.table;
            }
            if (this.getSelectedCells){
                this.selectedTds = this.getSelectedCells();
            }
        },
        onChangeWorksheetCheckBox: function(checked){
        	this.pane.getWidget('sheetName').set('hidden', !checked);
        	this.pane.resize();
        },
       getSelectedCells: function(){
            var cells = [];
            var tbl = this.getTableInfo().tbl;
            this.editor._tablePluginHandler._prepareTable(tbl);
            var e = this.editor;
            // Lets do this the way IE originally was (Looking up ids).  Walking the selection is inconsistent in the browsers (and painful), so going by ids is simpler.
            //var text = e._sCall("getSelectedHtml", [null]);
            var text = e.selection.getSelectedHtml([null]);
            var str = text.match(/id="*\w*"*/g);
            dojo.forEach(str, function(a){
                var id = a.substring(3, a.length);
                if(id.charAt(0) == "\"" && id.charAt(id.length - 1) == "\""){
                    id = id.substring(1, id.length - 1);
                }
                var node = e.byId(id);
                if(node && node.tagName.toLowerCase() == "td"){
                    cells.push(node);
                }
            }, this);
            if(!cells.length){// May just be in a cell (cursor point, or selection in a cell), so look upwards. for a cell container.
                var sel = dijit.range.getSelection(e.window);
                if(sel.rangeCount){
                    var r = sel.getRangeAt(0);
                    var node = r.startContainer;
                    while(node && node != e.editNode && node != e.document){
                        if(node.nodeType === 1){
                            var tg = node.tagName ? node.tagName.toLowerCase() : "";
                            if(tg === "td"){
                                return [node];
                            }
                        }
                        node = node.parentNode;
                    }
                }
            }
            return cells;
        },
	    copySelected: function(){
	    	this.lastSelectedTds = this.selectedTds;
	    },
	    emptySelected: function(){
	    	var table = this.table, isWorksheet = dcl.contains(table, 'tukosWorksheet');
	    	this.lastSelectedTds = [];
	    	this.selectedTds.forEach(function(td){
	    		if (isWorksheet){
	    			expressions.setExpression(td.children[0], '');
	    		}else{
	    			td.innerHTML = '';
	    		}
	    	});
	    },
	    pasteAtSelected: function(){
	    	var table = this.table, isWorksheet = dcl.contains(table, 'tukosWorksheet'), lastSelectedTds = this.lastSelectedTds, tableInfo = this.tableInfo, rCols = tableInfo.rCols, rows = tableInfo.rows;
	    	if (lastSelectedTds){
				var sRcs = this.getRowColStamp(lastSelectedTds[0]), sourceRow = sRcs[0], sourceCol = sRcs[1], stamp = sRcs[2],	selectedTds = this.selectedTds, tRcs = this.getRowColStamp(selectedTds[0]), targetRow = tRcs[0],
					targetCol = tRcs[1], rowOffset = targetRow - sourceRow, colOffset = targetCol - sourceCol, ltRcs = this.getRowColStamp(selectedTds[selectedTds.length-1]), lastTargetRow = ltRcs[0], lastTargetCol = ltRcs[1],
					self = this, e = this.editor, continuePaste = true, tRow, tCol;
	    		while(continuePaste){
	            	lastSelectedTds.forEach(function(sTd){
						var sRcs = 	self.getRowColStamp(sTd), tTd;
						tRow = sRcs[0] + rowOffset;
						tCol = sRcs[1] + colOffset;		
	        			if (tRow < rows && tCol < rCols[tRow]){
	        				tTd = e.byId(self.getTdId(tRow, tCol, stamp));
	        				if (isWorksheet){
	        					var sExpression = sTd.children[0], tExpression = tTd.children[0];
	        					expressions.copyExpression(sExpression, tExpression, rowOffset, colOffset);
	        				}else{
	            				tTd.innerHTML = sTd.innerHTML;
	        				}
	        				domAttr.set(tTd, 'style', domAttr.get(sTd, 'style'));
	        			}
	        		});
	            	if (tCol < lastTargetCol){
	            		colOffset = tCol - sourceCol + 1;
	            	}else if (tRow < lastTargetRow){
	            		colOffset = targetCol - sourceCol;
	            		rowOffset = tRow + 1 - sourceRow;
	            	}else{
	            		continuePaste = false;
	            	}
	        	}
	    	}else{
	    		Pmg.addFeedback(Pmg.message('nocellselected'), '', true);
	    	}
	    },
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
            var editor = this.editor, onChangeWorksheetCheckBox = this.onChangeWorksheetCheckBox;
            this.button.loadDropDown = function(callback){
                require(["tukos/widgets/editor/plugins/_EditorInsertTableDialog"], lang.hitch(this, function(InsertTableDialog){
                    var dropDown = (this.dropDown = new InsertTableDialog({editor: editor, button: this,isInsert: true, onChangeWorksheetCheckBox: onChangeWorksheetCheckBox, editableAtts: tableAtts}));
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
            var editor = this.editor, getTableInfo = this.getTableInfo, prepareTable = this.prepareTable, onChangeWorksheetCheckBox = this.onChangeWorksheetCheckBox;
            this.button.loadDropDown = function(callback){
                require(["tukos/widgets/editor/plugins/_EditorModifyTableDialog"], lang.hitch(this, function(ModifyTableDialog){
                    var dropDown = this.dropDown = new ModifyTableDialog({editor: editor, button: this, getTableInfo: getTableInfo, prepareTable: prepareTable, editableAtts: tableAtts});
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
            var editor = this.editor, getTableInfo = this.getTableInfo, getSelectedCells = this.getSelectedCells, prepareTable = this.prepareTable, onChangeWorksheetCheckBox = this.onChangeWorksheetCheckBox,
        		copySelected = this.copySelected, emptySelected = this.emptySelected, pasteAtSelected = this.pasteAtSelected;
            this.button.loadDropDown = function(callback){
                require(["tukos/widgets/editor/plugins/_EditorModifyTableSelectionDialog"], lang.hitch(this, function(ModifyTableSelectionDialog){
                    var dropDown = this.dropDown = new ModifyTableSelectionDialog({editor: editor, button: this, copySelected: copySelected, emptySelected: emptySelected, pasteAtSelected: pasteAtSelected, editableAtts: cellAtts});
                    dropDown = lang.mixin(dropDown, {
                    	getTableInfo: getTableInfo, getSelectedCells: getSelectedCells, prepareTable: prepareTable}); 
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
