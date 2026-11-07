define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/json5", "tukos/objects/modeling/localActionsUtils", "tukos/objects/modeling/Global", "tukos/utils",  "tukos/PageManager"], 
function(declare, lang, when, JSON5, localActionsUtils, Global, utils, Pmg){
	    return declare(null, {
		constructor: function(args){
			declare.safeMixin(this, args);
		},
		setNodalDofColumns: function(){
			const form = this.form, dofNames = form.getWidget('dofnames').collection.fetchSync();
			['boundariesconstraints', 'nodalconstraints', 'boundariesrhs', 'nodalrhs', 'nodalsolution'].forEach(function(nodalGridName){
				const  nodalGrid = form.getWidget(nodalGridName), columns = nodalGrid.columns;
				for (let i = 1; i <= dofNames.length; i++){
				    const dofi = 'col' + i;
				    if (!columns[dofi]){
				        columns[dofi] = lang.clone(form.widgetsDescription[nodalGridName].atts.dirColTemplate);
				    }
				    const column = columns[dofi];
				    column.label = dofNames[i-1][nodalGrid.dofColPrefix];
				    column.title = dofNames[i-1][nodalGrid.dofColPrefix];
				    column.field = 'dof' + i;
					nodalGrid.setColArgsFunctions(column);
				}
				setTimeout(function(){
					nodalGrid.set('columns', columns);
				}, 100);
			});
			form.setValueOf('ndof', dofNames.length);
		},
		getMeshDiagram: function(){
			const  form = this.form, currentTab = Pmg.tabs.currentPane(), meshId = form.valueOf('meshid');
			return Pmg.tabs.getTab(meshId, 'mdlmeshes', true);
		},
		runSimulation: function(mode){
			Pmg.setFeedback(Pmg.message('Starting simulation ...'));
			const form = this.form;
			when(this.getMeshDiagram(), function(meshTab){
				const meshForm = meshTab.form, groupsMaterialsElements = localActionsUtils.preProcessGroupsMaterialsElements(form, meshForm), nodalCoordinates = localActionsUtils.preProcessNodes(meshForm, 'gnodes', 'coord'), numNodes = nodalCoordinates[0].length,
					  ndof = form.valueOf('ndof'), {neq, equationsIndexes, imposedValues} = localActionsUtils.preProcessEquationsAndImposedValues(form, meshForm, numNodes, ndof), nodalRhs = localActionsUtils.preProcessNodalRhs(form, meshForm, numNodes, ndof, equationsIndexes);
				let globalProperties = form.valueOf('properties'), nonlinearOptions = form.valueOf('nonlinearoptions');
				if (globalProperties){
					globalProperties = JSON5.parse(globalProperties);
				}
				if (nonlinearOptions){
					nonlinearOptions = JSON5.parse(nonlinearOptions);
				}else{
					nonlinearOptions = {maxIterations: 5, convergenceTolerance: 0.001};
				}
				when(groupsMaterialsElements, function(groups){
					const globalArgs = {dimension: form.valueOf('dimension'), ndof: form.valueOf('ndof'), nodal: {coordinates: nodalCoordinates, equationsIndexes: equationsIndexes}, neq: neq, imposedValues: imposedValues, 
						groups: groups, nodalRhs: nodalRhs, properties: globalProperties, nonlinearOptions: nonlinearOptions},
						  global = new Global(globalArgs);
					if (mode === 'run'){
							  const timeDependency = form.valueOf('timedependency'), linearity = form.valueOf('linearity'), simulator = timeDependency + utils.capitalize(linearity);
						require(["tukos/objects/modeling/simulations/" + simulator], function(simulator){
							simulator.run(global);
							localActionsUtils.postProcessSolution(form, global, 'nodalsolution', 'dof');
							Pmg.addFeedback(Pmg.message('Simulation completed'));
						});
					}else{
						localActionsUtils.updateSolutionDiagram(form, global, 'nodalsolution', 'dof');
					}
				});
			});
		}
	});
});
