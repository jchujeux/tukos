define(["dojo/_base/declare", "dojo/string", "tukos/utils", "tukos/widgetUtils", "tukos/maths/matrices", "tukos/objects/modeling/isoparametricElementUtils", "tukos/objects/modeling/localActionsUtils", "tukos/objects/modeling/localActionsHiUtils", "tukos/PageManager"], 
function(declare, string, utils, wutils, matrices, isoUtils, localActionsUtils, localActionsHiUtils, Pmg){
	/*
	* the first node is at reference coordinates (rst) -1;
	*/	
	const downstreamRefNodes = {
			1: [{mandatory: [2]}],
			2: [{mandatory: [1,4], optional: [8]}, {mandatory: [1,2], optional: [5]}],
			3: [{mandatory: [2,3,7,5], optional: [10,19,14,18]}, {mandatory: [7,3,4,8], optional: [19,11,20,15]}, {mandatory: [5,6,7,8], optional: [13,14,15,16]}]
		},
		upstreamRefNodes = {
			1: [{mandatory: [1]}],
			2: [{mandatory: [2,3], optional: [6]}, {mandatory: [4,3], optional: [7]}],
		3: [{mandatory: [1,4,8,5], optional: [12,20,16,17]}, {mandatory: [6,2,1,5], optional: [18,9,17,13]}, {mandatory: [1,2,3,4], optional: [9,10,11,12]}]
		},
		referenceNodesRst = {
			1: [[1.0], [-1.0], [0.0]],
			2: [[1.0, 1.0], [-1.0, 1.0], [-1.0, -1.0], [1.0, -1.0], [0.0, 1.0], [-1.0, 0.0], [0.0, -1.0], [1.0, 0.0]],
			3: [[1.0, 1.0, 1.0], [-1.0, 1.0, 1.0], [-1.0, -1.0, 1.0], [1.0, -1.0, 1.0], [1.0, 1.0, -1.0], [-1.0, 1.0, -1.0], [-1.0, -1.0, -1.0], [1.0, -1.0, -1.0], 
				[0.0, 1.0, 1.0], [-1.0, 0.0, 1.0], [0.0, -1.0, 1.0], [1.0, 0.0, 1.0], [0.0, 1.0, -1.0], [-1.0, 0.0, -1.0], [0.0, -1.0, -1.0], [1.0, 0.0, -1.0], [1.0, 1.0, 0.0], [-1.0, 1.0, 0.0], [-1.0, -1.0, 0.0], [1.0, -1.0, 0.0]]
		},
		referenceDirections = ['r', 's', 't'];
	    return declare(null, {
		constructor: function(args){
			declare.safeMixin(this, args);
		},
		buildMesh: function(){
			const self = this,  parentsNodesCoordinates = this.preProcessParentsNodes(), groupsDescription = this.preProcessGroupsDescriptions(), groupsSharedNodes = this.buildGroupsSharedNodes(groupsDescription), dimension = parentsNodesCoordinates.length,
				  newNodesCoordinates = utils.vector(dimension, () => []), boundariesConditionsNodes = {natural: {}, essential: {}};
			for (let groupDescription of groupsDescription){
				const steps = groupDescription.steps, elements = [], parentNodesIndexes = groupDescription.parentNodesIndexes, optionalNodes = groupDescription.optionalNodes, boundariesConditions = groupDescription.boundaries;
				let rstIndexes = Array(groupDescription.dimension);
				const drillDownDirection = function(currentDirection){
					if (currentDirection){
						currentDirection += -1;
						for (let i = 0; i < steps[currentDirection]; i++){
							rstIndexes[currentDirection] = i;
							drillDownDirection(currentDirection);
						}
						currentDirection += 1;
					}else{/// here we are at each of the reference coordinates where to build a new node / element
						const element = {nodes: [], optionalNodes: optionalNodes.slice()};
						self.setupElement(element, elements, rstIndexes, steps, groupDescription, self.getGroupSharedNodes(groupDescription, groupsSharedNodes));

						const numberOfMandatoryNodes = isoUtils.numberOfMandatoryNodes(groupDescription.dimension), numberOfNodes =  numberOfMandatoryNodes + element.optionalNodes.length;
						for (let nodeIndex = 0; nodeIndex < numberOfNodes; nodeIndex++){
							if (!(nodeIndex in element.nodes)){//new node has not been created yet => do it
								let referenceNodeIndex = nodeIndex < numberOfMandatoryNodes ? nodeIndex : element.optionalNodes[nodeIndex-numberOfMandatoryNodes] - 1;
								const nodeGroupRst = self.nodeGroupRst(referenceNodeIndex, rstIndexes, steps);
								for (let i = 0; i < dimension; i++){
									newNodesCoordinates[i].push(isoUtils.rstValue(nodeGroupRst, parentNodesIndexes, optionalNodes, parentsNodesCoordinates[i]));
								}
								element.nodes[nodeIndex] = newNodesCoordinates[0].length - 1;
								if (boundariesConditions && nodeGroupRst.some((x) => x === 1 || x === -1)){
									boundariesConditions.forEach(function(boundaryCondition){
									if (!boundariesConditionsNodes[boundaryCondition.type][boundaryCondition.id]) boundariesConditionsNodes[boundaryCondition.type][boundaryCondition.id] = [];
									for (let d = 0; d < groupDescription.dimension; d++){
											const dir = referenceDirections[d];
											if (boundaryCondition[dir] === nodeGroupRst[d]){
												boundariesConditionsNodes[boundaryCondition.type][boundaryCondition.id].push(element.nodes[nodeIndex] + 1);
											}
										}
									});
								}
							}
						}
						/*
						* Here include the "middle-node" correction for the element optional nodes newNodesCoordinates
						*/
						element.optionalNodes.forEach(function(optionalNode, optionalNodeIndex){
							const maxIterations = 10;
							let edge = isoUtils.edge(optionalNode, dimension), rEdgeMiddle = 0.0, edgeElement = isoUtils.edgeElement(edge, element, dimension), middleGlobalCoordinates = [], firstGlobalCoordinates = [], lastGlobalCoordinates = [], 
								lastGlobalNodeIndex = 1;// 1d element, 2 mandatory nodes
							for (let d = 0; d < dimension;d++){
								firstGlobalCoordinates.push(newNodesCoordinates[d][edgeElement.nodes[0]]);
								lastGlobalCoordinates.push(newNodesCoordinates[d][edgeElement.nodes[lastGlobalNodeIndex]]);
							}
							let firstToLast = matrices.distance(firstGlobalCoordinates, lastGlobalCoordinates);
							let hasConverged = false, numIterations = 0;
							while (!hasConverged){
								middleGlobalCoordinates = isoUtils.rstValues([rEdgeMiddle], edgeElement.nodes, edgeElement.optionalNodes, newNodesCoordinates);
								let fromFirst = matrices.distance(middleGlobalCoordinates, firstGlobalCoordinates), fromLast = matrices.distance(middleGlobalCoordinates, lastGlobalCoordinates);
								if (Math.abs(fromFirst - fromLast)/firstToLast < 0.05 || numIterations === maxIterations){
									hasConverged = true;
									console.log('converged - numIterations: ' + numIterations);
									if (numIterations >= 1){
										let globalNodeIndex = element.nodes[isoUtils.numberOfMandatoryNodes(dimension) + optionalNodeIndex];
										for (let d = 0; d < dimension; d++){
											newNodesCoordinates[d][globalNodeIndex] = middleGlobalCoordinates[d];
										}
									}
								}else{
									console.log('watch step - we are not at the middle');
									numIterations += 1;
									rEdgeMiddle += (fromFirst - fromLast) / firstToLast;
								}
							}
						})
						elements.push(element);
					}
				}
				drillDownDirection(steps.length);
				groupDescription.elements = elements;
			}
			this.postProcessNewNodes(newNodesCoordinates, boundariesConditionsNodes);
			this.postProcessGroupsDescriptions(groupsDescription);
			this.processsMeshDiagram(parentsNodesCoordinates, groupsDescription);
			this.processgMeshDiagram(newNodesCoordinates, groupsDescription);
		},
		preProcessParentsNodes: function(){
			return localActionsUtils.preProcessNodes(this.form.getWidget('snodes').collection.fetchSync(), 'coord');
		},
		preProcessGroupsDescriptions: function(){
			const form = this.form;
			return localActionsUtils.preProcessElementsDescriptions(['s1dgroups', 's2dgroups', 's3dgroups'].map(groupWidgetName => form.getWidget(groupWidgetName).collection.fetchSync()), true);
		},
		postProcessNewNodes: function(newNodesCoordinates, boundariesConditionsNodes){
			const form = this.form, generatedBoundaries = [];
			form.setValueOf('gnodes', localActionsUtils.postProcessNodes(newNodesCoordinates, 'coord'));
			utils.forEach(boundariesConditionsNodes, function(perType, type){
				utils.forEach(perType, function(gNodes, id){
					generatedBoundaries.push({type: type, boundaryId: id, nodes: JSON.stringify(gNodes)});
				});
			});
			form.setValueOf('gboundaries', generatedBoundaries);
		},
		postProcessGroupsDescriptions: function(groupsDescription){
			const groupsElementsIds = {}, newGridsValues = {1: [], 2: [], 3: []};
			for (const groupDescription of groupsDescription){
				const dimension = groupDescription.dimension, numberOfMandatoryNodes = isoUtils.numberOfMandatoryNodes(dimension), groupId = groupDescription.groupId;
				let elementId = groupsElementsIds[groupId] = groupsElementsIds[groupId] || 0;
				groupDescription.elements.forEach(function(element){
					elementId += 1;
					const newElement = {groupId: groupDescription.groupId, elementId: elementId};
					for (let i = 0; i < numberOfMandatoryNodes; i++){
						newElement['node' + (i+1)] = element.nodes[i] + 1;
					}
					for (let i = numberOfMandatoryNodes; i < element.nodes.length; i++){
						newElement['node' + element.optionalNodes[i - numberOfMandatoryNodes]] = element.nodes[i] + 1;
					}
					newGridsValues[dimension].push(newElement);
				});
				groupsElementsIds[groupId] += elementId;
			}
			for (let dimension = 1; dimension <= 3; dimension++){
				if (newGridsValues[dimension].length){
					this.form.setValueOf('g' + dimension + 'dgroups', newGridsValues[dimension]);
				}
			}
		},
		processsMeshDiagram: function(nodesCoordinates, groupsDescription){
			localActionsHiUtils.processDiagram(this.form, nodesCoordinates, groupsDescription, 'smeshdiagram', true);
		},
		processgMeshDiagram: function(newNodesCoordinates, groupsDescription){
			localActionsHiUtils.processDiagram(this.form, newNodesCoordinates, groupsDescription, 'gmeshdiagram', false);
		},
		buildGroupsSharedNodes: function(groupsDescription){
			const groupsShared = [];
			for (let i = 0; i < groupsDescription.length - 1; i++){
				const currentGroup = groupsDescription[i], currentDimension = currentGroup.dimension, numberMandatoryCurrent = isoUtils.numberOfMandatoryNodes(currentDimension);
				for (let j = i+1; j < groupsDescription.length; j++){
					const sharedNodes = [[], []], remainingGroup = groupsDescription[j], remainingDimension = remainingGroup.dimension, numberMandatoryRemaining = isoUtils.numberOfMandatoryNodes(remainingDimension);
					for (let nodeIndexCurrent = 0; nodeIndexCurrent < numberMandatoryCurrent; nodeIndexCurrent++){
						for (let nodeIndexRemaining = 0; nodeIndexRemaining < numberMandatoryRemaining; nodeIndexRemaining++){
							if (currentGroup.parentNodesIndexes[nodeIndexCurrent] === remainingGroup.parentNodesIndexes[nodeIndexRemaining]){// a shared node has been identified for the two groups
								sharedNodes[0].push(nodeIndexCurrent + 1);
								sharedNodes[1].push(nodeIndexRemaining + 1)
							}
						}
					}
					if (sharedNodes[0].length){
						groupsShared.push({groups: [currentGroup, remainingGroup], nodes: sharedNodes});
					}
				}
			}
			return groupsShared;
		},
		getGroupSharedNodes: function(groupDescription, groupsSharedNodes){
			const groupShared = [];
			for (const shared of groupsSharedNodes){
				switch (shared.groups.indexOf(groupDescription)){
					case 0: groupShared.push(shared); break;
					case 1: groupShared.push({groups: [shared.groups[1], shared.groups[0]], nodes: [shared.nodes[1], shared.nodes[0]]}); break;
				}
			}
			return groupShared;
		},
		upstreamElementsIndexes: function(rstIndexes, steps){
		    const upstreamIndexes = Array(rstIndexes.length).fill(0);
		    for (let i in rstIndexes){
		        let offset = 1;
				for (let j in steps){
					if (i === j && rstIndexes[i] === 0){
						upstreamIndexes[i] = false;// no upstream element for element at rstIndex in this group
						break;
					}else{
						upstreamIndexes[i] += offset * (i === j ? rstIndexes[j] - 1 : rstIndexes[j]);
					}
					offset = offset * steps[j];
				}
		    }
			return upstreamIndexes;
		},
		setupElement: function(element, elements, rstIndexes, steps, groupDescription, groupSharedNodes){
			const self = this, dimension = rstIndexes.length, upstreamElementsIndexes = this.upstreamElementsIndexes(rstIndexes, steps);
			rstIndexes.forEach(function(rstIndex, directionIndex){
				const hasDownstreamElement = rstIndex < (steps[directionIndex] - 1), hasUpstreamElement = (rstIndex > 0), downstreamNodes = downstreamRefNodes[dimension][directionIndex], upstreamNodes = upstreamRefNodes[dimension][directionIndex];
				if (hasDownstreamElement){
					element.optionalNodes.forEach(function(node, index){//foreach range is set before the first call to callback, and is initially set to the group optionalNode (as the downstream element does not exists yet)
						const optionalNodeIndex = upstreamNodes.optional.indexOf(node);
						if (optionalNodeIndex > -1){
							const optionalNode = downstreamNodes.optional[optionalNodeIndex];
							if (!element.optionalNodes.includes(optionalNode)) 
								element.optionalNodes.push(optionalNode);
						}
					});
				}else{
					for (const shared of groupSharedNodes){//this only works for groups with same dimension
						const sharingGroup = shared.groups[1], sharingNodes = shared.nodes[1], sharedNodes = shared.nodes[0];
						if (sharingGroup.elements && utils.haveSameValues(sharedNodes, downstreamNodes.mandatory) && utils.haveSameValues(sharingNodes, upstreamNodes.mandatory)){// we are at a downstream shared interface for that direction: add optional node if needed, and if the other group has odes defined, reus
							upstreamNodes.optional.forEach(function(node, index){
								if (sharingGroup.optionalNodes.includes(node)){
									const optionalNode = downstreamNodes.optional[index];
									if (!element.optionalNodes.includes(optionalNode)){
										element.optionalNodes.push(optionalNode);
									}
								}
							});
							self.setSharedNodesValues(element, sharedNodes, sharingGroup, sharingNodes, rstIndexes, directionIndex, false);
						}
					}
				}
				if (hasUpstreamElement){
					const upstreamElementIndex = upstreamElementsIndexes[directionIndex], upstreamElement = elements[upstreamElementIndex];
					downstreamNodes.mandatory.forEach(function(node, index){
						const nodeIndex = upstreamNodes.mandatory[index] - 1;
						element.nodes[nodeIndex] = upstreamElement.nodes[node-1];
					});
					if (upstreamElement.optionalNodes){
						const firstOptionalNodeIndex = isoUtils.firstOptionalNode(dimension) - 1;
						upstreamElement.optionalNodes.forEach(function(node, upstreamNodeIndex){
							let index = downstreamNodes.optional.indexOf(node);
							if (index > -1){//since the node exists and is an upstream one, make sure to include it, as optional node for this element
								const optionalNode  = upstreamNodes.optional[index];
								if (typeof optionalNode !== "undefined"){
									let optionalIndex = element.optionalNodes.indexOf(optionalNode);
									if (optionalIndex === -1){// the upstream element has an optional node which the downstream one has not => add it to the optional list
										element.optionalNodes.push(optionalNode);
										optionalIndex = firstOptionalNodeIndex + element.optionalNodes.length - 1;
									}else{
										optionalIndex += firstOptionalNodeIndex;
									}
									if (!element.nodes[optionalIndex]){
										element.nodes[optionalIndex] = upstreamElement.nodes[firstOptionalNodeIndex + upstreamNodeIndex];
									}
								}
							}
						});
					}
				}else{
					for (const shared of groupSharedNodes){//this only works for groups with same dimension
						const sharingGroup = shared.groups[1], sharingNodes = shared.nodes[1], sharedNodes = shared.nodes[0];
						if (sharingGroup.elements && utils.haveSameValues(sharedNodes, upstreamNodes.mandatory) && utils.haveSameValues(sharingNodes, downstreamNodes.mandatory)){//we are at a downstream shared interface for that direction: add optional node if needed, and if the other group has nodes defined, reus
							downstreamNodes.optional.forEach(function(node, index){
								if (sharingGroup.optionalNodes.includes(node)){
									const optionalNode = upstreamNodes.optional[index];
									if (!element.optionalNodes.includes(optionalNode)){
										element.optionalNodes.push(optionalNode);
									}
								}
							});
							self.setSharedNodesValues(element, sharedNodes, sharingGroup, sharingNodes, rstIndexes, directionIndex, true);
						}
					}
				}
			});
		},
		nodeGroupRst(referenceNodeIndex, rstIndexes, steps){
			const dimension = rstIndexes.length, result = [], nodeRst = referenceNodesRst[dimension][referenceNodeIndex];
			for (const [i, index] of rstIndexes.entries()){
				result[i] = -1 + (index * 2 + nodeRst[i] + 1) / steps[i];
			}
			return result;
		},
		elementIndex: function(steps, rstIndexes){
			let elementIndex = rstIndexes[0];
			for (let i = 1; i < rstIndexes.length; i++){
				for (let j = 0; j < i; j++){
					elementIndex += rstIndexes[i] * steps[j];
				}
			}
			return elementIndex;
		},
		setSharedNodesValues(element, sharedNodes, sharingGroup, sharingNodes, rstIndexes, directionIndex, sharesWithUpstream){
			const self = this, dimension = rstIndexes.length, sharingRstIndexes = rstIndexes.slice();
			sharingRstIndexes[directionIndex] = rstIndexes[directionIndex] === 0 ? sharingGroup.steps[directionIndex] - 1 : 0;
			const sharingElement = sharingGroup.elements[this.elementIndex(sharingGroup.steps, sharingRstIndexes)];
			sharedNodes.forEach(function(sharedNode, index){
				element.nodes[sharedNode - 1] = sharingElement.nodes[sharingNodes[index] - 1];
			});
			element.optionalNodes.forEach(function(node, index){
				if (sharesWithUpstream){
					if (upstreamRefNodes[dimension][directionIndex].optional.includes(node)){
						sharingElement.optionalNodes.forEach(function(optionalNode, sharingIndex){
							if (downstreamRefNodes[dimension][directionIndex].optional.includes(optionalNode)){
								element.nodes[self.nodesIndex(index, dimension)] = sharingElement.nodes[self.nodesIndex(sharingIndex, sharingGroup.dimension)];
							}
						});
					}
				}else{
					if (downstreamRefNodes[dimension][directionIndex].optional.includes(node)){
						sharingElement.optionalNodes.forEach(function(optionalNode, sharingIndex){
							if (upstreamRefNodes[dimension][directionIndex].optional.includes(optionalNode)){
								element.nodes[self.nodesIndex(index, dimension)] = sharingElement.nodes[self.nodesIndex(sharingIndex, sharingGroup.dimension)];
							}
						});
					}
				}
			});
		},
		nodesIndex: function(optionalNodesIndex, dimension){
			return isoUtils.firstOptionalNode(dimension) - 1 + optionalNodesIndex;
		}
	});
});
