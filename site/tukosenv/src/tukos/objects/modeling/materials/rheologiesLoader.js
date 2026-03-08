define (["dojo/_base/lang", "dojo/_base/Deferred", "dojo/when", "tukos/PageManager"],
		function(lang, Deferred, when, Pmg){
	let rheologiesBeingInstantiated = 0;
	const tukosRheologies = {LinearIsotropic: true, PressureThresholdCorrection: true};
	return {
            rheologiesPath: "tukos/objects/modeling/Materials/",
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
            load: function(rheologyType){
			if (this.loadedRheologies[rheologyType]){
                    return this.loadedRheologies[rheologyType];
                }else if(this.loadingRheologies[rheologyType]){
                	return this.loadingRheologies[rheologyType];
                }else{
                    var location = this.rheologyLocation(rheologyType) || null;
                    if (location){
                        this.loadingRheologies[rheologyType] =  new Deferred();
                    	require([location], lang.hitch(this, function(Rheology){
                            this.loadedRheologies[rheologyType] = Rheology;
                            this.loadingRheologies[rheologyType].resolve(Rheology);
                        }));
                        return this.loadingRheologies[rheologyType];
                    }else{
                    	console.log('programmer error - Loading rheology - unknown rheologyType: ' + rheologyType);
                    	return null;
                    }
                }
            }, 
			isLoaded: function(rheologyType){
				return this.loadedRheologies[rheologyType];
			},
			module: function(rheologyType){
				return this.loadedRheologies[rheologyType];
			},
			isTukosRheology(rheologyType){
				return tukosRheologies[rheologyType];
			},
            rheologyLocation: function(rheologyType){
                return this.rheologiesPath + rheologyType;
            }
        };
    }
);
