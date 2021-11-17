define([
	"dojo",
	"dijit",
	"dojox",
	"dijit/_Widget",
	"dijit/_TemplatedMixin",
	"dijit/_PaletteMixin",
	"dojo/_base/connect",
	"dojo/_base/declare",
	"dojo/i18n",
	"dojo/i18n!dojox/editor/plugins/nls/Smiley"
], function(dojo, dijit, dojox, _Widget, _TemplatedMixin, _PaletteMixin) {

	dojo.experimental("dojox.editor.plugins._SmileyPalette");

	var Emoticon = dojo.declare("dojox.editor.plugins.Emoticon",
		null,
		{
			// summary:
			//		JS Object representing an emoticon

			constructor: function(/*String*/ id){
				// summary:
				//	 Create emoticon object from an id (like "smile")
				// value: String
				//		alias name 'smile', 'cool' ..
				this.id = id;
			},

			getValue: function(){
				// summary:
				//		Returns a emoticon string in ascii representation, ex: :-)
				return Emoticon.ascii[this.id];
			},

			fillCell: function(/*DOMNode*/cell, /*String*/ blankGif){
				dojo.place('<span>&#' + this.getValue() + '</span>', cell);
			}
	});

	Emoticon.ascii = {
		smile: "128522",
		laughing: "129315",
		wink: "128521",
		grin: "128513",
		cool: "128526",
		angry: "128544",
		half: "128527",
		eyebrow: "128559",
		frown: "128547",
		shy: "128524",
		goofy: "128540",
		oops: "128558",
		tongue: "128539",
		idea: "129321",
		yes: "129316",
		no: "129326",
		angel: "128519",
		crying: "128546",
		happy: "128514"
	};

	Emoticon.fromAscii = function(/*String*/str){
		// summary:
		//		Factory to create Emoticon object based on string like ":-)" rather than id like "smile"
		var ascii = Emoticon.ascii;
		for(var i in ascii){
			if(str == ascii[i]){
				return new Emoticon(i);
			}
		}
		return null;
	};

	var SmileyPalette = dojo.declare("dojox.editor.plugins._SmileyPalette", [_Widget, _TemplatedMixin, _PaletteMixin], {
		// summary:
		//		A keyboard accessible emoticon-picking widget (for inserting smiley characters)
		// description:
		//		Grid showing various emoticons.
		//		Can be used standalone, or as a popup.

		// templateString:
		//		The template of this widget.
		templateString:
			'<table class="dijitInline dijitEditorSmileyPalette dijitPaletteTable"' +
				' cellSpacing=0 cellPadding=0><tbody dojoAttachPoint="gridNode"></tbody></table>',

		baseClass: "dijitEditorSmileyPalette",

		_palette: [
			["smile", "laughing", "wink", "grin"],
			["cool", "angry", "half", "eyebrow"],
			["frown", "shy", "goofy", "oops"],
			["tongue", "idea", "angel", "happy"],
			["yes", "no", "crying", ""]
		],

		dyeClass: Emoticon,

		buildRendering: function(){
			// Instantiate the template, which makes a skeleton into which we'll insert a bunch of
			// <img> nodes
			this.inherited(arguments);

			var i18n = dojo.i18n.getLocalization("dojox.editor.plugins", "Smiley");

			// Generate hash from emoticon standard name (like "smile") to translation
			var emoticonI18n = {};
			for(var name in i18n){
				if(name.substr(0,8) == "emoticon"){
					emoticonI18n[name.substr(8).toLowerCase()] = i18n[name];
				}
			}
			this._preparePalette(
				this._palette,
				emoticonI18n
			);
		}
	});

	// For monkey-patching
	SmileyPalette.Emoticon = Emoticon;

	return SmileyPalette;
});
