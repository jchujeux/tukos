define([
	'dojo/_base/declare',
	'tukos/dstore/_StoreObjectsMixin',
	'tukos/dstore/UserFilters',
	'dstore/Memory',
], function (declare, _StoreObjectsMixin, UserFilters, Memory) {
	return declare([Memory, _StoreObjectsMixin, UserFilters], {
    });
});
