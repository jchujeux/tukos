define([
	"dojo",
	"dijit",
	"dojox",
	"dojo/dom-attr", // domAttr.get
	"dijit/_editor/_Plugin",
	"dijit/registry",
	"dijit/_editor/range",
	"dojo/_base/lang", // lang.delegate lang.hitch lang.trim
	"dojo/on",
	"dojo/sniff", // has("ie")
	"dijit/TooltipDialog",
	"dijit/form/ValidationTextBox",
	"dijit/form/Select",
	"dijit/form/Button",
	"dijit/form/DropDownButton",
	"dojo/_base/declare",
	"dojo/string",
    "tukos/PageManager"
], function(dojo, dijit, dojox, domAttr, _Plugin, registry, range, lang, on, has, TooltipDialog, ValidationTextBox, Select, Button, DropDownButton, declare, string, Pmg) {

    var TukosLinkDialog = dojo.declare(_Plugin, {
        // summary:
        //		This plugin provides the basis for an insert anchor dialog for the
        //		dijit.Editor
        //
        // description:
        //		The command provided by this plugin is:
        //
        //		- insertAnchor
    
        // htmlTemplate: [protected] String
        //		String used for templating the HTML to insert at the desired point.
        htmlTemplate: "<a nohref class=\"dijitEditorPluginTukosLinkDialogStyle\" style=\"cursor:pointer;color:blue;text-decoration:underline\"" +
                      " tukosId=\"${tukosIdInput}\" tukosObject=\"${tukosObjectInput}\" tukosView=\"${tukosViewInput}\"" +
                      " onClick=\"event.stopImmediatePropagation();if (!getElementById('dijitEditorBody')){parent.tukos.Pmg.editorGotoTab({object: '${tukosObjectInput}', view: '${tukosViewInput}', query: {id: '${tukosIdInput}'}}, event);}\">${textInput}</a> &nbsp",
    
        // tag: [protected] String
        //		Tag used for the link type.
        tag: "a",
    
        // iconClassPrefix: [const] String
        //		The CSS class name for the button node icon.
        iconClassPrefix: "dijitAdditionalEditorIcon",
    
        // TukosLinkDialogTemplate: [private] String
        //		Template for contents of TooltipDialog to pick URL
        _template: [
            "<table role='presentation'>", 
            "<tr><td><label for='${id}_tukosIdInput'>id</label></td>",
                "<td><input dojoType='dijit.form.ValidationTextBox' required='false' " + "id='${id}_tukosIdInput' name='tukosIdInput' intermediateChanges='true'>",
            "</td></tr>",
            "<tr><td><label for='${id}_tukosObjectInput'>${object}</label></td>",
                "<td><input dojoType='dijit.form.ValidationTextBox' required='true' id='${id}_tukosObjectInput' " + "name='tukosObjectInput' intermediateChanges='true'>",
            "</td></tr>",
            "<tr><td><label for='${id}_tukosViewInput'>${view}</label></td>",
            "<td><select id='${id}_tukosViewInput' name='tukosViewInput' data-dojo-type='dijit.form.Select'>",
                "<option selected='selected' value='Edit'>${edit}</option>",
                "<option value='Overview'>${overview}</option>",
                "</select>",
            "</td></tr>",
            "<tr><td><label for='${id}_textInput'>${description}</label></td>",
                "<td><input dojoType='dijit.form.ValidationTextBox' required='true' id='${id}_textInput' " + "name='textInput' intermediateChanges='true'>",
            "</td></tr>",
            "<tr><td colspan='2'>",
            "<button dojoType='dijit.form.Button' type='submit' id='${id}_setButton'>${apply}</button>",
            "<button dojoType='dijit.form.Button' type='button' id='${id}_cancelButton'>${cancel}</button>",
            "</td></tr></table>"
        ].join(""),
    
        _initButton: function(){
            // summary:
            //		Override _Plugin._initButton() to initialize DropDownButton and TooltipDialog.
            var _this = this, messages = Pmg.messages(['createTukosLink', 'object', 'view', 'description', 'edit', 'overview', 'apply', 'cancel']);
    
            // Build the dropdown dialog we'll use for the button
            var dropDown = (this.dropDown = new dijit.TooltipDialog({
                title: messages["createTukosLink"],
                execute: dojo.hitch(this, "setValue"),
                onOpen: function(){
                    _this._onOpenDialog();
                    dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                },
                onCancel: function(){
                    setTimeout(dojo.hitch(_this, "_onCloseDialog"),0);
                }
            }));
    
            this.button = new dijit.form.DropDownButton({
                label: messages["createTukosLink"],
                showLabel: false,
                iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "TukosLinkDialog",
                tabIndex: "-1",
                dropDown: this.dropDown
            });
    
            messages.id = dijit.getUniqueId(this.editor.id);
            this._uniqueId = messages.id;
    
            this.dropDown.set('content', dropDown.title +
                "<div style='border-bottom: 1px black solid;padding-bottom:2pt;margin-bottom:4pt'></div>" +
                string.substitute(this._template, messages));
    
            dropDown.startup();
            this._tukosIdInput = registry.byId(this._uniqueId + "_tukosIdInput");
            this._tukosObjectInput = registry.byId(this._uniqueId + "_tukosObjectInput");
            this._tukosViewInput = registry.byId(this._uniqueId + "_tukosViewInput");
            this._textInput = registry.byId(this._uniqueId + "_textInput");
            this._setButton = registry.byId(this._uniqueId + "_setButton");
            this.connect(registry.byId(this._uniqueId + "_cancelButton"), "onClick", function(){
                this.dropDown.onCancel();
            });
    
            if(this._tukosIdInput){
                this.connect(this._tukosIdInput, "onChange", "_checkInput");
            }
            if(this._tukosObjectInput){
                this.connect(this._tukosObjectInput, "onChange", "_checkInput");
            }
            if(this._tukosViewInput){
                this.connect(this._tukosViewInput, "onChange", "_checkInput");
            }
            if(this._textInput){
                this.connect(this._textInput, "onChange", "_checkInput");
            }
    
            this._connectTagEvents();
        },
        
        updateState: function(){
            // summary:
            //		Over-ride for button state control for disabled to work.
            this.button.set("disabled", this.get("disabled"));
        },
    
        setEditor: function(editor){
            // summary:
            //		Over-ride for the setting of the editor.
            // editor: Object
            //		The editor to configure for this plugin to use.
            this.editor = editor;
            this._initButton();
        },
    
        _checkInput: function(){
            // summary:
            //		Function to check the input to the dialog is valid
            //		and enable/disable set button
            // tags:
            //		private
            var disable = true;
            if(this._tukosIdInput.isValid()){
                disable = false;
            }
            this._setButton.set("disabled", disable);
        },
    
        _connectTagEvents: function(){
            // summary:
            //		Over-ridable function that connects tag specific events.
            this.editor.onLoadDeferred.then(lang.hitch(this, function(){
                this.own(on(this.editor.editNode, "dblclick", lang.hitch(this, "_onDblClick")));
            }));
        },
    
    
        _checkValues: function(args){
            // summary:
            //		Function to check the values in args and 'fix' them up as needed.
            // args: Object
            //		Content being set.
            // tags:
            //		protected
            return args;
        },
    
        setValue: function(args){
            // summary:
            //		Callback from the dialog when user presses "set" button.
            // tags:
            //		private
    
            // TODO: prevent closing popup if the text is empty
            this._onCloseDialog();
            if(has("ie") < 9){ //see #4151
                var sel = rangeapi.getSelection(this.editor.window);
                var range = sel.getRangeAt(0);
                var a = range.endContainer;
                if(a.nodeType === 3){
                    // Text node, may be the link contents, so check parent.
                    // This plugin doesn't really support nested HTML elements
                    // in the link, it assumes all link content is text.
                    a = a.parentNode;
                }
                if(a && (a.nodeName && a.nodeName.toLowerCase() !== this.tag)){
                    // Still nothing, one last thing to try on IE, as it might be 'img'
                    // and thus considered a control.
                    a = this.editor.selection.getSelectedElement(this.tag);
                }
                if(a && (a.nodeName && a.nodeName.toLowerCase() === this.tag)){
                    // Okay, we do have a match.  IE, for some reason, sometimes pastes before
                    // instead of removing the targeted paste-over element, so we unlink the
                    // old one first.  If we do not the <a> tag remains, but it has no content,
                    // so isn't readily visible (but is wrong for the action).
                    if(this.editor.queryCommandEnabled("unlink")){
                        // Select all the link children, then unlink.  The following insert will
                        // then replace the selected text.
                        this.editor.selection.selectElementChildren(a);
                        this.editor.execCommand("unlink");
                    }
                }
            }
            // make sure values are properly escaped, etc.
            args = this._checkValues(args);
            this.editor.execCommand('inserthtml',
                string.substitute(this.htmlTemplate, args));
    
            // IE sometimes leaves a blank link, so we need to fix it up.
            // Go ahead and do this for everyone just to avoid blank links
            // in the page.
            //query("a", this.editor.document).forEach(function(a){
                //if(!a.innerHTML && !domAttr.has(a, "name")){
                    // Remove empty anchors that do not have "name" set.
                    // Empty ones with a name set could be a hidden hash
                    // anchor.
                    //a.parentNode.removeChild(a);
                //}
            //}, this);
        },
    
        _onCloseDialog: function(){
            // summary:
            //		Handler for close event on the dialog
    
            if(this.editor.focused){
                // put focus back in the edit area, unless the dialog closed because the user clicked somewhere else
                this.editor.focus();
            }
        },
    
        _getCurrentValues: function(a){
            // summary:
            //		Over-ride for getting the values to set in the dropdown.
            // a:
            //		The anchor/link to process for data for the dropdown.
            // tags:
            //		protected
            var tukosId, tukosObject, tukosView, text;
            if(a && a.tagName.toLowerCase() === "a" && dojo.attr(a, "tukosview")){
                tukosId = a.getAttribute('tukosId');
                tukosObject = a.getAttribute('tukosObject');
                tukosView = a.getAttribute('tukosView');
                text = a.textContent || a.innerText;
                this.editor.selection.selectElement(a, true);
            }else{
                    text = this.editor.selection.getSelectedText();
            }
            return {tukosIdInput: tukosId || '', tukosObjectInput: tukosObject || '', tukosViewInput: tukosView || '', textInput: text || ''}; //Object;
        },
    
        _onOpenDialog: function(){
            // summary:
            //		Handler for when the dialog is opened.
            //		If the caret is currently in a URL then populate the URL's info into the dialog.
            var a, b, fc;
            if(has("ie")){
                // IE, even IE10, is difficult to select the element in, using the range unified
                // API seems to work reasonably well.
                var sel = rangeapi.getSelection(this.editor.window);
                if(sel.rangeCount){
                    var range = sel.getRangeAt(0);
                    a = range.endContainer;
                    if(a.nodeType === 3){
                        // Text node, may be the link contents, so check parent.
                        // This plugin doesn't really support nested HTML elements
                        // in the link, it assumes all link content is text.
                        a = a.parentNode;
                    }
                    if(a && (a.nodeName && a.nodeName.toLowerCase() !== this.tag)){
                        // Still nothing, one last thing to try on IE, as it might be 'img'
                        // and thus considered a control.
                        a = this.editor.selection.getSelectedElement(this.tag);
                    }
                    if(!a || (a.nodeName && a.nodeName.toLowerCase() !== this.tag)){
                        // Try another lookup, IE's selection is just terrible.
                        b = this.editor.selection.getAncestorElement(this.tag);
                        if(b && (b.nodeName && b.nodeName.toLowerCase() == this.tag)){
                            // Looks like we found an A tag, use it and make sure just it is
                            // selected.
                            a = b;
                            this.editor.selection.selectElement(a);
                        }else if(range.startContainer === range.endContainer){
                            // STILL nothing.  Trying one more thing.  Lets look at the first child.
                            // It might be an anchor tag in a div by itself or the like.  If it is,
                            // we'll use it otherwise we give up.  The selection is not easily
                            // determinable to be on an existing anchor tag.
                            fc = range.startContainer.firstChild;
                            if(fc && (fc.nodeName && fc.nodeName.toLowerCase() == this.tag)){
                                a = fc;
                                this.editor.selection.selectElement(a);
                            }
                        }
                    }
                }
            }else{
                a = this.editor.selection.getAncestorElement(this.tag);
            }
            this.dropDown.reset();
            this._setButton.set("disabled", true);
            this.dropDown.set("value", this._getCurrentValues(a));
        },
    
        _onDblClick: function(e){
            // summary:
            //		Function to define a behavior on double clicks on the element
            //		type this dialog edits to select it and pop up the editor
            //		dialog.
            // e: Object
            //		The double-click event.
            // tags:
            //		protected.
            if(e && e.target){
                var t = e.target;
                var tg = t.tagName ? t.tagName.toLowerCase() : "";
                if(tg === this.tag && domAttr.get(t, "tukosview")){
                    var editor = this.editor;
    
                    this.editor.selection.selectElement(t);
                    editor.onDisplayChanged();
    
                    // Call onNormalizedDisplayChange() now, rather than on timer.
                    // On IE, when focus goes to the first <input> in the TooltipDialog, the editor loses it's selection.
                    // Later if onNormalizedDisplayChange() gets called via the timer it will disable the LinkDialog button
                    // (actually, all the toolbar buttons), at which point clicking the <input> will close the dialog,
                    // since (for unknown reasons) focus.js ignores disabled controls.
                    if(editor._updateTimer){
                        editor._updateTimer.remove();
                        delete editor._updateTimer;
                    }
                    editor.onNormalizedDisplayChanged();
    
                    var button = this.button;
                    setTimeout(function(){
                        // Focus shift outside the event handler.
                        // IE doesn't like focus changes in event handles.
                        button.set("disabled", false);
                        button.loadAndOpenDropDown().then(function(){
                            if(button.dropDown.focus){
                                button.dropDown.focus();
                            }
                        });
                    }, 10);
                }
            }
        }
    });
    
    // Register this plugin.
    _Plugin.registry["TukosLinkDialog"] = function(){
        return new TukosLinkDialog({command: "createTukosLink"});
    };

    return TukosLinkDialog;

});
