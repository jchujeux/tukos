var profile = (function(){
    var testResourceRe = /^dojoFixes\/tests\//,
        // checks if mid is in dojoFixes/tests directory

        copyOnly = function(filename, mid){
            var list = {
                "dojoFixes/.git": true,
				"dojoFixes/dojoFixes.profile": true,
                // we shouldn't touch our profile
                "dojoFixes/package.json": true
                // we shouldn't touch our package.json
            };
            return (mid in list) ||
                (/^dojoFixes\/resources\//.test(mid)
                    && !/\.css$/.test(filename)) ||
                /(png|jpg|jpeg|gif|tiff)$/.test(filename);
            // Check if it is one of the special files, if it is in
            // dojoFixes/resource (but not CSS) or is an image
        };

    return {
        resourceTags: {
            test: function(filename, mid){
                return testResourceRe.test(mid) || mid=="dojoFixes/tests";
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