"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/utils"], 
function(declare, BaseClass, utils){
    return declare([BaseClass], {
    /*!
    * svgString is a dom-less svg generator. For this, it allows to build the svgTree of nested svg construct, which then it converts into a svg string that can be directly entered as innerHtml to a dom node, via function svgString();
    */
    constructor: function(atts){
        this.svgTree = {
            tag: "svg",
            atts: Object.assign({xlink: "http://www.w3.org/1999/xlink", xmlns: "http://www.w3.org/2000/svg"}, atts),            
            children: []
        };
        this.defs = this.addChild({tag: 'defs', children: []});
    },
    parentOrRoot: function(parent){
        return parent || this.svgTree;
    },
    children: function(parent){
        return this.parentOrRoot(parent).children;
    },
    delete: function(element){
        delete this.children(element.parent)[element.index];
    },
    addChild: function(description, parent){
        const parentOrRoot = this.parentOrRoot(parent), children = this.children(parentOrRoot), element = {...description, index: children.length, parent: parentOrRoot};
        children.push(element);
        return element;
    },
    svgString: function(){
        return this.svgSubstring(this.svgTree);
    },
    svgSubstring: function(parent){
        return `<${parent.tag} ${this.attsString(parent.atts)}>${this.childrenString(parent.children)}</${parent.tag}>`;
    },
	transformAtt: function(transformAtt){
		let transformString = '';
		utils.forEach(transformAtt, function(params, name){
			if (Array.isArray(params)){
				transformString += `${name}(${params.join(' ')})`;
			}else{
				transformString +=  `${name}(${params})`;
			}
		});
		return transformString;
	},
    attsString: function(atts){
        let result = '';
		if (atts){
			if (atts.transform && typeof atts.transform !== "string"){
				atts.transform = this.transformAtt(atts.transform);
			}
			utils.forEach(atts, function(att, target){
			    result += ` ${target}="${att}" `;
			});
		}
        return result;
    },
    childrenString: function(children){
        let self = this, result = '';
		if (typeof children === 'string'){
            return children;
        }
        children && utils.forEach(children, function(child){
            result += typeof child === 'string' ? child : `${self.svgSubstring(child)}`;
        });
        return result;
    },
    group: function(atts, children, parent){
        return this.addChild({tag: 'g', atts: atts || [], children: children || []}, parent);
    },
    text: function(atts, textOrChildren, parent){
        return this.addChild({tag: 'text', atts: atts || [], children: textOrChildren}, parent);
    },
	gradient: function(gradientType, atts, children, parent){
	    return this.addChild({tag: gradientType, atts: atts.id ? atts : Object.assign({id: utils.uniqueId()}, atts), children: children || []}, parent || this.defs);
	},
    linearGradient: function(atts, children, parent){
        return this.gradient('LinearGradient', atts,  children, parent);
    },
    radialGradient: function(atts, children, parent){
		return this.gradient('RadialGradient', atts,  children, parent);
    },
    urlId: function(element){
        return `#${element.atts.id}`;
    },
    path: function(pathString, atts, parent){
        return this.addChild({tag: 'path', atts: {d: pathString, ...atts}}, parent);
    },
	use: function(atts, parent){
		return this.addChild({tag: 'use', atts: atts}, parent);
	},
    toFront: function(element, targetParent){
        this.addChild(element, targetParent || element.parent);
        this.delete(element);
    },
  swap: function(element1, element2){
    [element1.parent.children[element1.index], element2.parent.children[element2.index]] = [element2.parent.children[element2.index], element1.parent.children[element1.index]];
    [element1.index, element1.parent, element2.index, element2.parent] = [element2.index, element2.parent, element1.index, element1.parent];
  },
  sibling: function(element, delta){
    const children = element.parent.children, keys = Object.keys(children), siblingIndex = keys[keys.indexOf(element.index) + delta];
    return children[siblingIndex];
  },
  next: function(element){
    return this.sibling(element, 1);
  },
  previous: function(element){
    return this.sibling(element, -1);
  },
  swapSibling: function(element, delta){
    const sibling = this.sibling(element, delta);
    if (sibling){
      this.swap(element, sibling);
      return true;
    }else{
      return false;
    }
  },
  swapNext: function(element){
      return this.swapSibling(element, 1);
  },
  swapPrevious: function(element){
      return this.swapSibling(element, -1);
  }
  });
});