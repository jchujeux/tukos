var profile = (function(){
    var testResourceRe = /^tukos\/tests\//,
        // checks if mid is in tukos/tests directory

        copyOnly = function(filename, mid){
            var list = {
                "tukos/.git": true,
				"tukos/tukos.profile": true,
                // we shouldn't touch our profile
                "tukos/package.json": true,
                // we shouldn't touch our package.json
                "tukos/evalutils": true,
                "tukos/menuUtils": true,
                "tukos/widgets/WidgetsLoader": true,
            };
            return (mid in list) ||
                (/^tukos\/resources\//.test(mid)
                    && !/\.css$/.test(filename)) ||
                /(png|jpg|jpeg|gif|tiff)$/.test(filename);
            // Check if it is one of the special files, if it is in
            // tukos/resource (but not CSS) or is an image
        };

    return {
        resourceTags: {
            test: function(filename, mid){
                return testResourceRe.test(mid) || mid=="tukos/tests";
                // Tag our test files
            },

            copyOnly: function(filename, mid){
                return copyOnly(filename, mid);
                // Tag our copy only files
            },

            amd: function(filename, mid){
                return !testResourceRe.test(mid)
                    && !copyOnly(filename, mid)
                    && /\.js$/.test(filename);
                // If it isn't a test resource, copy only,
                // but is a .js file, tag it as AMD
            }
        }
    };
})();