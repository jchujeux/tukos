define([
	'dojo/_base/declare',
	'dojo/_base/lang',
	'dojo/dom-construct',
	'dojo/dom-class',
	'dojo/on',
	'dojo/query',
	'dojo/when',
	'dgrid/util/has-css3',
	'dgrid/Tree'
], function (declare, lang, domConstruct, domClass, on, querySelector, when, has, Tree) {

	return declare(Tree, {
		expand: function (target, expand, noTransition) {
			// summary:
			//		Expands the row corresponding to the given target.
			// target: Object
			//		Row object (or something resolvable to one) to expand/collapse.
			// expand: Boolean?
			//		If specified, designates whether to expand or collapse the row;
			//		if unspecified, toggles the current state.

			if (!this._treeColumn) {
				return;
			}

			var grid = this,
				row = target.element ? target : this.row(target),
				isExpanded = !!this._expanded[row.id],
				hasTransitionend = has('transitionend'),
				promise;

			target = row.element;
			target = target.className.indexOf('dgrid-expando-icon') > -1 ? target :
				querySelector('.dgrid-expando-icon', target)[0];

			noTransition = noTransition || !this.enableTreeTransitions;

			if (target && target.mayHaveChildren && (noTransition || expand !== isExpanded)) {
				// toggle or set expand/collapsed state based on optional 2nd argument
				var expanded = expand === undefined ? !this._expanded[row.id] : expand;

				// update the expando display
				domClass.replace(target, 'ui-icon-triangle-1-' + (expanded ? 'se' : 'e'),
					'ui-icon-triangle-1-' + (expanded ? 'e' : 'se'));
				domClass.toggle(row.element, 'dgrid-row-expanded', expanded);

				var rowElement = row.element,
					container = rowElement.connected,
					containerStyle,
					scrollHeight,
					options = {};

				if (!container) {
					// if the children have not been created, create a container, a preload node and do the
					// query for the children
					container = options.container = rowElement.connected =
						domConstruct.create('div', { className: 'dgrid-tree-container' }, rowElement, 'after');
					var query = function (options) {
                        return when(grid._renderedCollection.getChildren(row.data), function(childCollection){
                            var results;
                            if (grid.sort && grid.sort.length > 0) {
								childCollection = childCollection.sort(grid.sort);
							}
							if (childCollection.track && grid.shouldTrackCollection) {
								container._rows = options.rows = [];
	
								childCollection = childCollection.track();
	
								// remember observation handles so they can be removed when the parent row is destroyed
								container._handles = [
									childCollection.tracking,
									grid._observeCollection(childCollection, container, options)
								];
							}
							if ('start' in options) {
								var rangeArgs = {
									start: options.start,
									end: options.start + options.count
								};
								results = childCollection.fetchRange(rangeArgs);
							} else {
								results = childCollection.fetch();
							}
							return results;
                        });
					};
					if ('level' in target) {
						// Include level information on query for renderQuery case
						// include on container for insertRow to detect in other cases
						container.level = query.level = target.level + 1;
					}

					// Add the query to the promise chain
					if (this.renderQuery) {
						promise = this.renderQuery(query, options);
					}
					else {
						// If not using OnDemandList, we don't need preload nodes,
						// but we still need a beforeNode to pass to renderArray,
						// so create a temporary one
						var firstChild = domConstruct.create('div', null, container);
						promise = this._trackError(function () {
							return grid.renderQueryResults(
								query(options),
								firstChild,
								lang.mixin({ rows: options.rows },
									'level' in query ? { queryLevel: query.level } : null
								)
							).then(function (rows) {
								domConstruct.destroy(firstChild);
								return rows;
							});
						});
					}

					if (hasTransitionend) {
						// Update height whenever a collapse/expand transition ends.
						// (This handler is only registered when each child container is first created.)
						on(container, hasTransitionend, this._onTreeTransitionEnd);
					}
				}

				// Show or hide all the children.

				container.hidden = !expanded;
				containerStyle = container.style;

				// make sure it is visible so we can measure it
				if (!hasTransitionend || noTransition) {
					containerStyle.display = expanded ? 'block' : 'none';
					containerStyle.height = '';
				}
				else {
					if (expanded) {
						containerStyle.display = 'block';
						scrollHeight = container.scrollHeight;
						containerStyle.height = '0px';
					}
					else {
						// if it will be hidden we need to be able to give a full height
						// without animating it, so it has the right starting point to animate to zero
						domClass.add(container, 'dgrid-tree-resetting');
						containerStyle.height = container.scrollHeight + 'px';
					}
					// Perform a transition for the expand or collapse.
					setTimeout(function () {
						domClass.remove(container, 'dgrid-tree-resetting');
						containerStyle.height =
							expanded ? (scrollHeight ? scrollHeight + 'px' : 'auto') : '0px';
					}, 0);
				}

				// Update _expanded map.
				if (expanded) {
					this._expanded[row.id] = true;
				}
				else {
					delete this._expanded[row.id];
				}
			}

			// Always return a promise
			return when(promise);
		}
	});
});
