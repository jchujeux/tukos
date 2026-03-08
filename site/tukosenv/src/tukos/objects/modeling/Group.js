"use strict";
define(["dojo/_base/declare", "dojo/json5", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/objects/modeling/Element",  "tukos/objects/modeling/isoparametricElementUtils", "tukos/utils", "tukos/maths/utils", "tukos/PageManager"], 
function(declare, JSON5, BaseClass, modeling, Element, isoUtils, utils, mathUtils, Pmg){
	return declare([BaseClass], {
		/*dimension, // usually the same as global.dimension
		globalDirections, // usually globalDirection[] = i
		ndof, //: usually the same as unknowns
		globalDofs, //  ususally groupToGlobal[i] = i.
		problemType, //: the type of partial differential equation to solve
		    //diffusion => equation de Laplace, diffusion de la chaleur, mais aussi écoulement en milieux porerux
		    //solidmechanics => milieux déformables, équilibre, contraintes - déformations
		    //fluidmechanics: => tbd
		elementType, // the environmental restrictions that impact the elementy matrices and rhs, context: {type: 'truss'|'planestrain'|'planestress'|'planeflow'|..., properties: {'area': ...}}
		material,//  the material for the elements in the group. Optional. If absent, a material must be provided for each element
		rheologyType, // general | linearisotropic | linearorthotropic | rheological model name
		integrationOrder,//: integrationOrder[d] is the order of integration in the direction d for the reference element
		refEltsDimension,
		cSky,//: the C matrix in B(T).C.B, in skyline format, default value for lements and integration points, optional
		elements,// the array of elements descriptions
		global,	// the global object for thhis problem to solve	*/

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
			if (this.material){
				this.cSky = this.material.cSky(this.refEltsDimension, this.problemType, this.elementType, this.rheologyType);
				if (this.properties){
					this.properties = JSON5.parse(this.properties);
					if (this.properties.rhs === 'gravity' && !this.gravityDirection){
						Pmg.addFeedback(Pmg.message('grouprhsgravitynoglobalgravitydirection'), null, null, true);
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
				refElt.push({refH: isoUtils.refH(rst, element.optionalNodes), dHRefDXRef: isoUtils.dHRefDXRef(rst, element.optionalNodes)});
			});
			return refElt;
		}
	});
});
