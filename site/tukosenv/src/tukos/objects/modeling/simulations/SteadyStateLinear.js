define(["dojo/_base/lang", "tukos/maths/matrices", "tukos/utils", "tukos/PageManager"], 
function(lang, matrices, utils, Pmg){
	return {
		run: function(global){
			try{
				const {globalMatrix, globalRhs} = global.assemble({matrix: true, rhs: true}, global.nodalRhs.slice()), decomposedMatrix = matrices.skyColsolDecompose(globalMatrix);
				global.solution = matrices.skyColsolSolve(decomposedMatrix, globalRhs);
			} catch(e){
				Pmg.setFeedbackAlert(Pmg.message('unexpected error during simulation: ') + e);
			}
			
		}
	}
});
