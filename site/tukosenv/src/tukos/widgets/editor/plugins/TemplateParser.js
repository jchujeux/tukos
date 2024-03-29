define([
	"dojo",
	"dijit",
	"dojox",
            "dojo/_base/array",	
            "dojo/dom-attr", // domAttr.get
	"dijit/_editor/_Plugin",
	"dijit/registry",
	"dijit/_editor/range",
	"dojo/_base/lang", // lang.delegate lang.hitch lang.trim
	"dojo/on",
	"dijit/TooltipDialog",
	"dijit/form/TextBox",
	"dijit/form/ComboBox",
	"dijit/form/Button",
	"dijit/form/DropDownButton",
	"dojo/_base/declare",
	"dojo/string",
            "tukos/PageManager",
            "tukos/utils",
	"dojo/i18n!tukos/nls/messages"
], function(dojo, dijit, dojox, arrayUtil, domAttr, _Plugin, registry, range, lang, on, TooltipDialog, TextBox, ComboBox, Button, DropDownButton, declare, string, Pmg, utils, messages) {

    var TemplateParser = dojo.declare(_Plugin, {
        // Allows selection of an expression from a choice list via context menu (html 5 then Firefox only)

        htmlContextMenuTemplate: "<menu id=\"choiceList_${listName}\" type=\"context\">${menuItems}</menu>",
        htmlMenuItemTemplate: "<menuitem label=\"${expression}\" onclick=\"getElementById(getElementById('dijitEditorBody').selectedChoiceSpan).innerHTML= '${expression} ' \"></menuitem>",
        htmlSpanTemplate: "<span id=\"${uniqueSpanId}\" contextmenu=\"choiceList_${listName}\" onmousedown=\"getElementById('dijitEditorBody').selectedChoiceSpan='${uniqueSpanId}'\" style=\"background-color:lightgrey;\">${selection}</span>",

        iconClassPrefix: "dijitAdditionalEditorIcon",
    
        _dialogTemplate:
            " <table role='presentation'>" +
            "<tr><td><label for='${id}_choiceList'>${listName}</label></td><td>${selectTemplate}</td></tr>" +
            "<tr><td><label for='${id}_0'>${choice} 1</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_0' name='0'></td></tr>" +
            "<tr><td><label for='${id}_1'>${choice} 2</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_1' name='1'></td></tr>" +
            "<tr><td><label for='${id}_2'>${choice} 3</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_2' name='2'></td></tr>" +
            "<tr><td><label for='${id}_3'>${choice} 4</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_3' name='3'></td></tr>" +
            "<tr><td><label for='${id}_4'>${choice} 5</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_4' name='4'></td></tr>" +
            "<tr><td><label for='${id}_4'>${choice} 5</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_5' name='5'></td></tr>" +
            "<tr><td><label for='${id}_4'>${choice} 5</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_6' name='6'></td></tr>" +
            "<tr><td><label for='${id}_4'>${choice} 5</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_7' name='7'></td></tr>" +
            "<tr><td><label for='${id}_4'>${choice} 5</label></td><td><input dojoType='dijit.form.TextBox' required='false' id='${id}_8' name='8'></td></tr>" +
            "<tr><td colspan='2'><button dojoType='dijit.form.Button' type='submit' id='${id}_insertButton'>${insert}</button><button dojoType='dijit.form.Button' type='button' id='${id}_saveButton'>${save}</button>" +
            "<button dojoType='dijit.form.Button' type='button' id='${id}_closeButton'>${close}</button>" + 
            "</td></tr></table>",
            
            _selectTemplate: "<select id='${id}_choiceList' name='choiceList' data-dojo-type='dijit.form.ComboBox'}>${otherOptions}</select>",
            
            _optionTemplate: "<option value='${value}'>${value}</option>",
    
        _initButton: function(){
            // summary:
            //		Override _Plugin._initButton() to initialize DropDownButton and TooltipDialog.
            var _this = this;
    
            // Build the dropdown dialog we'll use for the button
            var dropDown = (this.dropDown = new dijit.TooltipDialog({
                title: messages["choicelisteditor"],
                execute: dojo.hitch(this, "setValue"),
                onOpen: function(){
                    _this._onOpenDialog();
                    dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                },
                onClose: function(){
                    setTimeout(dojo.hitch(_this, "_onCloseDialog"),0);
                }
            }));
    
            this.button = new dijit.form.DropDownButton({
                label: messages["choicelisteditor"],
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "ChoiceList",
                tabIndex: "-1",
                dropDown: this.dropDown
            });
    
            this._uniqueId = dijit.getUniqueId(this.editor.id);
            this._previousSpanIds = [];
    
            dropDown.startup();
    
        },
        
        updateState: function(){
            // Over-ride for button state control for disabled to work.
            this.button.set("disabled", this.get("disabled"));
        },
    
        setEditor: function(editor){
            // Over-ride for the setting of the editor.
            this.editor = editor;
            this._initButton();
        },

        setValue: function(args){
            this._onCloseDialog();
            this.saveChoiceList();
            var listName = args.choiceList;
            if (this.currentSpan){
                this.currentSpan.contextMenu = 'choiceList_' + listName;
            }else{
                if (this.selectedHtml){
                    this.editor.execCommand('inserthtml', string.substitute(this.htmlSpanTemplate, {uniqueSpanId: utils.uniqueId(this._previousSpanIds) + 'Span',  listName: listName, selection: this.editor.selection.getSelectedHtml()}));
                }else{
                    Pmg.setFeedback(messages.emptyselection);
                }
            }
        },
        
        saveChoiceList: function(){
            var listName = registry.byId(this._uniqueId + '_choiceList').get('value');
            var menuItems = '';
            for (var i = 0; i<5; i++){
                var value = registry.byId(this._uniqueId + "_" + i).get('value');
                if (value){
                    menuItems += string.substitute(this.htmlMenuItemTemplate, {expression: value});
                }
            }
            var existingContextMenu = this.editor.document.getElementById('choiceList_' + listName);
            if (existingContextMenu){
                existingContextMenu.innerHTML = menuItems;
            }else{
                var root = this.editor.document, newMenu = root.createElement('menu');
                newMenu.id = 'choiceList_' + listName;
                newMenu.type = 'context';
                root.getElementById('dijitEditorBody').appendChild(newMenu);
                newMenu.innerHTML = menuItems;
             }
        },
    
        _onCloseDialog: function(){
            if(this.editor.focused){
                this.editor.focus();
            }
        },
    

        _onOpenDialog: function(){
            var parent = this.editor.selection.getParentElement();
            this.selectedHtml = this.editor.selection.getSelectedHtml();
            if (parent.tagName === 'SPAN' && parent.contextMenu){// is the span
                this.currentSpan = parent;
                this.currentContextMenu = parent.contextMenu;
                this.currentChoiceList = this.getChoiceList(this.currentContextMenu);
            }else{
                var span = this.getSelectedSpan(this.selectedHtml);
                if (span){
                    this.currentSpan = this.editor.document.getElementById(span);
                    this.currentContextMenu = this.currentSpan.contextMenu;
                    this.currentChoiceList = this.getChoiceList(this.currentContextMenu);
                }else{
                    this.currentSpan = this.currentContextMenu = this.currentChoiceList = undefined;
                }
            }
            this.existingChoiceListsIds = this.getExistingChoiceListsIds(this.editor.value);
            this.selectHtml = string.substitute(this._selectTemplate, {id: this._uniqueId, 'new': messages.anew, otherOptions: this.setOtherChoiceListOptions()});
            this.dropDown.set('content', this.dropDown.title +
                "<div style='border-bottom: 1px black solid;padding-bottom:2pt;margin-bottom:4pt'></div>" +
                string.substitute(this._dialogTemplate, {id: this._uniqueId, listName: 'nom de liste', choice: messages.choice, insert: messages.insert, save: Pmg.message('save'), selectTemplate: this.selectHtml, close: Pmg.message('close')})
            );
            this.currentListName = this.currentContextMenu ? this.getListName(this.currentContextMenu) : '';
            registry.byId(this._uniqueId + '_choiceList').set('value', this.currentListName);
            this.updateChoiceListFields(this.currentListName);
            this.connect(registry.byId(this._uniqueId + "_choiceList"), "onChange", function(newValue){
                this.updateChoiceListFields(newValue);
            });
            this.connect(registry.byId(this._uniqueId + "_closeButton"), "onClick", function(){
                this.dropDown.onClose();
            });
            this.connect(registry.byId(this._uniqueId + "_saveButton"), "onClick", lang.hitch(this, function(){
                this.saveChoiceList();
            }));
            this.connect(registry.byId(this._uniqueId + "_saveButton"), "onClick", lang.hitch(this, function(){
                this.saveChoiceList();
            }));
        },
        
        getSelectedSpan: function(selectedHtml){
            var result = /<span .*id="([2-9a-z]*Span)/g.exec(selectedHtml);
            return (result && result[1]) || undefined;
        },

        getChoiceList: function(contextMenu){
            var str = contextMenu.innerHTML, re = /<menuitem label="([^"]*)/g, choiceList = [], res;
            while (res = re.exec(str)){
                choiceList.push(res[1]);
            }
            return choiceList;
        },
        
        getListName: function(contextMenu){
            var result = /choiceList_(.*)/g.exec(contextMenu.id);
            return (result && result[1]) || undefined;
        },
        
        getExistingChoiceListsIds: function(content){
            var re = /<menu id="choiceList_([^"]*)/g, result = [], res;
            while (res = re.exec(content)){
                result.push(res[1]);
            }
            return result;
        },

        setOtherChoiceListOptions: function(){
            var result = '', choiceLists = this.existingChoiceListsIds;
            arrayUtil.forEach(this.existingChoiceListsIds, lang.hitch(this, function(item){
                result += string.substitute(this._optionTemplate, {value: item});
            }));
            return result;
        },
        
        updateChoiceListFields: function(newValue){
            var existingContextMenu = this.editor.document.getElementById('choiceList_' + newValue), i = 0;
            if (existingContextMenu){
                var choiceList = this.getChoiceList(existingContextMenu);
                for (var j in choiceList){
                    registry.byId(this._uniqueId + "_" + i).set('value', choiceList[j]);
                    i++;
                }
            }
            while (i <= 8){
                registry.byId(this._uniqueId + "_" + i).set('value', '');
                i++;
            }
        }
    });

    _Plugin.registry["TemplateParser"] = function(){
        return new TemplateParser({command: "parseTemplate"});
    };

    return ChoiceList;

});
