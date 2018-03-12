define([
	'dojo/_base/declare',
	'tukos/dstore/_StoreIdgMixin',
	'tukos/dstore/_StoreObjectsMixin',
	'dstore/Memory',
	'dstore/Trackable',
	'tukos/dstore/TreeObjects'
], function (declare, _StoreIdgMixin, _StoreObjectsMixin, Memory, Trackable, Tree) {
	return declare([Memory, _StoreIdgMixin, _StoreObjectsMixin, Trackable, Tree], {
    });
});
