define([
	"dojo",
	"dijit",
	"dojox",
	"dijit/_editor/_Plugin",
	"dijit/form/DropDownButton",
	"dojo/_base/connect",
	"dojo/_base/declare",
	"dojo/i18n",
	"tukos/widgets/editor/plugins/_SmileyPalette",
	"dojox/html/format",
	"dojo/i18n!dojox/editor/plugins/nls/Smiley"
], function(dojo, dijit, dojox, _Plugin) {

dojo.experimental("dojox.editor.plugins.Smiley");

var Smiley = dojo.declare("dojox.editor.plugins.Smiley", _Plugin, {
	// summary:
	//		This plugin allows the user to select from emoticons or "smileys"
	//		to insert at the current cursor position.
	// description:
	//		The commands provided by this plugin are:
	//
	//		- smiley - inserts the selected emoticon

	// iconClassPrefix: [const] String
	//		The CSS class name for the button node is formed from `iconClassPrefix` and `command`
	iconClassPrefix: "dijitAdditionalEditorIcon",

	// emoticonMarker:
	//		a marker for emoticon wrap like [:-)] for regexp convienent
	//		when a message which contains an emoticon stored in a database or view source, this marker include also
	//		but when user enter an emoticon by key board, user don't need to enter this marker.
	//		also emoticon definition character set can not contains this marker
	emoticonMarker: '[]',

	emoticonImageClass: 'dojoEditorEmoticon',

	_initButton: function(){
		this.dropDown = new dojox.editor.plugins._SmileyPalette();
		this.connect(this.dropDown, "onChange", function(ascii){
			this.button.closeDropDown();
			this.editor.focus();
			//
			//ascii = this.emoticonMarker.charAt(0) + ascii + this.emoticonMarker.charAt(1);
			this.editor.execCommand("inserthtml", '&#' + ascii);
		});
		this.i18n = dojo.i18n.getLocalization("dojox.editor.plugins", "Smiley");
		this.button = new dijit.form.DropDownButton({
			label: '&#128522',
			showLabel: true,
			iconClass: this.iconClassPrefix + " " + this.iconClassPrefix + "Smiley",
			tabIndex: "-1",
			dropDown: this.dropDown
		});
		//this.emoticonImageRegexp = new RegExp("class=(\"|\')" + this.emoticonImageClass + "(\"|\')");
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
		
		if(dojo.isFF){
			// This is a workaround for a really odd Firefox bug with
			// leaving behind phantom cursors when deleting smiley images.
			// See: #13299
			var deleteHandler = dojo.hitch(this, function(){
				var editor = this.editor;
				// have to use timers here because the event has to happen
				// (bubble), then we can poke the dom.
				setTimeout(function(){
					if(editor.editNode){
						dojo.style(editor.editNode, "opacity", "0.99");
						// Allow it to apply, then undo it to trigger cleanup of those
						// phantoms.
						setTimeout(function(){if(editor.editNode) { dojo.style(editor.editNode, "opacity", "");} }, 0);
					}
				}, 0);
				return true;
			})
			this.editor.onLoadDeferred.addCallback(dojo.hitch(this, function(){
				this.editor.addKeyHandler(dojo.keys.DELETE, false, false, deleteHandler);
				this.editor.addKeyHandler(dojo.keys.BACKSPACE, false, false, deleteHandler);
			}));
		}
	}
});

// Register this plugin.
dojo.subscribe(dijit._scopeName + ".Editor.getPlugin",null,function(o){
	if(o.plugin){ return; }
	var name = o.args.name.toLowerCase();
	if(name === "smiley"){
		o.plugin = new Smiley();
	}
});

return Smiley;

});
