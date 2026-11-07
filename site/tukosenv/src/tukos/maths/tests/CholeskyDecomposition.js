define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/tests/utils", "tukos/maths/matrices"], 
function(declare, lang, testUtils, matrices){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			/* implement here your testing procedure*/
			const a = testUtils.matrixSymmetric3by3, aa = lang.clone(a);
			const {lower, determinant} = matrices.symDecompose(aa);
			const inverse = matrices.symCholeskyInverse(lower);
			const expectedIdentity = matrices.symMultiply(a, inverse);
			return 	'<b>a matrix</b>: ' + JSON.stringify(a) +
					'<br><b>Inverse</b>:' + JSON.stringify(inverse) +
					'<br>Determinant</br>:' + JSON.stringify(determinant) +
					'<br>Expected identity</b>:' + JSON.stringify(expectedIdentity);
		}
	});
});
