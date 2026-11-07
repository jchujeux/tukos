"use strict";
define(["dojo/json5", "tukos/utils", "tukos/objects/modeling/isoparametricElementUtils"], 
function(JSON5, utils, isoUtils){
	return {
		preProcessNodes: function(nodesGridValues, colNamePrefix){//from [nodeIndex][colNamePrefix] to [colNamePrefix][nodeIndex]
			const nodesValues = [];
			if (nodesGridValues.length){
				let node = nodesGridValues[0], i = 1, colName = colNamePrefix + i;
				while (node.hasOwnProperty(colName)){
					nodesValues.push([]);
					i += 1;
					colName = colNamePrefix + i;
				}
				for (let node of nodesGridValues){
					for (let i = 0; i < nodesValues.length; i++){
						nodesValues[i].push(Number(node[colNamePrefix + (i+1)]));
					}
				}
			}
			return nodesValues;
		},
		postProcessNodes: function(nodesValues, colNamePrefix){//from [colNamePrefix][nodeIndex] to [nodeIndex][colNamePrefix]
			const dimension = nodesValues.length, numNodes = nodesValues[0].length, newGridValues = [];
			for (let n = 0; n < numNodes; n++){
				const row = {nodeId: n+1};
				for (let i = 0; i < dimension; i++){
					row[colNamePrefix + (i+1)] = nodesValues[i][n];
				}
				newGridValues.push(row);
			}
			return newGridValues;
		},
		postProcessBoundariesNodes: function(boundariesConditionsNodes){
			const newGridValues = [];
			utils.forEach(boundariesConditionsNodes, function(conditionsPerType, type){
				utils.forEach(conditionsPerType, function(nodes, boundaryId){
					newGridValues.push({boundaryId: boundaryId, type: type, nodes: JSON.stringify(nodes)});
				});
			});
			return newGridValues;
		},
		preProcessElementsDescriptions: function(groupsDescriptions, sMesh){
			const concatenatedElementsDescriptions = [];
			groupsDescriptions.forEach(function(elementsDescription, index){
				if (!utils.empty(elementsDescription)){
					const refEltDimension = index + 1, firstOptionalNode = isoUtils.firstOptionalNode(refEltDimension), maxNumberOfNodes = isoUtils.maxNumberOfNodes(refEltDimension);
					elementsDescription.forEach(function(elementDescription){
						if (!utils.empty(elementDescription)){
							let nodesIndexes = [], optionalNodes = [];
							for(let i = 1; i <= maxNumberOfNodes; i++){
								let node = 'node' + i;
								const nodeIndex = elementDescription[node] - 1;
								if (!isNaN(nodeIndex) && nodeIndex >= 0){
									nodesIndexes.push(nodeIndex);
									if (i >= firstOptionalNode){
										optionalNodes.push(i);
									}
								}else{
									if (i < firstOptionalNode){
										console.log('missingmandatorynode');
										return;
									}
								}
							}
							if (sMesh){
								let steps = [];
								for (let i = 1; i <= refEltDimension; i++){
									let numSteps = elementDescription['steps' + i];
									steps.push(numSteps);
								}
								concatenatedElementsDescriptions.push({dimension: refEltDimension, groupId: elementDescription.groupId, steps: steps, parentNodesIndexes: nodesIndexes, optionalNodes: optionalNodes, 
									boundaries: elementDescription.boundaries ? JSON5.parse(elementDescription.boundaries) : undefined, backgroundColor: elementDescription.backgroundColor, opacity: elementDescription.opacity});
							}else{
								concatenatedElementsDescriptions.push({groupId: elementDescription.groupId, nodes: nodesIndexes, optionalNodes: optionalNodes});
							}
						}
					});
				}
			});
			return concatenatedElementsDescriptions;
		}
	};
});
