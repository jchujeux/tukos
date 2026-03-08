"use strict";
define(["dojo/dom-attr", "dojo/colors", "dojox/gfx", "tukos/utils", "tukos/maths/utils", "tukos/maths/matrices",  "tukos/objects/modeling/isoparametricElementUtils", "tukos/PageManager"], function(domAtt, Color, gfx, utils, mathUtils, matrices, isoUtils, Pmg){
	return {
		getNodalSolutionId: function(dofName, dofNames){
			let id;
			dofNames.some(function(dofNamesRow, index){
				if (dofNamesRow.solution === dofName){
					id = index;
					return true;
				} 
			});
			return id;
		},
		buildMeshDiagram: function(nodesCoordinates, groupsDescription, domNode, sourceMesh, diagramOptions, nodalSolutions, dofNames){
			domAtt.set(domNode, 'innerHTML', '');
			const self = this, svgSize = [domAtt.getNodeProp(domNode, 'offsetWidth'), domAtt.getNodeProp(domNode, 'offsetHeight') || diagramOptions.height || 390], svgMargin = diagramOptions.margin || 10;
			const surface = gfx.createSurface(domNode, svgSize[0], svgSize[1]), dimension = nodesCoordinates.length, numNodes = nodesCoordinates[0].length, minMaxCoordinates = [], textGroup = surface.createGroup();
			surface.rawNode.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
			for (let i = 0; i < dimension; i++){
				minMaxCoordinates[i] = [Number.MAX_VALUE, Number.MIN_VALUE];
			}
			for (let n = 0; n < numNodes; n++){
				for (let i = 0; i < dimension; i++){
					const coordinate = nodesCoordinates[i][n];
					if (coordinate < minMaxCoordinates[i][0]){
						minMaxCoordinates[i][0] = coordinate;
					}
					if (coordinate > minMaxCoordinates[i][1]){
						minMaxCoordinates[i][1] = coordinate;
					}
				}
			}
			const scaleFactors = [];
			minMaxCoordinates.forEach(function(minMax, index){
				scaleFactors[index] = (svgSize[index] - 2*svgMargin) / (minMax[1] - minMax[0]);
			});
			const scaleFactor = Math.min(...scaleFactors), svgNodes = [[], []];
		
			let minSolution, maxSolution, solutionFill, fillNodalValue;
			const isoValuesDescriptions = {}, isoValuesStroke = {};//    "isoValues": [{stroke: {"color": "orange', width: "2", "style": "ShortDash"}, values: [0,  20 000, ...]}, stroke: {"color: "red"}, values: [10 000]}]
			if (nodalSolutions){
				if (diagramOptions.fill){
					fillNodalValue = diagramOptions.fill.dof ? nodalSolutions[this.getNodalSolutionId(diagramOptions.fill.dof, dofNames)] : 0;
					minSolution = fillNodalValue.reduce((r,a) => Math.min(r,a));
					maxSolution = fillNodalValue.reduce((r,a) => Math.max(r,a));
					solutionFill = utils.mergeRecursive({type: 'color', colorRanges: {0: {value: minSolution, color: 'white'}, 1: {value: maxSolution, color: "darkred"}}}, diagramOptions.fill || {});
				}
				if (diagramOptions.isoValues){
					let stroke = {color: "orange", width: 2};
					utils.forEach(diagramOptions.isoValues, function(dofDescription, dofName){
						let stroke = {color: "orange", width: 2};
						const dofIsoValuesDescriptions = isoValuesDescriptions[dofName] = {}, dofIsoValuesStroke = isoValuesStroke[dofName] = {};
						utils.forEach(dofDescription, function(description){
							if (description.stroke){
								stroke = {...stroke, ...description.stroke};
							}
							const values = (description.values || description);
							if (values.forEach){
								values.forEach((value) => {dofIsoValuesDescriptions[value] = []; dofIsoValuesStroke[value] = stroke});
							}else{
								dofIsoValuesDescriptions[values] = [];
								dofIsoValuesStroke[values] = stroke;
							}
						});
					});
				}
			}
			surface.whenLoaded(function() {
				for (let n = 0; n < numNodes; n++){
					svgNodes[0].push(svgMargin + scaleFactor * (nodesCoordinates[0][n] - minMaxCoordinates[0][0]));
					svgNodes[1].push(svgMargin + scaleFactor * (minMaxCoordinates[1][1] - nodesCoordinates[1][n]));
					textGroup.createText({x: svgNodes[0][n], y: svgNodes[1][n], text: n+1})
						.setFont({family: "Arial", size: "8pt"})
						.setFill("blue");
				}
				for (let groupDescription of groupsDescription){
					const dimension = groupDescription.dimension, numberOfMandatoryNodes = isoUtils.numberOfMandatoryNodes(dimension), groupId = groupDescription.groupId, 
						  groupColor = Color.fromString(groupDescription.backgroundColor || 'lightgrey').toRgb().concat(groupDescription.opacity);
					let elementId = 0;
					for (let element of sourceMesh ? [{nodes: groupDescription.parentNodesIndexes, optionalNodes: groupDescription.optionalNodes}] : groupDescription.elements){
						elementId += 1;
						const middleSvgCoord = [0, 0];
						let fillParameters = groupColor;
						if (fillNodalValue){
							if (solutionFill.type === 'gradientColor'){
									const minSolution = {node: undefined, value: Number.MAX_VALUE}, maxSolution = {node: undefined, value: -Number.MIN_VALUE};
								element.nodes.forEach(function(node){//find the node with the lowest /higest solution
									const solution = fillNodalValue[node];
									if (solution < minSolution.value){
										minSolution.node = node;
										minSolution.value = solution;
									}
									if (solution > maxSolution.value){
										maxSolution.node = node;
										maxSolution.value = solution;
									}
								});
								let minOffsetColor, maxOffsetColor;
								const ranges = solutionFill.colorRanges, rangesLength = Object.keys(ranges).length;
								if (minSolution.value < ranges[0].value){
									minOffsetColor = ranges[0].color;
								}
								if (maxSolution.value > ranges[rangesLength-1].value){
									minOffsetColor = ranges[rangesLength-1].color;
								}
								for (let i = 1; i < rangesLength; i++){
									if (!minOffsetColor && minSolution.value < ranges[i].value){
										minOffsetColor = Color.blendColors(Color.fromString(ranges[i-1].color), Color.fromString(ranges[i].color), minSolution.value / (ranges[i].value - ranges[i-1].value));
									}
									if (!maxOffsetColor && maxSolution.value < ranges[i].value){
										maxOffsetColor = Color.blendColors(Color.fromString(ranges[i-1].color), Color.fromString(ranges[i].color), maxSolution.value / (ranges[i].value - ranges[i-1].value));
									}
								}
								minOffsetColor = minOffsetColor || ranges[rangesLength-1].color;
								maxOffsetColor = maxOffsetColor || ranges[rangesLength-1].color;
								const solutionGradient = matrices.asyMultiplyVector(isoUtils.dHDX([0.0, 0.0], element.optionalNodes, [element.nodes.map((n) => svgNodes[0][n]), element.nodes.map((n) => svgNodes[1][n])]), element.nodes.map((n) => fillNodalValue[n]));
								const gradientNorm = Math.sqrt(matrices.dotProduct(solutionGradient, solutionGradient)), normalizedGradient = solutionGradient.map((x) => x / gradientNorm);
								fillParameters = {type: "linear", gradientUnits: "objectBoundingBox", x1: 0, y1: 0, x2: normalizedGradient[0], y2: normalizedGradient[1], 
											  	  colors: [{offset: 0, color: minOffsetColor}, {offset: 1, color: maxOffsetColor}]};
							}
							if (diagramOptions.isoValues && dimension === 2){
								utils.forEach(isoValuesDescriptions, function(dofIsoValuesDescriptions, dofName){
									const nodalSolution = nodalSolutions[self.getNodalSolutionId(dofName, dofNames)];
									utils.forEach(dofIsoValuesDescriptions, function(isoValueDescriptions, value){
										const elementIntersections = isoUtils.valueIntersections(value, element, nodalSolution, dimension);
										if (elementIntersections.length){
											if (elementIntersections.length > 2){
												Pmg.addFeedback('More than two intersections, algorithm should fail');
											}
											let needsNewCurveDescription = true;
											isoValueDescriptions.some(function(description, index){
												if (elementIntersections[elementIntersections.length-1].id === description[0][0].id){
													isoValueDescriptions[index] = [elementIntersections].concat(description);
													needsNewCurveDescription = false;
													return true;
												}else if (elementIntersections[0].id === description[description.length-1][1].id){
													isoValueDescriptions[index] = description.concat([elementIntersections]);
													needsNewCurveDescription = false;
													return true;
												}
											});
											if (needsNewCurveDescription){
												isoValueDescriptions.push([elementIntersections]);
											}
										}
									});
								});
							}
						}
						const svgCoordinates = function(node){
							return [svgNodes[0][node], svgNodes[1][node]];
						};
						const path = surface.createPath().moveTo(svgCoordinates(element.nodes[0]))
							.setStroke("black")
							.setFill(fillParameters);
						for (let i = 1; i <= numberOfMandatoryNodes; i++){
							const edge = i, optionalNodeIndex = isoUtils.optionalGlobalNodeIndex(edge, element, dimension), endSvgCoord = svgCoordinates(element.nodes[i % numberOfMandatoryNodes]);
							if (optionalNodeIndex){
								const control = mathUtils.quadraticBezierControlPoint(svgCoordinates(element.nodes[i-1]), svgCoordinates(optionalNodeIndex), endSvgCoord);
								path.qCurveTo(control[0], control[1], endSvgCoord[0], endSvgCoord[1]);
							}else{
								path.lineTo(endSvgCoord[0], endSvgCoord[1]);
							}
							middleSvgCoord[0] += endSvgCoord[0];
							middleSvgCoord[1] += endSvgCoord[1];
						}
						middleSvgCoord[0] /= numberOfMandatoryNodes;
						middleSvgCoord[1] /= numberOfMandatoryNodes;
						textGroup.createText({x: middleSvgCoord[0], y: middleSvgCoord[1], text: groupId + ', ' + elementId})
							.setFont({family: "Arial", size: "8pt"})
							.setFill("red");
					}
				}
				self.addIsoValuesCurves(isoValuesDescriptions, isoValuesStroke, surface, svgNodes);
				textGroup.moveToFront();
			});
		},
		addIsoValuesCurves: function(isoValuesDescriptions, isoValuesStroke, surface, svgNodes){
			utils.forEach(isoValuesDescriptions, function(dofIsoValuesDescriptions, dofName){
				const dofIsoValuesStroke = isoValuesStroke[dofName];
				utils.forEach(dofIsoValuesDescriptions, function(isoValueDescriptions, value){
					const concatenatedDescriptions = [];
					isoValueDescriptions.forEach(function(isoValueDescription){//first concatenate isovalues curves descriptions
						let needsNewConcatenatedDescription = true;
						concatenatedDescriptions.some(function(concatenatedDescription, index){
							if (isoValueDescription[0][0].id === concatenatedDescription[concatenatedDescription.length-1][1].id){
								concatenatedDescription.push(...isoValueDescription);
								needsNewConcatenatedDescription = false;
								return true;
							}
							if (isoValueDescription[isoValueDescription.length-1][1].id === concatenatedDescription[0][0].id){
								concatenatedDescription.unshift(...isoValueDescription);
								needsNewConcatenatedDescription = false;
								return true;
							}
							return needsNewConcatenatedDescription;
						});
						if (needsNewConcatenatedDescription)
							concatenatedDescriptions.push(isoValueDescription);
					});
					dofIsoValuesDescriptions[value] = concatenatedDescriptions;
				});
				utils.forEach(dofIsoValuesDescriptions, function(isoValueDescriptions, value){
					isoValueDescriptions.forEach(function(isoValueDescription){
						const path = surface.createPath()
							.setStroke(dofIsoValuesStroke[value])
							.moveTo(isoUtils.intersectionCoordinates(isoValueDescription[0][0], svgNodes));
						const addElementContributionToPath = function(elementIntersections){
							path.lineTo(isoUtils.intersectionCoordinates(elementIntersections[1], svgNodes));
						}
						utils.forEach(isoValueDescription, ((elementIntersections) => addElementContributionToPath(elementIntersections)));
					});
				});
			});
		},
	}
});