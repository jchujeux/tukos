define([
    "dojo/_base/kernel",
    "dojo/topic"
], function (kernel, topic) {

    var w = kernel.global;
    var cb ="_googleApiLoadCallback";

    return {
        load: function (param, req, loadCallback) {
            if (!cb) return;
            w[cb] = function () {
                delete w[cb];
                cb = null;
                loadCallback();
            }
            require([param + "&callback=" + cb]);
        }
    };

});