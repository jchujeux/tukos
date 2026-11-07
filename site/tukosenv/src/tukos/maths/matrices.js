"use strict";
define(["tukos/utils"],
	function(utils) {
			const tolerance = 1.0E-8;
	return {
		dotProduct: function(a,b){
			return a.reduce((r, a, i) => r += a*b[i], 0);
		},
		vectorOperation: function(a, b, callback){
			const result = [];
			for (let i = 0; i < a.length; i++){
				result[i] = callback(a[i], b[i]);
			}
			return result;
		},
		transpose: function(matrix) {
		  return matrix[0].map((col, i) => matrix.map(row => row[i]));
		},
		sqrToSym: function(matrix, inPlace){
			if (inPlace){
				for (let r = 0; r < matrix.length; r++){
					matrix[r].splice(r+1);
				}
			}else{
				const sym = [];
				for (let r = 0; r < matrix.length; r++){
					sym.push(matrix[r].slice(0, r+1));
				}
				return sym;
			}
		},
		symToSqr: function(sym, inPlace){
			let  result;
			if (inPlace){
				result = sym;
			}else{
				result = [];
				for (let r = 0; r < sym.length; r++){
					result.push(sym[r].slice(0, r+1));
				}
			}
			for (let i = 0; i < sym.length; i++){
				for (let j = i+1; j < sym.length; j++){
					result[i][j] = result[j][i];
				}
			}
			return result;
		},
		symToSky: function(sym){
			const length = sym.length, diagIndexes = [], upper = [];
			for (let r = 0; r < length; r++){
				let zeroCol = [];
				for (let c = r; c >= 0; c--){
					const value = sym[r][c];
					if (value == 0){
						zeroCol.push(value);
					}else{
						if (zeroCol.length){
							upper.push(...zeroCol);
							zeroCol = [];
						}
						upper.push(value);
					}
				}
				diagIndexes[r] = upper.length;
			}
			return {diagIndexes: diagIndexes, upper: upper};
		},
		skyToSym: function(sky){// s = diagIndexes[c-1] + c-r
			const {diagIndexes, upper} = sky, rows = [];
			let diagIndex = 0;
			for (let r= 0; r < diagIndexes.length; r++){
				const nextDiagIndex = diagIndexes[r], rrFirst = r - nextDiagIndex + diagIndex + 1, row = [];
				let s = nextDiagIndex - 1;
				for (let c = 0; c < rrFirst; c++){
					row.push(0.0);
				}
				for (let c = rrFirst; c <= r; c++){
					row.push(upper[s]);
					s -= 1;
				}
				rows.push(row);
				diagIndex = nextDiagIndex;
			}
			return rows;
		},
		multiply(a, b){
			if (a.diagIndexes){
				if (b.diagIndexes){
					return this.skyMultiply(a, b);
				}else{
					if (a.diagIndexes.length === 1  && b.length === 1){
						return [a.upper[0] * b[0][0]];
					}else{
						if (b[0].length === 1 && b[1].length === 2){
							return this.symMultiply(this.skyToSym(a), b);
						}else{
							return this.asyMultiply(this.symToSqr(this.skyToSym(a)), b);
						}
					}
				}
			}else{
				if (b.diagIndexes){
					if (a.length === 1 && b.diagIndexes.length === 1){
						return [b.upper[0] * a[0][0]];
					}else{
						if (a[0].length === 1 && a[1].length === 2){
							return this.symMultiply(a, this.skyToSym(b));
						}else{
							return this.asyMultiply(a, this.symToSqr(this.skyToSym(b)));
						}
					}
				}else{
					if (a.length === 1 && b.length === 1){
						return [b[0][0] * a[0][0]];
					}else{
						if (a[0].length === 1 && a[1].length === 2){
							if (b[0].length === 1 && b[1].length === 2){
								return this.symMultiply(a, b);
							}
						}else{
							if (b[0].length === 1 && b[1].length === 2){
								return this.symMultiply(this.symToSqr(a), b);
							}else{
								return this.asyMultiply(a,b)
							}
						}
					}
				}
			}
		},
		asyMultiply: function(a, b){
		  let res = [];
		  for (let r = 0; r < a.length; ++r) {
		    res[r] = [];
		    for (let c = 0; c < b[0].length; ++c) {
		      res[r][c] = 0;
		      for (let i = 0; i < a[0].length; ++i)
		        res[r][c] += a[r][i] * b[i][c];
		    }
		  }
		  return res;
		},
		asyMultiplyVector: function(a, b){
		  let res = [];
		  for (let r = 0; r < a.length; ++r) {
		    res[r] = 0;
		    for (let i = 0; i < a[0].length; ++i){
		        res[r] += a[r][i] * b[i];
			}
		  }
		  return res;
		},
		symMultiply: function(a,b){
			const length = a.length;
			let res = new Array(length);
			for (let r = 0; r < length; ++r) {
			  res[r] = new Array(r+1);
			  for (let c = 0; c <= r; ++c) {
			    res[r][c] = 0;
				for (let i = 0; i <= c; ++i)
				  res[r][c] += a[r][i] * b[c][i];
				for (let i = c+1; i <= r; ++i)
					res[r][c] += a[r][i] * b[i][c];
				for (let i = r+1; i < length; ++i)
					res[r][c] += a[i][r] * b[i][c];
			  }
			}
			return res;
		},
		skyMultiplyVector: function(sky, vector){// s = diagIndexes[c-1] + c-r
			const {diagIndexes, upper} = sky, result = utils.vector(diagIndexes.length, () => 0.0);
			let diagIndex = 0;
			for (let r = 0; r < diagIndexes.length; r++){
				const nextDiagIndex = diagIndexes[r];
				let s = nextDiagIndex - 1;
				for (let c = r - nextDiagIndex + diagIndex + 1; c <= r; c++){//handling the lower triangle contribution
					result[r] += upper[s] * vector[c];
					s -= 1;
				}
				for (let c = r + 1; c < diagIndexes.length; c++){//handling the upper triangle contribution
					const s = diagIndexes[c-1] + c - r;
					if (s < diagIndexes[c])
						result[r] += upper[s] * vector[c];
				}
				diagIndex = nextDiagIndex;
			}
			return result;
		},
		skyMultiply: function(skyA, skyB, isTriangular){// s = diagIndexes[c-1] + c-r
			
			// fill result by columns, using only the upper triangle of a and b
			/*for (let c = 0; c < length; c++){
				for (let r = c; r >= 0; r--){// we skip result arrays initialization
					result[r][c] = 0.0;
					for (let k = 0; k < length; k++){
						result[r][c] += (k < r ? a[k][r] : a[r][k]) * (k < c ? b[k][c] : b[c][k]);
					}
				}
			}
			*/
			// limit the loop the the sky contributions by reducing the loop intervals::
			//    we have r <= c, then:
			// if k <= r, the contributing factors are a[k][r] and b[k][c]. and with varying k, both factors move long a colums
			//		The lowest k for which a[k][r] exists is r - diagIndexesA[r] + diagIndexesA[r-1] + 1, 
			//		the lowest k for which b[k][c] exists is c - diagIndexesB[c] + diagIndexesB[c-1] + 1, 
			//    => the loop needs to start at max(lowest), and ends at r
			//if r < k <= c, the contributing factors are a[r][k] and b[k][c], and with varying k, a[r][k] moves successive columns along the same row, while b[k][c] moves along a column.
			//		a[r][k] exists if k - diagIndexesA[k] + diagIndexesA[k-1] + 1  is <= r
			//		the lowest k for which b[k][c] exists is c - diagIndexesB[c] + diagIndexesB[c-1] + 1,
			//	  => the loop needs to start at max (r+1, lowestB) and stops at c
			//if r < c < k, the contributing factors are a[r][k] and b[c][k], and with varying k, both move on successive columns along the same row
			//		a[r][k] exists if k - diagIndexesA[k] + diagIndexesA[k-1] + 1  is <= r
			//		b[c][k] exists if k - diagIndexesB[k] + diagIndexesB[k-1] + 1  is <= c
			//
			//In the above skyA and skyB are assumed symmetric. We may want to handle the case where they are triangular, e.g. to be able to calculate DL(T) in LDL(T)). 
			//Let's assume skyB is an upper triangular matrix, then, if k > c,  b[k][c] = 0. To cope with this situation, we need to skip step 3 above as all contributions are 0 
			//can we introduce a flag to handle this situation ?
			// skyMultiply: function(skyA, skyB, aType, bType), with xTape can be: symmetric (default), lower or  upper
			// 	we have a constraint: we need to keep the symmetry, or the triangle nature, means we can't combine lower triangle and upper triangle, so wht's do-able is skyMultiply: function(skyA, skyB, isUpperTriangular), 
			// knowing that the product of two upper triangular matrices is upper triangular
			// if this is the case, then knowing result [r, c], for c >= r is sum(a[r,k]*b[k,c], upper triangular means if k < r or k > c, contribution is 0.
			// 		
			
			const {diagIndexes: diagIndexesA, upper: upperA} = skyA, {diagIndexes: diagIndexesB, upper: upperB} = skyB, diagIndexesLength = diagIndexesA.length, {diagIndexes: diagIndexesR, upper: upperR} = {diagIndexes: [], upper: []};
			let diagIndexA = 0, diagIndexB = 0;
			for (let c = 0; c < diagIndexesLength; c++){
				const nextDiagIndexB = diagIndexesB[c];
				for (let r = c; r >= 0; r--){//we only build the upper part of result, 
					let nextDiagIndexA = diagIndexesA[r], sum = 0.0, contributes = false;
					const firstARow = r - nextDiagIndexA + diagIndexA + 1, firstBRow = c - nextDiagIndexB + diagIndexB + 1;
					let  firstRow = Math.max(firstARow, firstBRow);
					let sA = diagIndexA + r - firstRow, sB = diagIndexB + c - firstRow;//for both A and B, k is the row index, 
					if (firstRow <= r && !isTriangular){
						contributes = true;
						for (let k = firstRow; k <= r; k++){//handling the lower A triangle contribution & upper B triangle contribution:  a[k,r] * b[k, c]
							sum += upperA[sA] * upperB[sB];
							sA -= 1;
							sB -= 1;
						}
					}
					firstRow = Math.max(isTriangular ? r : r+1, firstBRow);
					sB = diagIndexB + c - firstRow;
					for (let k = firstRow; k <= c; k++){//handling the upperA triangle contribution and upper B triangle contribution: a[r,k] * b[k,c]
						sA = k === 0 ? 0 : diagIndexesA[k-1] + k - r;//now r is the row index for A
						if (sA < diagIndexesA[k]){
							contributes = true
							sum += upperA[sA] * upperB[sB];
						}
						sB -= 1;
					}
					if (!isTriangular){
						for (let k = c + 1; k < diagIndexesLength; k++){//handling the upperA triangle contribution and lower B triangle contribution: a[r,k] * b[c,k]
							sA = diagIndexesA[k-1] + k - r;
							sB = diagIndexesB[k-1] + k - c;//now c is the row index for B
							if (sA < diagIndexesA[k] && sB < diagIndexesB[k]){
								contributes = true;
								sum += upperA[sA] * upperB[sB];
							}
						}
					}
					if (contributes){
						upperR.push(sum);
						diagIndexA = r <= 1 ? 0: diagIndexesA[r-2];
					}else{
						break; // if no contribution of this value of r, all rows above don't contribute either
					}
				}
				diagIndexA = diagIndexesA[c];
				diagIndexesR.push(upperR.length);
				diagIndexB = nextDiagIndexB;
			}
			return {diagIndexes: diagIndexesR, upper: upperR};
		},
		skyAdd: function(skyA, skyB){//allows different sky profiles
			const {diagIndexes: diagIndexesA, upper: upperA} = skyA, {diagIndexes: diagIndexesB, upper: upperB} = skyB;
			if (diagIndexesA.length !== diagIndexesB.length) throw "incompatible matrices in matrices.skyAdd";
			const diagIndexesR = [], upperR = [], length = diagIndexesA.length;
			let c = 0, s = 0;
			while (diagIndexesA[c] === diagIndexesB[c] && c < length){
				diagIndexesR.push(diagIndexesA[c]);
				c++;
			}
			while (s < diagIndexesR[c-1]){
				upperR[s] = upperA[s] + upperB[s];
				s++;
			}
			if (c < length){
				let sR = s, sA = s, sB = s, diagIndexA = (c === 0 ? 0 : diagIndexesA[c-1]), diagIndexB = (c === 0 ? 0 : diagIndexesB[c-1]);
				while (c < length){
					const nextDiagIndexA = diagIndexesA[c], nextDiagIndexB = diagIndexesB[c], colHeightA = nextDiagIndexA - diagIndexA, colHeightB = nextDiagIndexB - diagIndexB, addHeight = Math.min(colHeightA, colHeightB);
					let h = 0;
					while (h < addHeight){
						upperR[sR] = upperA[sA] + upperB[sB];
						sR +=1; sA +=1; sB +=1; h +=1;
					}
					if (colHeightA > colHeightB){
						while (h < colHeightA){
							upperR[sR] = upperA[sA];
							sR +=1; sA +=1; h +=1;
						}
					}else if (colHeightB > colHeightA){
						while (h < colHeightB){
							upperR[sR] = upperB[sB];
							sR +=1; sB +=1; h +=1;
						}
					}
					diagIndexesR.push(sR);
					diagIndexA = nextDiagIndexA;
					diagIndexB = nextDiagIndexB;
					c++;
				}
			}
			return {diagIndexes: diagIndexesR, upper: upperR};
		},
		aTSkyA: function(a, sky){
			const {diagIndexes, upper} = sky, aCols = a[0].length, rDiagIndexes = [], rValues = [];
			for (let j = 0; j < aCols; j++){
				const col = [];
				for (let i = j; i >= 0 ; i--){
			       	let result = 0.0, diagIndex = 0;
				    for (let l = 0; l < diagIndexes.length; l++){
						result += a[l][i] * upper[diagIndex] * a[l][j];
						const minColIndex = l + diagIndex -  diagIndexes[l] + 1;
						let colIndex = diagIndex + 1;
						for (let k = l-1; k >= minColIndex; k--){
							result += upper[colIndex] * (a[k][i] * a[l][j] + a[l][i] * a[k][j]);
							colIndex++;
						}
						diagIndex = diagIndexes[l];
				    }
					col.push(result);
			    }
				while (col.length > 1 && col[col.length-1] == 0.0){
					col.pop();
				}
				rValues.push(...col);
				rDiagIndexes.push(rValues.length);
			}
			return {diagIndexes: rDiagIndexes, upper: rValues};			
		},
		sqrDecompose: function(a){// in place
			const length = a.length, pivot = utils.vector(length+1, (v, i) => i), tolerance = 1E-8;
			let aMax, iMax;
			for (let i = 0; i < length;i++){
				aMax = 0.0;
				iMax = i;
				for (let k = i; k < length;k++){
					const aAbs = Math.abs(a[k][i]);
					if (aAbs > aMax){
						aMax = aAbs;
						iMax = k;
					}
				}
				if (aMax < tolerance){
					throw new Error("matrices.LUPDecmpose: pivot too small"); 
				}
				if (iMax !== i){
					const j = pivot[i];
					pivot[i] = pivot[iMax];
					pivot[iMax] = j;
					let aRow = a[i];
					a[i] = a[iMax];
					a[iMax] = aRow;
					pivot[length]++;
				}
				for (let j = i+1; j < length; j++){
					a[j][i] /= a[i][i];
					for (let k = i+1; k < length; k++){
						a[j][k] -= a[j][i] * a[i][k];
					}
				}
			}
			return {pivot: pivot, lu: a};
		},
		symDecompose: function(a){// LL* inspired from here: https://jamesmccaffreyblog.com/2023/09/18/matrix-cholesky-decomposition-and-inverse-using-javascript/
		  const n = a.length;
		  let result = [], det = 1.0;
		  for (let i = 0; i < n; ++i) {
		    result.push([]);
			for (let j = 0; j <= i; ++j) {
		      let sum = 0.0;
		      for (let k = 0; k < j; ++k){
				sum += result[i][k] * result[j][k];
			  }
	      	  if (i == j) {
	        	let tmp = a[i][i] - sum;
	        	if (tmp < 0.0) throw "matCholeskyDecomp fatal error ";
	        	result[i][i] = Math.sqrt(tmp);
				det *= tmp;
		      }else{
		        if (result[j][j] == 0.0) throw "matCholeskyDecomp fatal error ";
		        result[i][j] = (a[i][j] - sum) / result[j][j];
		      }
		    }
		  }
		  return {lower: result, determinant: det};
		},
		skyDecompose: function(sky){// Cholesky decomposition: LL*
		  const {diagIndexes, upper} = sky, n = diagIndexes.length;
		  let iDiagIndex = 0, det = 1.0;
		  for (let i = 0; i < n; ++i) {
			let iDiagIndexNext = diagIndexes[i];
			const iRowMin = i + 1 - (iDiagIndexNext - iDiagIndex);
			let jDiagIndex = iRowMin > 0 ? diagIndexes[iRowMin-1] : 0;
			for (let j = iRowMin; j <= i; j++) {
		      let jDiagIndexNext = diagIndexes[j], sum = 0.0;
			  const jRowMin = j + 1 - (jDiagIndexNext - jDiagIndex);
			  const kMin = Math.max(iRowMin, jRowMin);
		      for (let k = kMin; k < j; k++){
				sum += upper[iDiagIndex+i-k] * upper[jDiagIndex+j-k];//result[i][k] * result[j][k];
			  }
		  	  if (i == j) {
		    	let tmp = upper[iDiagIndex] - sum;
		    	if (tmp < 0.0) throw "matCholeskyDecomp fatal error ";
		    	upper[iDiagIndex] = Math.sqrt(tmp);
				det *= tmp;
		      }else{
		        if (upper[jDiagIndex] == 0.0) throw "matCholeskyDecomp fatal error ";
		        upper[iDiagIndex+i-j] = (upper[iDiagIndex+i-j] - sum) / upper[jDiagIndex];
		      }
			  jDiagIndex = jDiagIndexNext;
		    }
			iDiagIndex = iDiagIndexNext;
		  }
		  return {diagIndexes: diagIndexes, upper: upper, determinant: det};
		},
		skyColsolDecompose: function(sky){
			const {diagIndexes, upper} = sky, length = diagIndexes.length;
			let diagIndex = 0;
			for (let c = 0; c < length; c++){ // loop 140
				const lowS = diagIndex + 1, nextDiagIndex = diagIndexes[c], highS = nextDiagIndex - 1, minCRMin = c + 1 - nextDiagIndex + diagIndex;
				if (highS > lowS){
					for (let pC = 1; pC < c; pC++){ // loop 80
						let sum = 0.0, pCRMax = pC - 1, pCI = diagIndexes[pC-1], cI = diagIndex + c - pC, cItoUpdate = cI;
						const rMin = Math.max(pC - diagIndexes[pC] + pCI, minCRMin);
						for (let r = pCRMax; r >= rMin; r--){ // loop
							pCI += 1; cI += 1; 
							sum += upper[pCI] * upper [cI];
						}
						upper[cItoUpdate] -= sum; 
					}
				}
				if (highS >= lowS){//label 90
					let sum = 0.0, r = c - 1, temp;
					for (let s = lowS; s <= highS; s++){
						temp = upper[s] / upper[r >= 1 ? diagIndexes[r-1] : 0];
						sum += temp * upper[s];
						upper[s] = temp;
						r -= 1;
					}
					upper[diagIndex] -= sum;
				}
				if (upper[diagIndex] <= 0.0) throw "fatal error: inskyColsolDecompose, matrix is not positive definite ";//label 110
				diagIndex = nextDiagIndex;
			}
			return sky;
		},
		skyLDLDecompose: function(sky){
		  const {diagIndexes, upper} = sky, n = diagIndexes.length;
		  let iDiagIndex = 0, det = 1.0;
		  for (let i = 0; i < n; ++i) {
			let iDiagIndexNext = diagIndexes[i];
			const iRowMin = i + 1 - (iDiagIndexNext - iDiagIndex);
			let jDiagIndex = iRowMin > 0 ? diagIndexes[iRowMin-1] : 0;
			for (let j = iRowMin; j <= i; j++) {
		      let jDiagIndexNext = diagIndexes[j], sum = 0.0;
			  const jRowMin = j + 1 - (jDiagIndexNext - jDiagIndex);
			  const kMin = Math.max(iRowMin, jRowMin);
		      for (let k = kMin; k < j; k++){
				sum += upper[iDiagIndex+i-k] * upper[jDiagIndex+j-k];//result[i][k] * result[j][k];
			  }
		  	  if (i == j) {
		    	upper[iDiagIndex] = upper[iDiagIndex] - sum;
				det *= upper[iDiagIndex];
		      }else{
		        if (upper[jDiagIndex] == 0.0) throw "matCholeskyDecomp fatal error ";
		        upper[iDiagIndex+i-j] = (upper[iDiagIndex+i-j] - sum) / upper[jDiagIndex];
		      }
			  jDiagIndex = jDiagIndexNext;
		    }
			iDiagIndex = iDiagIndexNext;
		  }
		  return {diagIndexes: diagIndexes, upper: upper, determinant: det};
		},
		skyColsolForwardSubstitution: function(skyLDL, b){// s = diagIndexes[c-1] + c-r
			const {diagIndexes, upper} = skyLDL;
			let diagIndex = 0;
			for (let r = 0; r < b.length; r++){
				const nextDiagIndex = diagIndexes[r];
				let sum = 0.0, k = r;
				for (let s = diagIndex + 1; s < nextDiagIndex; s++){
					k -= 1;
					sum += upper[s] * b[k];
				}
				b[r] = b[r] - sum;
				diagIndex = nextDiagIndex;
			}
			return b;
		},
		skyLDLForwardSubstitution: function(skyLDL, b){// s = diagIndexes[c-1] + c-r
			const {diagIndexes, upper} = skyLDL;
			let diagIndex = 0;
			for (let r = 0; r < b.length; r++){// loop 180
				const nextDiagIndex = diagIndexes[r];
				let sum = 0.0, c = r;
				for (let s = diagIndex + 1; s < nextDiagIndex; s++){ // loop 170
					c -= 1;
					sum += upper[s] * b[c];
				}
				result[r] = b[r] - sum;
				diagIndex = nextDiagIndex;
			}
			return b;
		},
		skyColsolBackwardSubstitution: function(skyLDL, b){
			const {diagIndexes, upper} = skyLDL;
			let diagIndex = 0;
			for (let r = 0; r < diagIndexes.length; r++){//loop 200
				b[r] = b[r] / upper[diagIndex];
				diagIndex = diagIndexes[r];
			}
			for (let r = diagIndexes.length-1; r > 0; r--){// loop 230
				const nextDiagIndex = diagIndexes[r], diagIndex = diagIndexes[r-1];
				let k = r;
				for (let s = diagIndex + 1; s < nextDiagIndex; s++){
					k -= 1;
					b[k] -= upper[s] * b[r];
				}
			}
			return b;
		},
		skyLineLDLBackwardSubstitution: function(skyLDL, y){
			const {diagIndexes, upper} = skyLDL;
			for (let r = diagIndexes.length-1; r >= 0; r--){
				let sum = 0.0, diagIndex = diagIndexes[r];
				for (let c = diagIndexes.length - 1; c > r; c--){// in https://algowiki-project.org/en/Backward_substitution, it is stated that not decreasing c implies severe degradation of parallel properties of the algorithm (?)
					const nextDiagIndex = diagIndexes[c], s = diagIndex + c - r;
					if (s < nextDiagIndex){
						sum += upper[s] * y[c];
					}
					diagIndex = nextDiagIndex;
				}
				y[r] = (y[r] - sum) / (r === 0 ? upper[0] : upper[diagIndexes[r-1]]);
			}
			return y;
		},
		skyColsolSolve: function(skyLDL, b){
			return this.skyColsolBackwardSubstitution(skyLDL, this.skyColsolForwardSubstitution(skyLDL, b));
		},
		skyLDLSolve: function(skyLDL, b){
			return this.skyLineLDLBackwardSubstitution(skyLDL, this.skyLDLForwardSubstitution(skyLDL, b));
		},
		sqrDeterminant: function(sqr, sqrDecomposed){
			switch (sqr.length){
				case 1: return sqr[0][0];
				case 2: return this.sqrDeterminant2by2(sqr);
				case 3: return this.sqrDeterminant3by3(sqr);
				default:
					if (typeof sqrDecomposed === 'undefined') throw "sqrDeterminant needs LUP defined if dimension is 3 or more";
					return this.sqrDecomposedDeterminant(sqrDecomposed);
			}
		},
		sqrDeterminant2by2: function(sqr){
			return  sqr[0][0] * sqr[1][1] - sqr[0][1] * sqr[1][0];
		},
		sqrDeterminant3by3: function(sqr){// found here: https://www.youtube.com/watch?v=p8VnTCfJHAo
			let det = 0.0;
			for (let i = 0; i < 3; i++){
				let term = 1.0;
				for (let j = 0; j < 3; j++){
					const ij = (i + j) % 3;
					term *= sqr[j][ij];
				}
				det += term;
			}
			for (let i = 2; i < 5; i++){
				let term = 1.0;
				for (let j = 0; j < 3; j++){
					const ij = (i - j) % 3;
					term *= sqr[j][ij];
				}
				det -= term;
			}
			return det;
		},
		sqrDecomposedDeterminant: function(sqrDecomposed){
			const {lu, pivot, det} = sqrDecomposed;
			if (typeof det === "undefined"){
				let det = lu[0][0];
				for (let i = 1; i < lu.length;i++){
					det *= lu[i][i];
				}
				sqrDecomposed.determinant = (pivot[lu.length] - lu.length) % 2 ? -det : det;
			}
			return sqrDecomposed.determinant;
		},
		symDeterminant: function(sym, symDecomposed){
			switch (sym.length){
				case 1: return sym[0][0];
				case 2: return this.symDeterminant2by2(sym);
				case 3: return this.symDeterminant3by3(sym);
				default:
					if (typeof symDecomposed === 'undefined') throw "sqrDeterminant needs symDecomposed defined if dimension is 3 or more";
					return this.symDecomposedDeterminant(symDecomposed);
			}
		},
		symDeterminant2by2: function(sym){
			return  sym[0][0] * sym[1][1] - sym[1][0] * sym[1][0];
		},
		symDeterminant3by3: function(sym){// found here: https://www.youtube.com/watch?v=p8VnTCfJHAo
			let det = 0.0;
			for (let i = 0; i < 3; i++){
				let term = 1.0;
				for (let j = 0; j < 3; j++){
					const ij = (i + j) % 3;
					term *= j >= ij ? sym[j][ij] : sym[ij][j];
				}
				det += term;
			}
			for (let i = 2; i < 5; i++){
				let term = 1.0;
				for (let j = 0; j < 3; j++){
					const ij = (i - j) % 3;
					term *= j >= ij ? sym[j][ij] : sym[ij][j];
				}
				det -= term;
			}
			return det;
		},
		symDecomposedDeterminant: function(symDecomposed){
			return symDecomposed.determinant;
		},
		skyDeterminant: function(sky, skyDecomposed){
			switch (sky.length){
				case 1: return sky[0];
				case 2: return this.skyDeterminant2by2(sky);
				case 3: return this.skyDeterminant3by3(sky);
				default:
					if (typeof skyDecomposed === 'undefined') throw "sqrDeterminant needs skyDecomposed defined if dimension is 3 or more";
					return this.skyDecomposedDeterminant(skyDecomposed);
			}
		},
		skyDeterminant2by2: function(sky){
			return  sky[0][0] * sky[1][1] - sky[1][0] * sky[1][0];
		},
		skyDeterminant3by3: function(sky){// found here: https://www.youtube.com/watch?v=p8VnTCfJHAo
			return this.symDeterminant3by3(this.skyToSym(sky));
		},
		sqrInverse: function(a, determinant, LUP){
			switch (a.length){
				case 1: return [[1 / a[0][0]]];
				case 2: return this.inverse2by2(a, determinant);
				case 3: return this.inverse3by3(a, determinant);
				default:
					if (typeof LUP === 'undefined'){
						LUP = LUPDecompose(lang.clone(a));
					}
					if (determinant !== false){
						determinant = this.LUPDeterminant(LUP);
					}
					return LUPInverse(LUP);
			}
		},
		skyDecomposedDeterminant: function(skyDecomposed){
			return skyDecomposed.determinant;
		},
		inverse2by2: function(a/* [[a,b], [c,d]]*/, determinant/* if undefined, will be computed*/){
			if (typeof determinant === 'undefined'){
				determinant = this.sqrDeterminant2by2(a);
			}
			return Math.abs(determinant) < tolerance ? null : [[a[1][1] / determinant, -a[0][1]/ determinant], [-a[1][0] / determinant, a[0][0] / determinant]];
		},
		inverse3by3: function(a, determinant){// found here: https://www.youtube.com/watch?v=p8VnTCfJHAo
			if (typeof determinant === 'undefined'){
				determinant = this.sqrDeterminant3by3(a);
			}
			if (Math.abs(determinant) < tolerance){
				return null;
			}else{
				const inv = utils.vector(3, () => []);
				for (let i = 0; i < 3; i++){
					const ii = (1 + i) % 3, ii1 = (ii + 1) % 3;
					for (let j = 0; j < 3; j++){
						const jj = (1 + j) % 3, jj1 = (jj + 1) % 3;
						inv[j][i] = (a[ii][jj] * a[ii1][jj1] - a[ii][jj1] * a[ii1][jj]) / determinant;
					}
				}
				return inv;
			}
		},
		LUPInverse: function (luP){
			const {lu, pivot} = luP, inverse = utils.vector(lu.length, () => []);
			for (let j = 0; j < lu.length; j++){
				for (let i = 0; i < lu.length; i++){
					inverse[i][j] = pivot[i] === j ? 1.0 : 0.0;
					for (let k = 0; k < i; k++){
						inverse[i][j] -= lu[i][k] * inverse[k][j];
					}
				}
				for (let i = lu.length - 1; i >= 0; i--){
					for (let k = i+1; k < lu.length; k++){
						inverse[i][j] -= lu[i][k] * inverse[k][j];
					}
					inverse[i][j] /= lu[i][i];
				}
			}
			return inverse;
		},
		symCholeskyInverse: function(L){
			let n = L.length;
			let  result = utils.vector(L.length, (v, i) => utils.vector(i+1, (w, j) => (i == j ? 1.0 : 0.0)));
			for (let k = 0; k < n; k++) {
			  for (let j = 0; j <= k; j++) {
			    for (let i = 0; i < k; i++) {
			      if (i >= j) result[k][j] -= result[i][j] * L[k][i];
			    }
			    result[k][j] /= L[k][k];
			  }
			}
			for (let k = n - 1; k >= 0; --k) {
			  for (let j = 0; j <= k; j++) {
			    for (let i = k + 1; i < n; i++) {
			      if (i >= j) result[k][j] -= result[i][j] * L[i][k];
			    }
			    result[k][j] /= L[k][k];
			  }
			}
			return result;			
		}
	};
});