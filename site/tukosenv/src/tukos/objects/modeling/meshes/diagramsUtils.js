"use strict";
define(["dojo/dom", "dojo/dom-attr", "dojo/colors", "tukos/svg/svgTukos", "tukos/svg/utils", "tukos/utils", "tukos/maths/utils", "tukos/maths/matrices",  "tukos/objects/modeling/isoparametricElementUtils", "tukos/PageManager"], 
	function(dom, domAtt, Color, svgTukos, svgUtils, utils, mathUtils, matrices, isoUtils, Pmg){
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
		buildMeshDiagram: function(nodesCoordinates, groupsDescription, domNodeName, sourceMesh, diagramOptions, nodalSolution, dofNames){
			const domNode = dom.byId(domNodeName);
			domAtt.set(domNode, 'innerHTML', '');
			const self = this, svgSize = [domAtt.getNodeProp(domNode, 'offsetWidth'), domAtt.getNodeProp(domNode, 'offsetHeight') || diagramOptions.height || 390], svgMargin = diagramOptions.margin || 10;
			const draw = svgTukos.SVG().addTo(domNode).size(svgSize[0], svgSize[1]), dimension = nodesCoordinates.length, numNodes = nodesCoordinates[0].length, minMaxCoordinates = [], textGroup = draw.group();
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
			const arrowsDescriptions = {};
			if (nodalSolution){
				if (diagramOptions.fill && diagramOptions.fill.dof){
					fillNodalValue = nodalSolution[this.getNodalSolutionId(diagramOptions.fill.dof, dofNames)];
					minSolution = fillNodalValue.reduce((r,a) => Math.min(r,a));
					maxSolution = fillNodalValue.reduce((r,a) => Math.max(r,a));
					solutionFill = utils.mergeRecursive({type: 'color', colorRanges: {0: {value: minSolution, color: 'white'}, 1: {value: maxSolution, color: "darkred"}}}, diagramOptions.fill || {});
				}
				if (diagramOptions.isoValues){
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
				if (diagramOptions.arrows){
					utils.forEach(diagramOptions.arrows, function(description, name){
						arrowsDescriptions[name] = {values: [], description: description};
						arrowsDescriptions[name].description.maxComponentsNorm = 0;
					});
				}
			}
			for (let n = 0; n < numNodes; n++){
				svgNodes[0].push(svgMargin + scaleFactor * (nodesCoordinates[0][n] - minMaxCoordinates[0][0]));
				svgNodes[1].push(svgMargin + scaleFactor * (minMaxCoordinates[1][1] - nodesCoordinates[1][n]));
				if (diagramOptions.nodesId !== 'no') textGroup.add(draw.text(n+1).font({family: "Arial", size: "8pt"}).amove(Math.trunc(svgNodes[0][n]), Math.trunc(svgNodes[1][n])).fill("blue"));
			}
			for (let groupDescription of groupsDescription){
				const dimension = groupDescription.dimension, numberOfMandatoryNodes = isoUtils.numberOfMandatoryNodes(dimension), groupId = groupDescription.groupId/*, 
					  groupColor = Color.fromString(groupDescription.backgroundColor || 'lightgrey').toRgb().concat(groupDescription.opacity)*/;
				let elementId = 0;
				for (let element of sourceMesh ? [{nodes: groupDescription.parentNodesIndexes, optionalNodes: groupDescription.optionalNodes}] : groupDescription.elements){
					elementId += 1;
					const middleSvgCoord = [0, 0];
					let fillParameters = {color: groupDescription.backgroundColor, opacity: groupDescription.opacity};
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
							const solutionGradient = matrices.asyMultiplyVector(isoUtils._dHDX([0.0, 0.0], element.optionalNodes, [element.nodes.map((n) => svgNodes[0][n]), element.nodes.map((n) => svgNodes[1][n])]), element.nodes.map((n) => fillNodalValue[n]));
							const gradientNorm = Math.sqrt(matrices.dotProduct(solutionGradient, solutionGradient)), normalizedGradient = solutionGradient.map((x) => x / gradientNorm);
							fillParameters = draw.gradient('linear', function(add){
								add.stop(0, minOffsetColor);
								add.stop(1, maxOffsetColor);
							});
							fillParameters.from(0,0).to(Number(normalizedGradient[0].toFixed(2)), Number(normalizedGradient[1].toFixed(2)));
						}
					}
					if (diagramOptions.isoValues && dimension === 2){
						utils.forEach(isoValuesDescriptions, function(dofIsoValuesDescriptions, dofName){
							const dofNodalSolution = nodalSolution[self.getNodalSolutionId(dofName, dofNames)];
							utils.forEach(dofIsoValuesDescriptions, function(isoValueDescriptions, value){
								const elementIntersections = isoUtils.valueIntersections(value, element, dofNodalSolution, dimension);
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
					if (diagramOptions.arrows){
						const svgCoordinates = function(domainCoordinates){
							return [svgMargin + scaleFactor * (domainCoordinates[0] - minMaxCoordinates[0][0]), svgMargin - scaleFactor * (domainCoordinates[1] - minMaxCoordinates[1][1])]
						}
						utils.forEach(diagramOptions.arrows, function(description, name){//{"filtrationVelocity": {"scale":"auto", "stroke":{"color": "green"}}}
							const arrowValuesFunction = groupDescription.material[name].bind(groupDescription.material);
							if (arrowValuesFunction){
								utils.forEach(element.refElt, function(rstReferenceInformation, iPoint){
									const rstElementInformation = isoUtils.rstElementInformation(rstReferenceInformation, element, nodalSolution), components = arrowValuesFunction(rstElementInformation);
									arrowsDescriptions[name].values.push({origin: svgCoordinates(rstElementInformation.element.iPointCoordinates(iPoint)), components: components});
									arrowsDescriptions[name].description.maxComponentsNorm = Math.max(arrowsDescriptions[name].description.maxComponentsNorm, matrices.norm(components));
								});
							}
						});
					}
					const svgCoordinates = function(node){
						return [svgNodes[0][node], svgNodes[1][node]];
					};
					let pathString = '';
					pathString = svgUtils.moveTo(pathString, svgCoordinates(element.nodes[0]));
					for (let i = 1; i <= numberOfMandatoryNodes; i++){
						const edge = i, optionalNodeIndex = isoUtils.optionalGlobalNodeIndex(edge, element, dimension), endSvgCoord = svgCoordinates(element.nodes[i % numberOfMandatoryNodes]);
						if (optionalNodeIndex){
							const control = mathUtils.quadraticBezierControlPoint(svgCoordinates(element.nodes[i-1]), svgCoordinates(optionalNodeIndex), endSvgCoord);
							pathString = svgUtils.qCurveTo(pathString, control, endSvgCoord);
						}else{
							pathString = svgUtils.lineTo(pathString, endSvgCoord[0], endSvgCoord[1]);
						}
						middleSvgCoord[0] += endSvgCoord[0];
						middleSvgCoord[1] += endSvgCoord[1];
					}
					draw.path(pathString)
						.stroke("black")
						.fill(fillParameters);
					middleSvgCoord[0] /= numberOfMandatoryNodes;
					middleSvgCoord[1] /= numberOfMandatoryNodes;
					if (diagramOptions.elementsId !== 'no') textGroup.add(draw.text(groupId + ', ' + elementId).font({family: "Arial", size: "8pt"}).amove(Math.trunc(middleSvgCoord[0]), Math.trunc(middleSvgCoord[1])).fill("red"));
				}
			}
			self.addArrows(arrowsDescriptions, draw);
			self.addIsoValuesCurves(isoValuesDescriptions, isoValuesStroke, draw, svgNodes, nodalSolution, dofNames);
			textGroup.front();
		},
		addIsoValuesCurves: function(isoValuesDescriptions, isoValuesStroke, draw, svgNodes, nodalSolution, dofNames){
			const self = this;
			utils.forEach(isoValuesDescriptions, function(dofIsoValuesDescriptions, dofName){
				const dofNodalSolution = nodalSolution[self.getNodalSolutionId(dofName, dofNames)];
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
						let pathString = "";
						pathString = svgUtils.moveTo(pathString, isoUtils.intersectionCoordinates(isoValueDescription[0][0], svgNodes));
						const addElementContributionToPath = function(elementIntersections){
							const element = elementIntersections[1].element;
							if (element.optionalNodes.length === 0){
								pathString = svgUtils.lineTo(pathString, isoUtils.intersectionCoordinates(elementIntersections[1], svgNodes));
							}else{
								const rst1 = isoUtils.intersectionRst(elementIntersections[0]), rst2 = isoUtils.intersectionRst(elementIntersections[1]);
								if (rst1[0] === rst1[1] || rst1[1] === rst2[1]){
									pathString = svgUtils.lineTo(pathString, isoUtils.intersectionCoordinates(elementIntersections[1], svgNodes));
									}else{
									const midRst = rst1.map((rst, i) => 0.5 * (rst + rst2[i])), midRstInformation = isoUtils.rstReferenceInformation(midRst, element.optionalNodes);
									const midRstValue = isoUtils.refHValue(midRstInformation.refH, element.nodes, dofNodalSolution);
									const midRstGradientValue = midRstInformation.dHRefDXRef.map((x) => x.reduce((value, y, n) => value + y * dofNodalSolution[element.nodes[n]], 0.0));
									const midPoint = midRst.map((x,d) => x + 0.5 * (value - midRstValue) / midRstGradientValue[d]);
									const midPointValue = isoUtils.refHValue(isoUtils.refH(midPoint, element.optionalNodes), element.nodes, dofNodalSolution);
									/*if (Math.abs(value -midPointValue) > 0.25 * Math.abs(value - midRstValue)){
										console.log(`midPoint value = ${midPointValue} is more than 10% of midRstValue = ${midRstValue}`);
									}*/
									const beginSvgCoord = isoUtils.intersectionCoordinates(elementIntersections[0], svgNodes), endSvgCoord = isoUtils.intersectionCoordinates(elementIntersections[1], svgNodes),
										  control = mathUtils.quadraticBezierControlPoint(beginSvgCoord, isoUtils.refHValues(isoUtils.refH(midPoint, element.optionalNodes), element.nodes, svgNodes), endSvgCoord);
									pathString = svgUtils.qCurveTo(pathString, control, endSvgCoord);
								}
							}
							
						}
						utils.forEach(isoValueDescription, ((elementIntersections) => addElementContributionToPath(elementIntersections)));
						draw.path(pathString)
						.stroke(dofIsoValuesStroke[value])
						.fill('none');
					});
				});
			});
		},
		addArrows: function(arrowsDescriptions, draw){
			utils.forEach(arrowsDescriptions, function(arrowsDescription, name){
				const arrowScale = arrowsDescription.description.maxLength / arrowsDescription.description.maxComponentsNorm, arrowScaleArray = [arrowScale, -arrowScale];
				arrowsDescription.values.forEach(function(value){
					svgUtils.arrow(draw.group(), value.origin, value.components.map((x, i) => x * arrowScaleArray[i]), arrowsDescription.description.options);
				});
			});
		}
	}
});