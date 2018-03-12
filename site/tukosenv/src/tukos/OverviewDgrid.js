/*
 *  Provides a grid overview capability, allowing to display contents of a tukos object table
 *      -> 'overview': used as read-only cells, selector allow to select specific actions on selected rows via the save button
 */
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/string", "dgrid/extensions/DnD", "tukos/PageManager", "tukos/TukosDgrid", "tukos/dstore/Request", 
         "dijit/form/TextBox", "dijit/form/Button", "dijit/TooltipDialog", "dijit/popup", "dijit/layout/ContentPane", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, string, DnD, PageManager, TukosDgrid, Request, TextBox, Button, TooltipDialog, popup, ContentPane, messages){
    return declare([TukosDgrid, DnD], {
        constructor: function(args){
            args.storeArgs.sortParam = args.storeArgs.sortParam || PageManager.getItem('sortParam');
            if (!args.storeArgs.target){
               args.storeArgs.object = args.object;
               args.storeArgs.target = PageManager.requestUrl(args.storeArgs);
            }
            args.store = new Request(args.storeArgs);
            args.store.userFilters = lang.hitch(this, this.userFilters);
            args.store.postFetchAction = lang.hitch(this, this.postFetchAction);
            args.collection = args.store.filter({contextpathid: args.form.tabContextId()});
            for (var i in args.columns){
                var field = args.columns[i]['field'];
                if (args.columns[i]['field'] && args.objectIdCols.indexOf(args.columns[i]['field']) >= 0){
                    args.columns[i]['renderCell'] = this.renderNamedId;
                }
            }
        },
        postCreate: function(){
            this.inherited(arguments);
            if (this.hasFilters){
            	this.showFilters();
            }
            this.dndSource.getObject = this.getObject;
            //this.dndSource.onDropInternal = this.onDropInternal;
            //this.dndSource.onDropExternal = this.onDropExternal;
            var grid = this;
            grid.modify = new Object({length: 0, values: new Object});
            
            var newColValue = function(){
                switch (grid.clickedCell.column.field){
                    case 'id':
                    case 'updator':
                    case 'updated':
                        break;
                    default:
                        var dialogContent = new ContentPane({'content': messages.entermodify + '<p>'});
                        var entryBox = new TextBox({placeHolder: messages.entertargetvalue});
                        if (grid.modify.values[grid.clickedCell.column.field] != undefined){
                            entryBox.set('value', grid.modify.values[grid.clickedCell.column.field]);
                        }
                        dialogContent.addChild(entryBox);
                        var enterButton = new Button({label: messages.define, onClick: function(){
                                if (grid.modify.values[grid.clickedCell.column.field] == undefined){
                                    grid.modify.length += 1;
                                }
                                grid.modify.values[grid.clickedCell.column.field] = entryBox.get('value');
                                popup.close(myDialog);
                            }
                        });
                        dialogContent.addChild(enterButton);
                        var unsetButton = new Button({label: messages.cancel, onClick: function(){
                                if (grid.modify.values[grid.clickedCell.column.field] != undefined){
                                    delete grid.modify.values[grid.clickedCell.column.field];
                                    grid.modify.length += -1;
                                }
                                popup.close(myDialog);
                            }
                        });
                        dialogContent.addChild(unsetButton);
                        var myDialog = new TooltipDialog({});                                                              
                        myDialog.set('content', dialogContent);
                        popup.open({
                            popup: myDialog,
                            around: grid.clickedCell.element
                        });
                        var none = 'none';
                }
            }

            this.contextMenuItems.header.push({atts: {label: "new target col value..."  , onClick: function(evt){newColValue()}}});
        },
        allowSelect: function(row){
            if (typeof row.id == 'undefined'){//is the header rather than a data row (?)
                return true;
            }else if (row.data.canEdit){
                return true;
            }else{
                return false;
            }
        },
        getObject: function(node){
            return this.grid.clickedRow.data;
        }, 
        
        postFetchAction: function(response){
        	if (response.summary){
        		this.form.setWidgets(response.summary);
        	}
        }
    }); 
});
