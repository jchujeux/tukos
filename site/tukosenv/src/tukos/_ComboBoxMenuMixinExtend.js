/*
 * Adds tooltip to store-driven dropdown menus
 */
define (["dijit/Tooltip"], 
    function(Tooltip){
    return {
		_createOption: function(/*Object*/ item, labelFunc){
			// summary:
			//		Creates an option to appear on the popup menu subclassed by
			//		`dijit/form/FilteringSelect`.
			var menuitem = this._createMenuItem();
			var labelObject = labelFunc(item);
			if(labelObject.html){
				menuitem.innerHTML = labelObject.label;
			}else{
				menuitem.appendChild(
					menuitem.ownerDocument.createTextNode(labelObject.label)
				);
			}
			if (item.tooltip){
				new Tooltip({connectId: [menuitem], label: item.tooltip});
			}
			// #3250: in blank options, assign a normal height
			if(menuitem.innerHTML == ""){
				menuitem.innerHTML = "&#160;";	// &nbsp;
			}
			return menuitem;
		}
    }
});
