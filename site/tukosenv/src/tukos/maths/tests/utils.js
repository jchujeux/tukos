define([], 
function(){
	return {
		sqrSymmetric2by2: [[2, 1], [1, 2]],
		sqrSymmetric3by3: [[25, 15, -5], [15, 18, 0], [-5, 0, 11]],
		symMatrix3by3: [[25], [15, 18], [-5, 0, 11]],
		matrixNonSymmetric3by3: [[0,5,22/3], [4, 2, 1], [2, 7, 9]],
		matrixSymmetric6by6: [
			[2.0, 1.5, 1.5, 0.0, 0.0, 0.0],
			[1.5, 2.0, 1.5, 0.0, 0.0, 0.0],
			[1.5, 1.5, 2.0, 0.0, 0.0, 0.0],
			[0.0, 0.0, 0.0, 0.8, 0.0, 0.0],
			[0.0, 0.0, 0.0, 0.0, 0.8, 0.0],
			[0.0, 0.0, 0.0, 0.0, 0.0, 0.8]
		],
		symMatrix6by6: 		[
					[2.0],
					[1.5, 2.0],
					[1.5, 1.5, 2.0],
					[0.0, 0.0, 0.0, 0.8],
					[0.0, 0.0, 0.0, 0.0, 0.8],
					[0.0, 0.0, 0.0, 0.0, 0.0, 0.8]
				],
		nonSymmetricSquareMatrix2x2: [[2,0.5], [1, 1]],
		nonSymmetricSquareMatrix3x3: [[-4,2, 1], [1, 2, 3], [5, 1, 2]],
		nonSymmetricSquareMatrix: [
			[2.0, 1.5, 1.5, 1.2, 1.1, 0.0, 1.2, 1.1, 0.0],
			[1.0, 1.5, 1.5, 0.0, 0.0, 1.2, 1.2, 1.1, 0.0],
			[2.0, 1.5, 1.5, 0.0, 0.0, 0.6, 1.2, 1.1, 0.0],
			[3.0, 1.5, 1.5, 2.0, 0.0, 0.7, 1.2, 1.1, 0.0],
			[4.0, 1.5, 1.5, 0.0, 3.0, 0.8, 1.2, 1.1, 0.0],
			[1.0, 1.5, 1.5, 0.0, 0.5, 4.2, 1.2, 1.1, 0.0],				
			[1.0, 1.5, 1.5, 0.0, 0.5, 0.2, 5.2, 1.1, 0.0],				
			[1.0, 1.5, 1.5, 0.0, 0.5, 0.2, 1.2, 14.1, 0.0],				
			[1.0, 1.5, 1.5, 0.0, 0.5, 0.2, 1.2, 1.1, 3.0],				
		],
		getMatrixSymetric: function(dimension, max){
			const result = this.getSymMatrix(dimension, max);
			for (let i = 0; i < dimension; i++){
				for (let j = i+1; j < dimension; j++){
					result[i].push(result[j][i]);
				}
			}
			return result;
		},
		getSymMatrix: function(dimension, max){
			const result = [];
			for (let i = 0; i < dimension; i++){
				const row = [];
				for (let j = 0; j <= i; j++){
					row.push(Math.floor(Math.random() * max));
				}
				result.push(row);
			}
			return result;
		}

	}
});
