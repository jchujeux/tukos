define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojo/_base/Color", "dojo/colors", "tukos/utils", "dijit/ColorPalette", "dojo/i18n!dojo/nls/colors"], 
function(declare, lang, domStyle, Color, Colors, utils, ColorPalette, colorsLocale){

	var colorWidget = new ColorPalette(), colorWidgetDomNode = colorWidget.domNode, hexToName, nameToHex, maxDistance = 255*3, newColor = new Colors(), testColor = new Colors();
	
	buildHexName = function(){
		hexToName = {}, nameToHex = {};
		for(var i = 0; i < colorWidget._cells.length; i++){
			var cell = colorWidget._cells[i], hex = cell.dye.getValue(), name = cell.node.title;
			hexToName[hex] = name;
			nameToHex[name] = hex;
		}
	};
	closiestMatch = function(newHex){
		newColor.setColor(newHex);
		var newRgb = newColor.toRgb(), distance = maxDistance, closiestMatch;
		//utils.forEach(hexToName, function(testName, testHex){
		utils.forEach(Color.named, function(testRgb, testName){
			//testColor.setColor(testHex || '');
			testColor.setColor(testRgb);
			var testRgb = testColor.toRgb(), newDistance = Math.abs(testRgb[0] - newRgb[0]) + Math.abs(testRgb[1] - newRgb[1]) + Math.abs(testRgb[2] - newRgb[2]);
			if (newDistance < distance){
				distance = newDistance;
				closiestMatch = testName;
			}
		});
		return closiestMatch;
	}
	
	return {

        colorWidget: function(){
        	colorWidget.domNode = colorWidgetDomNode;
        	return colorWidget;
        	
        },
        
		format: function(hex){
        	if(!hex){
        		return hex;
        	}else if(!hexToName){
        		buildHexName();
        	}
        	if (hexToName[hex]){
        		return hexToName[hex];
        	}else{
        		var name = hexToName[hex] = colorsLocale[closiestMatch(hex)] +  ' (' + hex + ')';
        		nameToHex[name] = hex;
        		return name;
        	}
        },
 
        parse: function(name){
        	if(!name){
        		return name;
        	}else if(!nameToHex){
        		buildHexName();
        	}
        	return nameToHex[name];
        }
    };
});
