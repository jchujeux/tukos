define(["dojo/_base/lang", "tukos/maths/matrices", "tukos/utils", "tukos/PageManager"], 
function(lang, matrices, utils, Pmg){
	return {
		run: function(global){
			try{
				//global.solution = [];
				let mode = {matrix: true, rhs: 'unbalanced'}, iteration = 0, decomposedMatrix, initialUnbalancedRhs, initialDeltaSolution;
				const {maxIterations, convergenceTolerance} = global.nonlinearOptions;
				while (iteration < maxIterations){
					const {globalMatrix, globalRhs} = global.assemble(mode, global.nodalRhs.slice()); 
					if (mode.matrix){
						decomposedMatrix = matrices.skyColsolDecompose(globalMatrix);
					}
					if (iteration === 0){
						initialUnbalancedRhs = globalRhs;
						Pmg.addFeedback('convergenceTolerance: ' + convergenceTolerance);
					}
					const deltaSolution = matrices.skyColsolSolve(decomposedMatrix, globalRhs.slice());
					if (global.solution){
						global.solution = matrices.vectorOperation(global.solution, deltaSolution, (a,b) => a + b);
					}else{
						global.solution = deltaSolution;
					}
					if (iteration >= 1){
						const trimmedSolution = global.solution.map((x) => x < 0 ? 0.0 : x), trimmedDeltaSolution = deltaSolution.map((x, i) => global.solution[i] < 0.0 ? 0.0 : x);
						const energyConvergence = Math.abs(matrices.dotProduct(deltaSolution, globalRhs) / matrices.dotProduct(initialDeltaSolution, initialUnbalancedRhs)),
						solutionConvergence = Math.sqrt(matrices.dotProduct(trimmedDeltaSolution, trimmedDeltaSolution) / matrices.dotProduct(trimmedSolution, trimmedSolution)),
						rhsConvergence = Math.sqrt(matrices.dotProduct(globalRhs, globalRhs) / matrices.dotProduct(initialUnbalancedRhs, initialUnbalancedRhs));
						Pmg.addFeedback('iteration: ' + iteration + ' convergence Estimates - internal energy: ' + energyConvergence + ' delta solution ' + solutionConvergence + ' unbalanced rhs: ' + rhsConvergence);
						if (energyConvergence <= convergenceTolerance){
							Pmg.addFeedback('iteration: ' + iteration + ' convergence OK ');
							break;
						}
					}else{
						initialDeltaSolution = deltaSolution;
					}
					mode = {matrix: false, rhs: 'unbalanced'};
					iteration += 1;
				}
			} catch(e){
				Pmg.setFeedbackAlert(Pmg.message('unexpected error during simulation: ') + e);
			}
			
		}
	}
});
