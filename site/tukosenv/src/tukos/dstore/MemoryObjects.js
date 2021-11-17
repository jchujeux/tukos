define([
	'dojo/_base/declare',
	'tukos/dstore/_StoreIdgMixin',
	'tukos/dstore/_StoreObjectsMixin',
	'dstore/Memory',
	'dstore/Trackable',
], function (declare, _StoreIdgMixin, _StoreObjectsMixin, Memory, Trackable) {
	return declare([Memory, _StoreIdgMixin, _StoreObjectsMixin, Trackable], {
    });
});
