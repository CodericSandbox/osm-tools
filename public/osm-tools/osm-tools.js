(function($) {
    window.OsmTools = window.OsmTools || {
        
        /**
         * Uses the container given by its identifier to render a tree
         * of Regions.
         * Uses lazyloading for the child nodes.
         * Allows to store the list of selected regions as JSON encoded list
         * in the field given by selectedField
         * 
         * @param {string} identifier
         * @param {string} selectedField
         * @returns {void}
         */
        createRegionTree: function(identifier, selectedField) {
            $(identifier).on('select_node.jstree', function (e, data) {
                // it does not make sense to select a region and some or all
                // of its subregions as they are (hopefully) included in the
                // parent -> unselect the children and the parents, only other
                // non-related regions (e.g. in the same level) stay selected
            
                // @todo use obj.siblings('.jstree-open') as .on('open_node.jstree') ?
                function recursiveDeselect(node) {
                    var children = data.instance.get_node(node).children;
                    if (!children || !children.length) {
                        return;
                    }
                    for (var i = 0; i < children.length; i++) {
                        data.instance.deselect_node(children[i]);
                        recursiveDeselect(children[i]);
                    }
                }

                recursiveDeselect(data.node.id);
                data.instance.deselect_node(data.node.parents);
            })
            .on('changed.jstree', function(e, data) {
                // save the currently selected IDs
                if (selectedField && $(selectedField).length) {
                    $(selectedField).val(JSON.stringify(data.selected));
                }
            })
            .on('open_node.jstree', function (e, data) {
                // collapse all other open branches when a node is selected
                var obj = data.instance.get_node(data.node, true);
                if(obj) {
                    obj.siblings('.jstree-open').each(function () {
                        data.instance.close_node(this, 0);
                    });
                }
            }).jstree({
                'plugins' : ['checkbox'],
                'core' : {
                    animation: 200, 
                    themes: {
                        variant : 'small',
                        icons: false
                    },
                    data : {
                        url : window.BASE_PATH + '/osmtools/json/jstree',
                        async: false,
                        data: function(node) {
                            var parent = node.id;
                            if (parent === "#") {
                                parent = '';
                            }                      
                            var ret = {
                                parent: parent,
                            }; 
                            return ret;                                       
                        },
                        type : "GET", 
                    }
                },
                checkbox : {
                    three_state: false,
                }
            });
        }
    };
}(jQuery));
