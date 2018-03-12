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
            "dojo/when",
	"dijit/TooltipDialog",
	"dijit/form/TextBox",
	"dijit/form/ComboBox",
	"dijit/form/Button",
	"dijit/form/DropDownButton",
	"dojo/_base/declare",
	"dojo/string",
            "tukos/PageManager",
            "tukos/utils", "tukos/hiutils",
	"dojo/i18n!tukos/nls/messages"
], function(dojo, dijit, dojox, arrayUtil, domAttr, _Plugin, registry, range, lang, on, when, TooltipDialog, TextBox, ComboBox, Button, DropDownButton, declare, string, Pmg, utils, hiutils, messages) {

    var TemplateProcess = dojo.declare(_Plugin, {
        iconClassPrefix: "dijitAdditionalEditorIcon",
    
        _dialogTemplate:
            " <table role='presentation'>" +
            "<tr><td><button dojoType='dijit.form.Button' type='submit' id='${id}_processButton'>${process}</button><button dojoType='dijit.form.Button' type='button' id='${id}_cancelButton'>${cancel}</button>" + 
            "</td></tr></table>",
    
        _initButton: function(){
            // summary:
            //		Override _Plugin._initButton() to initialize DropDownButton and TooltipDialog.
            var _this = this;
    
            // Build the dropdown dialog we'll use for the button
            var dropDown = (this.dropDown = new dijit.TooltipDialog({
                title: messages.templateProcessor,
                execute: dojo.hitch(this, "processTemplate"),
                onOpen: function(){
                    _this._onOpenDialog();
                    dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                },
                onClose: function(){
                    setTimeout(dojo.hitch(_this, "_onCloseDialog"),0);
                }
            }));
    
            this.button = new dijit.form.DropDownButton({
                label: messages.templateProcessor,
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "TemplateProcess",
                tabIndex: "-1",
                dropDown: this.dropDown
            });
            this._uniqueId = dijit.getUniqueId(this.editor.id);

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

        processTemplate: function(args){
            this._onCloseDialog();
            var body = this.editor.document.getElementById('dijitEditorBody');
            when (hiutils.processTemplate(body.innerHTML, {'@': this.editor.pane}), function(newContent){
                body.innerHTML = newContent;
            });
        },

        _onCloseDialog: function(){
            if(this.editor.focused){
                this.editor.focus();
            }
        },
    
        _onOpenDialog: function(){
            var warning;
            this.dropDown.set('content', this.dropDown.title +
                "<div style='border-bottom: 1px black solid;padding-bottom:2pt;margin-bottom:4pt'></div>" +
                "<div>" + messages.willReplaceTemplate + "</div>" +
                string.substitute(this._dialogTemplate, {id: this._uniqueId, process: messages.process, choice: messages.transform, cancel: messages.cancel})
            );
            this.connect(registry.byId(this._uniqueId + "_cancelButton"), "onClick", function(){
                this.dropDown.onClose();
            });
        }
    });

    _Plugin.registry["TemplateProcess"] = function(){
        return new TemplateProcess({command: "processTemplate"});
    };

    return TemplateProcess;

});
