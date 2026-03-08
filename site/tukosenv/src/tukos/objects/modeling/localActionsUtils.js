"use strict";
define(["dojo/_base/lang", "dojo/string", "dojo/json5", "dojo/when", "dojo/promise/all", "tukos/utils", "tukos/widgetUtils", "tukos/objects/modeling/isoparametricElementUtils", "tukos/objects/modeling/meshes/diagramsUtils", "tukos/objects/modeling/rheologies/rheologiesLoader", "tukos/PageManager"], 
function(lang, string, JSON5, when, all, utils, wutils, isoUtils, diagramsUtils, rheologiesLoader, Pmg){
	return {
		preProcessNodes: function(form, nodesWidgetName, colNamePrefix){
			const nodes = form.getWidget(nodesWidgetName).collection.fetchSync(),  nodesValues = [];
			if (nodes.length){
				let node = nodes[0], i = 1, colName = colNamePrefix + i;
				while (node.hasOwnProperty(colName)){
					nodesValues.push([]);
					i += 1;
					colName = colNamePrefix + i;
				}
				for (let node of nodes){
					for (let i = 0; i < nodesValues.length; i++){
						nodesValues[i].push(Number(node[colNamePrefix + (i+1)]));
					}
				}
			}
			return nodesValues;
		},
		postProcessNodes: function(form, nodesWidgetName, nodesValues, colNamePrefix){
			const dimension = nodesValues.length, numNodes = nodesValues[0].length, newGridValues = [];
			for (let n = 0; n < numNodes; n++){
				const row = {nodeId: n+1};
				for (let i = 0; i < dimension; i++){
					row[colNamePrefix + (i+1)] = nodesValues[i][n];
				}
				newGridValues.push(row);
			}
			form.setValueOf(nodesWidgetName, newGridValues);
		},
		postProcessBoundariesNodes: function(form, boundariesWidgetName, boundariesConditionsNodes){
			const newGridValues = [];
			utils.forEach(boundariesConditionsNodes, function(conditionsPerType, type){
				utils.forEach(conditionsPerType, function(nodes, boundaryId){
					newGridValues.push({boundaryId: boundaryId, type: type, nodes: JSON.stringify(nodes)});
				});
			});
			form.setValueOf(boundariesWidgetName, newGridValues);
		},
		processDiagram: function(form, nodesCoordinates, groupsDescription, widgetDiagramName, sourceMesh, nodalSolutions){
			const diagramEditor = form.getWidget(widgetDiagramName), diagramEditorValue = diagramEditor.get('value'), diagramOptions = diagramEditor.get('diagramOptions'), id = form.valueOf('id'), diagramDomId = (id || utils.uniqueId()) + widgetDiagramName;
			if (utils.empty(diagramEditorValue)){
				form.setValueOf(widgetDiagramName, string.substitute('<div id="${diagramDomId}" style="text-align: center;"></div>', {diagramDomId: diagramDomId}));
			}else if (id){
				if (!diagramEditorValue.includes(diagramDomId) && diagramEditorValue.includes(widgetDiagramName + '"')){// need to replace the diagramDomId generated with a uniqueId with the one with the itemId
					const newDiagramEditorValue = diagramEditorValue.replace(/([^"]*meshdiagram)/, id + widgetDiagramName);
					form.setValueOf(widgetDiagramName, newDiagramEditorValue);
				}
			}
			if (nodalSolutions){
				diagramsUtils.buildMeshDiagram(nodesCoordinates, groupsDescription, diagramDomId, sourceMesh, diagramOptions ? JSON5.parse(diagramOptions) : {}, nodalSolutions, form.getWidget('dofnames').collection.fetchSync());
			}else{
				diagramsUtils.buildMeshDiagram(nodesCoordinates, groupsDescription, diagramDomId, sourceMesh, diagramOptions ? JSON5.parse(diagramOptions) : {});
			}
			wutils.markAsChanged(form.getWidget(widgetDiagramName));
		},
		preProcessElementsDescriptions: function(form, groupsWidgetsNames, sMesh){
			//const groupsDescriptions = {1: form.getWidget(groupsWidgetsNames[0]).collection.fetchSync(), 2: form.getWidget(groupsWidgetsNames[1]).collection.fetchSync(), 3: form.getWidget(groupsWidgetsNames[2]).collection.fetchSync()}, concatenatedElementsDescriptions = [];
			const groupsDescriptions = {}, concatenatedElementsDescriptions = [];
			groupsWidgetsNames.forEach(function(groupWidgetName){
				groupsDescriptions[groupWidgetName] = form.getWidget(groupWidgetName).collection.fetchSync();
			});
			utils.forEach(groupsDescriptions, function(elementsDescription, groupWidgetName){
				if (!utils.empty(elementsDescription)){
					const refEltDimension = parseInt(groupWidgetName[1]), firstOptionalNode = isoUtils.firstOptionalNode(refEltDimension), maxNumberOfNodes = isoUtils.maxNumberOfNodes(refEltDimension);
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
										Pmg.setFeedbackAlert(Pmg.message('missingmandatorynode'));
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
		},
		preProcessEquationsAndImposedValues: function (form, meshForm, numNodes, ndof){
			const equations = utils.vector(ndof, () => []), imposedValues = utils.vector(ndof, () => []), boundariesConstraints = utils.toObject(form.getWidget('boundariesconstraints').collection.fetchSync(), 'boundaryId', false, false, true),
				  nodalConstraints = utils.toObject(form.getWidget('nodalconstraints').collection.fetchSync(), 'nodeId', false, false, true);
			let combinedConstraints = {};
			if (!utils.empty(boundariesConstraints)){
				const boundariesNodes = utils.toObject(meshForm.getWidget('gboundaries').collection.fetchSync(), 'boundaryId', false, false, true)
				utils.forEach(boundariesConstraints, function(boundaryConstraints, boundaryId){
					const dofsConstraints = {};
					for (let d = 0; d < ndof; d++){
						const dofIndex = 'dof' + (d+1), constraint = boundaryConstraints[dofIndex];
						if (constraint){
							dofsConstraints[dofIndex] = constraint;
						}
					}
					const nodes = JSON.parse(boundariesNodes[boundaryId].nodes);
					nodes.forEach(function(node){
						combinedConstraints[node] = dofsConstraints;
					});
				});
			}
			combinedConstraints = Object.assign(combinedConstraints, nodalConstraints);
			let ieq = -1, hasImposedValues = false;
			for (let n = 0; n < numNodes; n++){
				for (let d = 0; d < ndof; d++){
					const constraint = combinedConstraints[n+1] && combinedConstraints[n+1]['dof' + (d+1)];
					if (typeof constraint === "undefined" || constraint === ''){
						equations[d][n] =  (ieq +=1);
					}else{
						equations[d][n] =  -1;
						if (constraint != 0){
							imposedValues[d][n] = constraint;
							hasImposedValues = true;
						}
					}
				}
			}
			return {neq: ieq + 1, equationsIndexes: equations, imposedValues: hasImposedValues ? imposedValues : false};
		},
		preProcessNodalRhs: function (form, meshForm, numNodes, ndof, equationIndexes){
			const equationRhs = utils.vector(equationIndexes.length, () => 0.0), boundariesRhs = utils.toObject(form.getWidget('boundariesrhs').collection.fetchSync(), 'boundaryId', false, false, true),
				  nodalRhs = utils.toObject(form.getWidget('nodalrhs').collection.fetchSync(), 'nodeId', false, false, true);
		  let combinedRhs = {};
		  if (!utils.empty(boundariesRhs)){
		  	const boundariesNodes = utils.toObject(meshForm.getWidget('gboundaries').collection.fetchSync(), 'boundaryId', false, false, true)
		  	utils.forEach(boundariesRhs, function(boundaryRhs, boundaryId){
		  		const dofsConstraints = {};
		  		for (let d = 0; d < ndof; d++){
		  			const dofIndex = 'dof' + (d+1), rhs = boundaryRhs[dofIndex];
		  			if (rhs){
		  				dofsRhs[dofIndex] = rhs;
		  			}
		  		}
		  		const nodes = JSON.parse(boundariesNodes[boundaryId].nodes);
		  		nodes.forEach(function(node){
		  			combinedRhs[node] = dofsRhs;
		  		});
		  	});
		  }
		  combinedRhs = Object.assign(combinedRhs, nodalRhs);
			for (let n = 0; n < numNodes; n++){
				for (let d = 0; d < ndof; d++){
					if (equationIndexes[d][n] != -1){
						const rhs = nodalRhs[n+1] && nodalRhs[n+1]['dof' + (d+1)];
						equationRhs[equationIndexes[d][n]] = isNaN(rhs) || rhs === '' ?  0.0 : rhs;
					}
				}
			}
			return equationRhs;
		},
		preProcessGroupsMaterialsElements: function(form, meshForm){
			const groupsDescription = form.getWidget('groups').collection.fetchSync(), elements = this.preProcessElementsDescriptions(meshForm, ['g1dgroups', 'g2dgroups', 'g3dgroups'], false), groups = {}, groupsElements = [],
				  instantiatingRheologies = [], instantiatingMaterialsTabs = {}, initialValuesTabsSet = {};
			elements.forEach(function(element){
				(groupsElements[element.groupId] || (groupsElements[element.groupId] = [])).push(element);
			});
			groupsDescription.forEach(function(groupDescription){
				const materialId = groupDescription.materialId;
				const group = groups[groupDescription.groupId] = utils.getSubObject(['groupId', 'dimension', 'globalDirections', 'ndof', 'globalDofs', 'problemType', 'elementType', 'rheologyType', 'materialId', 'properties', 'integrationOrder'], groupDescription);
				group.elements = groupsElements[groupDescription.groupId];
				if (!rheologiesLoader.isLoaded(group.problemType, group.rheologyType)){
					instantiatingRheologies.push(rheologiesLoader.load(group.problemType, group.rheologyType));
				}
				if (!instantiatingMaterialsTabs[materialId]){
					instantiatingMaterialsTabs[materialId] = Pmg.tabs.getTab(materialId, 'mdlmaterials', true);
				}
				initialValuesTabsSet[materialId] = when(instantiatingMaterialsTabs[materialId], function(materialTab){
					return when(materialTab.form.initialWidgetsValuesSet, function(){
						const form =  materialTab.form, description = lang.clone(form.displayedValueOf('description')), properties = {};
						utils.forEach(description, function(descriptionRow){
							descriptionRow.properties = JSON5.parse(descriptionRow.properties);
							properties[descriptionRow.rheology] = descriptionRow;
						});
						group.materialProperties = properties;
					});
				});
			});
			return all({...initialValuesTabsSet, ...instantiatingRheologies}).then(function(){
				return groups;
			});
		},
		postProcessSolution: function(form, global, nodesWidgetName, colNamePrefix){
			const nodal = global.nodal, solution = global.solution, imposedValues = global.imposedValues, groups = global.groups, nodalSolution = [];
			for (let i = 0; i < nodal.equationsIndexes.length; i++){
				const dofSolution = [];
				nodal.equationsIndexes[i].forEach(function(equationIndex, n){
					dofSolution.push(equationIndex === -1 ? 0 : solution[equationIndex]);
				});
				nodalSolution.push(dofSolution);
			}
			if (imposedValues){
				imposedValues.forEach(function(row, d){
					row.forEach(function(imposedValue, n){
						nodalSolution[d][n] = imposedValue;
					});
				});
			}
			this.postProcessNodes(form, nodesWidgetName, nodalSolution, colNamePrefix);
			this.processDiagram(form, nodal.coordinates, groups, 'gmeshdiagram', false, nodalSolution);
		},
		updateSolutionDiagram: function(form, global, nodesWidgetName, colNamePrefix){
			const {nodal, groups} = global, nodalSolution = this.preProcessNodes(form, nodesWidgetName, colNamePrefix);
			this.processDiagram(form, nodal.coordinates, groups, 'gmeshdiagram', false, nodalSolution);
		}
	}
});
