define (["dojo/_base/declare", "dojo/_base/lang",  "dojo/on", "dojo/mouse",
         "dijit/form/TextBox", "dijit/form/Button", "dijit/TooltipDialog", "dijit/popup", "dijit/layout/ContentPane", "dijit/Menu", "dijit/MenuItem",
         "dijit/registry", "tukos/TukosDgrid", "tukos/dstore/MemoryTreeObjects", "dgrid/extensions/DnD", "tukos/_GridEditMixin",  "tukos/DialogConfirm", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, mouse, TextBox, Button, TooltipDialog, popup, ContentPane, Menu, MenuItem, registry, TukosDgrid, MemoryTreeObjects, DnD, _GridEditMixin, DialogConfirm, messages){
    var widget = declare([TukosDgrid, DnD, _GridEditMixin], {

        constructor: function(args){
            args.store = new MemoryTreeObjects(args.storeArgs);
            args.collection = args.store.getRootCollection();
        },

        postCreate: function(){
            this.inherited(arguments);
            this.dndSource.getObject = this.getObject;
            this.dndSource.onDropInternal = this.onDropInternal;
            this.dndSource.onDropExternal = this.onDropExternal;
            if (!this.disabled){
                var self = this;
                var addedItems = [
                        {atts: {label: messages.insertrowbefore  ,   onClick: function(evt){self.addRow('before')}}}, 
                        {atts: {label: messages.addrowatend    ,   onClick: function(evt){self.addRow('append')}}}, 
                        {atts: {label: messages.copyrow,   onClick: function(evt){self.copyRow(evt)}}}, 
                        {atts: {label:messages.deleterow,   onClick: function(evt){self.deleteRow()}}}
                ];
                this.contextMenuItems.row = this.contextMenuItems.row.concat(addedItems);
                this.contextMenuItems.idCol = this.contextMenuItems.idCol.concat(addedItems);
                this.contextMenuItems.header.push({atts: {label: messages.addrowatend   ,   onClick: function(evt){self.addRow('append')}}});
                if (this.columnsEdit){
                    this.contextMenuItems.header.push({atts: {label: messages.insertcolumn, onClick: function(evt){self.addColumn()}}});
                    this.contextMenuItems.header.push({atts: {label: messages.deletecolumn, onClick: function(evt){self.deleteColumn()}}});
                }
            }
            this.revert();//Necessary for the children rows expansion / collapse to work (!)
        }

    }); 
/*    
    widget.loadDependingWidgets = function(Widget, atts){
    	return _GridEditMixin.loadDependingWidgets(Widget, atts);
    }
*/    
    return widget;
});
