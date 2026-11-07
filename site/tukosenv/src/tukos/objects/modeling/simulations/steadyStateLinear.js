define(["tukos/maths/matrices", "tukos/objects/modeling/meshes/diagramsSvgBuilder"], 
function(matrices, diagramsSvgBuilder){
	return {
		run: function(mode, global, diagramOptions, simulationWorker, nodalSolution, dofNames){
			try{
				if (mode === 'run'){
					const {globalMatrix, globalRhs} = global.assemble({matrix: true, rhs: true}, global.nodalRhs.slice());
					simulationWorker.postMessage({status: 'addFeedback', message: '...global assembly done'});
					const decomposedMatrix = matrices.skyLDLDecompose(globalMatrix);
					simulationWorker.postMessage({status: 'addFeedback', message: '... LDL(T) decomposition done'});
					global.solution = matrices.skyLDLSolve(decomposedMatrix, globalRhs);
					simulationWorker.postMessage({status: 'addFeedback', message: ' ... resolution done'});
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
