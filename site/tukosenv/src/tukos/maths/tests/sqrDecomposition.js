define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/tests/utils", "tukos/maths/matrices"], 
function(declare, lang, testUtils, matrices){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			/* implement here your testing procedure*/
			const a = testUtils.matrixSymmetric3by3, aa = lang.clone(a);
			const sqrDecomposed = matrices.sqrDecompose(aa);
			const inverse = matrices.LUPInverse(sqrDecomposed);
			const determinant = matrices.sqrDeterminant(aa, sqrDecomposed);
			const expectedIdentity = matrices.asyMultiply(a, inverse);
			return 	'<b>a matrix</b>: ' + JSON.stringify(a) +
					'<br>LUP<b>:' + JSON.stringify(sqrDecomposed) +
					'<br><b>Inverse</b>:' + JSON.stringify(inverse) +
					'<br>Determinant</br>:' + JSON.stringify(determinant) +
					'<br>Expected identity</b>:' + JSON.stringify(expectedIdentity);
		}
	});
});
