define(["tukos/maths/matrices", "tukos/objects/modeling/meshes/diagramsSvgBuilder"], 
function(matrices, diagramsSvgBuilder){
	return {
		run: function(mode, global, diagramOptions, simulationWorker, nodalSolution, dofNames){
			try{
				if (mode === 'run'){
					let assemblyMode = {matrix: true, rhs: 'unbalanced'}, iteration = 0, decomposedMatrix, initialUnbalancedRhs, initialDeltaSolution;
					const {maxIterations, convergenceTolerance} = global.nonlinearOptions;
					while (iteration <= maxIterations){
						const {globalMatrix, globalRhs} = global.assemble(assemblyMode, global.nodalRhs.slice()); 
						if (assemblyMode.matrix){
							simulationWorker.postMessage({status: 'addFeedback', message: '...global assembly done'});
							decomposedMatrix = matrices.skyLDLDecompose(globalMatrix);
							simulationWorker.postMessage({status: 'addFeedback', message: '... LDL(T) decomposition done'});
						}
						if (iteration === 0){
							initialUnbalancedRhs = globalRhs;
							simulationWorker.postMessage({status: 'addFeedback', message: `convergenceTolerance: ${convergenceTolerance}`});
						}
						const deltaSolution = matrices.skyLDLSolve(decomposedMatrix, globalRhs.slice());
						if (global.solution){
							global.solution = matrices.vectorOperation(global.solution, deltaSolution, (a,b) => a + b);
						}else{
							global.solution = deltaSolution;
						}
						simulationWorker.postMessage({status: 'addFeedback', message: ` ... resolution done - iteration ${iteration}`});
						if (iteration >= 1){
							const trimmedSolution = global.solution.map((x) => x < 0 ? 0.0 : x), trimmedDeltaSolution = deltaSolution.map((x, i) => global.solution[i] < 0.0 ? 0.0 : x);
							const energyConvergence = Math.abs(matrices.dotProduct(deltaSolution, globalRhs) / matrices.dotProduct(initialDeltaSolution, initialUnbalancedRhs)),
							solutionConvergence = Math.sqrt(matrices.dotProduct(trimmedDeltaSolution, trimmedDeltaSolution) / matrices.dotProduct(trimmedSolution, trimmedSolution)),
							rhsConvergence = Math.sqrt(matrices.dotProduct(globalRhs, globalRhs) / matrices.dotProduct(initialUnbalancedRhs, initialUnbalancedRhs));
							simulationWorker.postMessage({status: 'addFeedback', message: `iteration: ${iteration} convergence estimates: - internal energy: ${energyConvergence} delta solution: ${solutionConvergence} unbalanced rhs: ${rhsConvergence}`});
							if (energyConvergence <= convergenceTolerance){
								simulationWorker.postMessage({status: 'addFeedback', message: ' ... convergence OK '});
								break;
							}
						}else{
							initialDeltaSolution = deltaSolution;
						}
						assemblyMode = {matrix: false, rhs: 'unbalanced'};
						iteration += 1;
					}
					const nodalSolution = global.nodalSolution();
					simulationWorker.postMessage({status: 'nodalSolutionDone', nodalSolution: nodalSolution});
					simulationWorker.postMessage({status: 'addFeedback', message: ' ... nodal solution done'});
					simulationWorker.postMessage({status: 'svgDiagramStringDone', svgString: diagramsSvgBuilder.buildMeshDiagram(global.nodal.coordinates, global.groups, false, diagramOptions, nodalSolution, dofNames)});
					simulationWorker.postMessage({status: 'addFeedback', message: ' ... solution diagram done'});
				}else{
					global.solution = global.equationsSolution(nodalSolution);
					simulationWorker.postMessage({status: 'svgDiagramStringDone', svgString: diagramsSvgBuilder.buildMeshDiagram(global.nodal.coordinates, global.groups, false, diagramOptions, nodalSolution, dofNames)});
					simulationWorker.postMessage({status: 'addFeedback', message: ' ... solution diagram done'});
				}
				
			} catch(e){
				simulationWorker.postMessage({status: 'addFeedback', message: 'unexpected error during simulation' + e});
			}
		}
	}
});
