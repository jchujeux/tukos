define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/json5", "tukos/objects/modeling/localActionsUtils", "tukos/objects/modeling/localActionsHiUtils", "tukos/utils",  "tukos/PageManager"], 
function(declare, lang, when, JSON5, localActionsUtils, localActionsHiUtils, utils, Pmg){
	    return declare(null, {
		constructor: function(args){
			declare.safeMixin(this, args);
			this.onReadyMessageObject = {};
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
			const  form = this.form, meshId = form.valueOf('meshid');
			return Pmg.tabs.getTab(meshId, 'mdlmeshes', true, true);
		},
		runSimulation: function(mode){
			Pmg.setFeedback(Pmg.message('Preparing  simulation ...'));
			const self = this, form = this.form;
			when(this.getMeshDiagram(), function(meshTab){
				const meshForm = meshTab.form, groupsMaterialsElements = localActionsHiUtils.preProcessGroupsMaterialsElements(form, meshForm), nodalCoordinates = localActionsUtils.preProcessNodes(meshForm.getWidget('gnodes').collection.fetchSync(), 'coord'), 
					  numNodes = nodalCoordinates[0].length, ndof = form.valueOf('ndof'), {neq, equationsIndexes, imposedValues} = localActionsHiUtils.preProcessEquationsAndImposedValues(form, meshForm, numNodes, ndof),
					  nodalRhs = localActionsHiUtils.preProcessNodalRhs(form, meshForm, numNodes, ndof, equationsIndexes);
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
						groups: groups, nodalRhs: nodalRhs, properties: globalProperties, nonlinearOptions: nonlinearOptions};
					const timeDependency = form.valueOf('timedependency'), linearity = form.valueOf('linearity'), simulator = timeDependency + utils.capitalize(linearity);
					const diagramEditor = form.getWidget('gmeshdiagram'), diagramOptions = diagramEditor.get('diagramOptions');
					let simulationWorker;
					self.onReadyMessageObject = Object.assign(self.onReadyMessageObject, {action: 'run', simulator: simulator, globalArgs: globalArgs, mode: mode, dofNames: form.getWidget('dofnames').collection.fetchSync().map(x => x),
						diagramOptions: diagramOptions ? JSON5.parse(diagramOptions) : {}, nodalSolution: mode === "run" ? [] : localActionsUtils.preProcessNodes(form.getWidget('nodalsolution').collection.fetchSync(), 'dof')});
					if (this.simulationWorker){
						simulationWorker = this.simulationWorker;
					}else{
						simulationWorker = this.simulationWorker = new Worker(require.toUrl("tukos/objects/modeling/simulations/simulationWorker"));
					}
					simulationWorker.onmessage = function(e){
						//console.log('message received from worker: ', e.data);
						switch(e.data.status){
							case 'notReady':
								console.log('not ready');
								break;
							case 'ready': 
								simulationWorker.postMessage(self.onReadyMessageObject);
								break;
							case 'nodalSolutionDone':
								form.setValueOf('nodalsolution', localActionsUtils.postProcessNodes(e.data.nodalSolution, 'dof'));
								break;
							case 'svgDiagramStringDone':
								localActionsHiUtils.postProcessDiagram(form, 'gmeshdiagram', e.data.svgString);
								break;
							case 'addFeedback':
								Pmg.addFeedback(Pmg.message(e.data.message));
								break;
							default:
								console.log('unknown status' + e.data);
								//myWorker.terminate();
						}
					}
					simulationWorker.postMessage({action: 'isReady?'});
				});
			});
		}
	});
});
