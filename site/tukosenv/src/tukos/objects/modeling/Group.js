"use strict";
define(["dojo/_base/declare", "dojo/json5", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/objects/modeling/Element", "tukos/objects/modeling/rheologies/rheologiesLoader",  "tukos/objects/modeling/isoparametricElementUtils", 
		"tukos/utils", "tukos/maths/utils"], 
function(declare, JSON5, BaseClass, modeling, Element, rheologiesLoader, isoUtils, utils, mathUtils){
	return declare([BaseClass], {

		constructor: function constructor(args){
			this.inherited(constructor, arguments);
			const self = this;
			this.refElts = {};
			this.dimension = this.dimension || this.global.dimension;
			this.ndof = this.ndof || this.global.ndof;
			this.globalDirections = this.globalDirections || utils.vector(this.dimension, (v, i) => i);
			this.globalDofs = this.globalDofs || utils.vector(this.ndof, (v, i) => i);
			if ((this.global.properties || {}).gravityDirection){
				this.gravityDirection = [];
				this.globalDirections.forEach(function(direction){
					self.gravityDirection.push(self.global.properties.gravityDirection[direction]);
				})
			}
			this.refEltsDimension = modeling.refEltsDimensions[this.elementType] || this.dimension;
			this.integrationOrder = this.integrationOrder ? JSON5.parse(this.integrationOrder) : 2;
			if (Number.isInteger(this.integrationOrder)){
				this.integrationOrder = utils.vector(this.refEltsDimension, () => this.integrationOrder);
			}
			const rheologyClass = rheologiesLoader.module(this.problemType, this.rheologyType, this.elementType);
			this.material = new rheologyClass(this.materialProperties);
			this.material.group = this;
			if (this.material){
				this.cSky = this.material.cSky(this.refEltsDimension);
				if (this.properties){
					this.properties = JSON5.parse(this.properties);
					if (this.properties.rhs === 'gravity' && !this.gravityDirection){
						//Pmg.addFeedback(Pmg.message('grouprhsgravitynoglobalgravitydirection'), null, null, true);
					}
				}
				this.rhs = this.material.rhs(this);
			}
			this.integrationPoints = mathUtils.gaussPointsAndWeights(this.integrationOrder);
			for (let e = 0; e < this.elements.length; e++){
				const element = this.elements[e], key = JSON.stringify(element.optionalNodes);
				if (!this.refElts[key]){
					this.refElts[key] = this.initializeRefElt(element);
				}
				element.refElt = this.refElts[key];
				element.group = this;
				this.elements[e] = new Element(element);
			}
		},
		initializeRefElt: function(element){
			const refElt = [];
			this.integrationPoints.points.forEach(function(rst){
				refElt.push(isoUtils.rstReferenceInformation(rst, element.optionalNodes));
			});
			return refElt;
		}
	});
});
