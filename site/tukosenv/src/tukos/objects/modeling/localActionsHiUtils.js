"use strict";
define(["dojo/dom", "dojo/dom-attr", "dojo/_base/lang", "dojo/string", "dojo/json5", "dojo/when", "dojo/promise/all", "tukos/utils", "tukos/widgetUtils", "tukos/objects/modeling/isoparametricElementUtils", "tukos/objects/modeling/meshes/diagramsUtils", "tukos/objects/modeling/localActionsUtils",
	  "tukos/objects/modeling/rheologies/rheologiesLoader",	 "tukos/PageManager"], 
function(dom, domAtt, lang, string, JSON5, when, all, utils, wutils, isoUtils, diagramsUtils, localActionsUtils, rheologiesLoader, Pmg){
	return {
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
		getDiagramDomId: function(form, widgetDiagramName){
			const diagramEditor = form.getWidget(widgetDiagramName), diagramEditorValue = diagramEditor.get('value'), diagramOptions = diagramEditor.get('diagramOptions'), id = form.valueOf('id'), diagramDomId = (id || utils.uniqueId()) + widgetDiagramName;
			if (utils.empty(diagramEditorValue)){
				form.setValueOf(widgetDiagramName, string.substitute('<div id="${diagramDomId}" style="text-align: center;"></div>', {diagramDomId: diagramDomId}));
			}else if (id){
				if (!diagramEditorValue.includes(diagramDomId) && diagramEditorValue.includes(widgetDiagramName + '"')){// need to replace the diagramDomId generated with a uniqueId with the one with the itemId
					const newDiagramEditorValue = diagramEditorValue.replace(/([^"]*meshdiagram)/, diagramDomId);
					form.setValueOf(widgetDiagramName, newDiagramEditorValue);
				}
			}
			return diagramDomId;
		},
		postProcessDiagram: function(form, widgetDiagramName, svgString){
			const diagramDomId = this.getDiagramDomId(form, widgetDiagramName);
			const domNode = dom.byId(diagramDomId);
			domAtt.set(domNode, 'innerHTML', svgString);
			wutils.markAsChanged(form.getWidget(widgetDiagramName));
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
						combinedConstraints[node] = Object.assign(combinedConstraints[node] || {}, dofsConstraints);
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
			const groupsDescription = form.getWidget('groups').collection.fetchSync(), elements = localActionsUtils.preProcessElementsDescriptions(['g1dgroups', 'g2dgroups', 'g3dgroups'].map(groupWidgetName => meshForm.getWidget(groupWidgetName).collection.fetchSync()), false),
				  groups = {}, groupsElements = [], instantiatingRheologies = [], instantiatingMaterialsTabs = {}, initialValuesTabsSet = {};
			elements.forEach(function(element){
				(groupsElements[element.groupId] || (groupsElements[element.groupId] = [])).push(element);
			});
			groupsDescription.forEach(function(groupDescription){
				const materialId = groupDescription.materialId;
				const group = groups[groupDescription.groupId] = utils.getSubObject([
					'groupId', 'dimension', 'globalDirections', 'ndof', 'globalDofs', 'problemType', 'elementType', 'rheologyType', 'materialId', 'properties', 'integrationOrder', 'backgroundColor', 'opacity'], groupDescription);
				group.elements = groupsElements[groupDescription.groupId];
				if (!rheologiesLoader.isLoaded(group.problemType, group.rheologyType)){
					instantiatingRheologies.push(rheologiesLoader.load(group.problemType, group.rheologyType));
				}
				if (!instantiatingMaterialsTabs[materialId]){
					instantiatingMaterialsTabs[materialId] = Pmg.tabs.getTab(materialId, 'mdlmaterials', true, true);
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
			const nodalSolution = localActionsUtils.nodalSolution(global);
			if (nodalSolution){
				form.setValueOf(nodesWidgetName, localActionsUtils.postProcessNodes(nodalSolution, colNamePrefix));
			}else{
				form.setValueOf(nodesWidgetName, []);
			}
			this.processDiagram(form, nodal.coordinates, groups, 'gmeshdiagram', false, nodalSolution);
		},
		/*updateSolutionDiagram: function(form, global, nodesWidgetName, colNamePrefix, equationsIndexes){
			const {nodal, groups} = global, nodalSolution = localActionsUtils.preProcessNodes(form.getWidget(nodesWidgetName).collection.fetchSync(), colNamePrefix);//TO MODIFY
			global.solution = localActionsUtils.nodalSolutionToEquationSolution(nodalSolution, equationsIndexes);
			this.processDiagram(form, nodal.coordinates, groups, 'gmeshdiagram', false, nodalSolution);
		}*/
	}
});
