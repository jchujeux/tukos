"use strict";
define (["dojo/_base/lang", "dojo/_base/Deferred", "dojo/when", "tukos/PageManager"],
		function(lang, Deferred, when, Pmg){
		let rheologiesBeingInstantiated = 0;
		const rheologiesPath = "tukos/objects/modeling/rheologies/",
			  problemTypesPath = {hydraulicspressure: 'diffusion/hydraulics/pressure/', hydraulicshead:  'diffusion/hydraulics/head', solidmechanics: 'solidmechanics/'},
			  rheologiesTypesPath = {linearisotropic: 'isotropic/Linear', Linearorthotropic: 'orthotropic/linear', thresholdcorrection: 'isotropic/ThresholdCorrection'};
		return {
            loadedRheologies: {}, loadingRheologies: {},
			instantiationCompleted: function(){
				if (rheologiesBeingInstantiated){
					var instantiationDfd = new Deferred(true), watcher;
					watcher = setInterval(function(){
							if (!rheologiesBeingInstantiated){
								instantiationDfd.resolve();
								clearInterval(watcher);
							}
						}, 100);
					return instantiationDfd;
				}else{
					return true;
				}
			},            
			instantiate: function(rheologyType, atts, optionalRheologyInstantiationCallback){
				rheologiesBeingInstantiated += 1;
				return when(this.load(rheologyType), lang.hitch(this, function(Rheology){
                    return this._instantiate(Rheology, atts, optionalRheologyInstantiationCallback);
                }));
            },
            _instantiate: function(Rheology, atts, optionalRheologyInstantiationCallback){// requires Rheology to be loaded
                var rheology = new Rheology(atts);
                if (optionalRheologyInstantiationCallback){
                    optionalRheologyInstantiationCallback(rheology);
                }
				rheologiesBeingInstantiated += -1;                
				return rheology;
            },
            load: function(problemType, rheologyType){
				const rheologyLocation = this.rheologyLocation(problemType, rheologyType);
				if (this.loadedRheologies[rheologyLocation]){
                    return this.loadedRheologies[rheologyLocation];
                }else if(this.loadingRheologies[rheologyLocation]){
                	return this.loadingRheologies[rheologyLocation];
                }else{
                    if (rheologyLocation){
                        this.loadingRheologies[rheologyLocation] =  new Deferred();
                    	require([rheologyLocation], lang.hitch(this, function(Rheology){
                            this.loadedRheologies[rheologyLocation] = Rheology;
                            this.loadingRheologies[rheologyLocation].resolve(Rheology);
                        }));
                        return this.loadingRheologies[rheologyLocation];
                    }else{
                    	console.log('programmer error - Loading rheology - unknown rheologyType: ' + rheologyType);
                    	return null;
                    }
                }
            }, 
			isLoaded: function(problemType, rheologyType){
				return this.loadedRheologies[this.rheologyLocation(problemType, rheologyType)];
			},
			module: function(problemType, rheologyType){
				return this.loadedRheologies[this.rheologyLocation(problemType, rheologyType)];
			},
            rheologyLocation: function(problemType, rheologyType){
                return rheologiesPath + problemTypesPath[problemType] + rheologiesTypesPath[rheologyType];
            }
        };
    }
);
