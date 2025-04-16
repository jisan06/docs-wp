/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(function(wp, global)
{
    document.addEventListener("DOMContentLoaded", function()
    {
        var namespace = 'easydoc';
        var el = wp.element.createElement;
        var components = wp.components;

        if (typeof global.FoliokitBlockConfigurations === 'undefined') { global.FoliokitBlockConfigurations = {}; }
        if (typeof global.EasyDoc=== 'undefined') { global.EasyDoc= {}; }
        if (typeof global.EasyDoc.Block === 'undefined') { global.EasyDoc.Block = {}; }

        if (typeof global.EasyDoc.Block.NodeObserver === 'undefined')
        {
            (function($)
            {
                global.EasyDoc.Block.NodeHandlers = {};

                global.EasyDoc.Block.NodeObserver = new MutationObserver(function(mutations, observer)
                {
                    $.each(mutations, function (key, mutation)
                    {
                        var nodes = $(mutation.addedNodes);

                        $.each(global.EasyDoc.Block.NodeHandlers, function(selector, callbacks)
                        {
                            var node = nodes.is(selector) ? nodes : nodes.find(selector);

                            if (node.length)
                            {
                                $.each(callbacks, function(key, callback) {
                                    callback(node);
                                });
                            }
                        });
                    });
                });

                global.EasyDoc.Block.NodeObserver.observe($('#wpcontent')[0], {childList: true, subtree: true});

            })(kQuery);
        }

        EasyDoc.Block.icon = el('svg', {
            width: 24,
            height: 24,
            viewBox: "0 -50 400 400",
            fillRule: "evenodd",
            clipRule: "evenodd",
            strokeLinejoin: "round",
            strokeMiterlimit: 1.414
        }, [
            el('path', { d: "M300 190.766c-.003-19.314-4.91-38.863-15.238-56.75-15.213-26.345-39.769-45.191-69.153-53.061-29.387-7.87-60.077-3.83-86.417 11.373-10.383 5.998-13.939 19.274-7.947 29.655 5.989 10.386 19.268 13.939 29.655 7.944 16.295-9.409 35.295-11.907 53.472-7.041 18.185 4.882 33.382 16.539 42.789 32.841 19.427 33.645 7.857 76.829-25.794 96.258-10.384 6.001-13.934 19.277-7.945 29.657 5.998 10.381 19.275 13.94 29.656 7.948 36.494-21.076 56.916-59.439 56.922-98.824" } ),
            el('path', { d: "M230.226 42.998c-.003-7.383-3.765-14.572-10.559-18.645-35.48-21.251-79.752-21.618-115.544-.956-26.349 15.21-45.188 39.767-53.062 69.153-7.879 29.383-3.836 60.075 11.38 86.417 10.232 17.729 25.035 32.369 42.817 42.342 10.457 5.865 23.684 2.15 29.55-8.311 5.865-10.451 2.146-23.681-8.308-29.55-10.976-6.158-20.129-15.21-26.466-26.194-9.412-16.296-11.911-35.287-7.038-53.472 4.87-18.17 16.53-33.374 32.837-42.786 22.149-12.789 49.552-12.558 71.522.607 10.285 6.159 23.617 2.816 29.784-7.472a21.606 21.606 0 0 0 3.087-11.133" } ),
            el('path', { d: "M227.799 178.126c-.003-.618-.006-1.228-.011-1.842-.199-11.986-10.067-21.553-22.057-21.356-11.866.188-21.358 9.871-21.358 21.699v.351c.45 25.661-13.092 49.469-35.234 62.251-16.305 9.416-35.296 11.91-53.478 7.038-18.183-4.873-33.38-16.526-42.789-32.835-6.338-10.974-9.601-23.465-9.45-36.013.004-.096.004-.188.004-.278 0-11.863-9.548-21.556-21.44-21.701-11.99-.148-21.827 9.449-21.975 21.437-.284 20.405 5.023 40.531 15.265 58.269 15.203 26.337 39.764 45.186 69.147 53.056 29.383 7.873 60.075 3.833 86.417-11.373 35.255-20.363 56.956-58.052 56.959-98.703" } )
        ]);

        if (typeof wp.blocks.registerBlockCollection !== 'undefined') {

            var _ = Foliokit.translate;

            wp.blocks.registerBlockCollection(namespace, {
                title: _('EasyDocmenu documents'),
                icon: EasyDoc.Block.icon,
            });
        }

        EasyDoc.Block.setNodeHandler = function(node, callback, override = false)
        {
            if (!override)
            {
                if (typeof global.EasyDoc.Block.NodeHandlers[node] === 'undefined') {
                    global.EasyDoc.Block.NodeHandlers[node] = [];
                }

                global.EasyDoc.Block.NodeHandlers[node].push(callback);
            }
            else global.EasyDoc.Block.NodeHandlers[node] = [callback];
        }

		EasyDoc.Block.addHandler = function(config)
		{
			config.element.change(function() {
				EasyDoc.Block[config.handler](config);
			});

			EasyDoc.Block[config.handler](config);
		}

        EasyDoc.Block.selectVisibility = function(config)
        {
			let select = config.element;

			kQuery.each(config.values, function(key, value)
			{
				let selector = '.k-visibility_' + value + '__hidden';

				var nodes = kQuery(selector);

				if (select.val() === value)
				{
					if (nodes.length) {
						nodes.hide();
					}

					EasyDoc.Block.setNodeHandler(selector, function(nodes) {
						nodes.hide();
					}, true);
				}
				else
				{
					// Filter the set

					let filter = '.k-visibility_' + select.val() + '__hidden';

					nodes = nodes.not(filter);

					if (nodes.length) {
						nodes.show();
					}

					EasyDoc.Block.setNodeHandler(selector, function(nodes) {
						nodes.not(filter).show()
					}, true);
				}
			});
        }

		EasyDoc.Block.toggleVisibility = function(config)
        {
			let nodes = kQuery(config.selector), options = wp.data.select( 'core/block-editor' ).getSelectedBlock().attributes;

			if(options[config.attribute])
			{
				if (nodes.length) {
					nodes.show();
				}

				EasyDoc.Block.setNodeHandler(config.selector, function(nodes) {
					nodes.show();
				}, true);
			}
			else
			{
				if (nodes.length) {
					nodes.hide();
				}

				EasyDoc.Block.setNodeHandler(config.selector, function(nodes) {
					nodes.hide();
				}, true);
			}
        }

        EasyDoc.Block.setConfig = function(config)
        {
            if (config.routes)
            {
                for (const name in config.routes)
                {
                    if (config.attributes[name]) {
                        config.attributes[name].route = config.routes[name];
                    }
                }
            }
        }

        EasyDoc.Block.getAutocompleteControl = function(name, config, props)
        {
            var elementId = name+'_element_'+props.clientId;
            var attribute = name;
            var cacheAttribute = name+'_cache';

            EasyDoc.Block.setNodeHandler('#' + elementId, function(node)
            {
                var select2Options = Foliokit.getSelect2Options({
                    "multiple": config.multiple || false,
                    "validate": false,
                    "queryVarName": "search",
                    "placeholder": "- "+(config.placeholder || config.label || 'Select')+" -",
                    "allowClear": config.deselect || true,
                    "value": config.value || "id",
                    "text": config.title || "title",
                    "selected":props.attributes[attribute],
                    "url": config.route
                });

                var select2Element = kQuery("#"+elementId);

                if (select2Element.length && !select2Element.data('select2'))
                {
                    select2Element.select2(select2Options);

                    if (props.attributes[cacheAttribute])
                    {
                        var options = select2Element.data('select2').options.options;

                        if (options.multiple)
                        {
                            // Multi-selector

                            var values = [];

                            kQuery.each(props.attributes[cacheAttribute], function(idx, option)
                            {
                                select2Element.append(new Option(option.text, option.id, false, false));
                                values.push(option.id);
                            });

                            if (values.length) select2Element.val(values);
                        }
                        else
                        {
                            select2Element.select2("trigger", "select", {
                                data: props.attributes[cacheAttribute]
                            });
                        }

                        select2Element.trigger('change');
                    }

                    select2Element.on("change", function()
                    {
                        var attrs = {},
                            data = select2Element.select2('data'),
                            cache = [];

                        attrs[attribute] = select2Element.val();

                        var options = select2Element.data('select2').options.options;

                        if (options.multiple)
                        {
                            // Multi-selector

                            kQuery.each(data, function (idx, el) {
                                cache.push({id: el.id, text: el.text});
                            });

                            attrs[cacheAttribute] = cache.length ? cache : null;
                        }
                        else attrs[cacheAttribute] = data && data[0] ? {id: data[0].id, text: data[0].text} : null;

                        props.setAttributes(attrs)
                    });
                }
            }, true);

            return el('div', {
                'class': 'k-ui-ltr k-ui-container k-ui-namespace com_easydoc',
                style: {marginBottom: 24}
            }, el('select', {
                'id': elementId
            }));
        };

        EasyDoc.Block.getSelect2Control = function(name , config, props, callback)
        {
            var elementId = name + '_element';

            EasyDoc.Block.setNodeHandler('#' + elementId, function(node)
            {
                var select2Options = Object.assign({
                    "multiple":config.multiple || false,
                    theme: "bootstrap",
                    "placeholder": "- "+(config.placeholder || config.label || 'Select')+" -",
                    "allowClear": config.deselect || true,
                }, config.select2Options || {});

                var select2Element = kQuery("#" + elementId);

                if (select2Element.length && !select2Element.data('select2'))
                {
                    select2Element.select2(select2Options);

                    for (const tag of config.options)
                    {
                        let option = new Option(tag.label, tag.value, false, false);
                        select2Element.append(option);
                    }

                    select2Element.val(props.attributes[name]);

                    select2Element.trigger("change");

                    select2Element.on("change", function()
                    {
                        var attrs = {};

                        attrs[name] = select2Element.val();

                        props.setAttributes(attrs)
                    });
                }
            });

            return el('div', {
                'class': 'k-ui-ltr k-ui-container k-ui-namespace com_easydoc',
                style: {marginBottom: 24}
            }, el('select', {
                id: elementId,
                value: props.attributes[name]
            }));
        };

        EasyDoc.Block.getControl = function(name, config, props, callback) {
            var element = null;

            if (config.control === 'toggle') {
                element = el(components.ToggleControl, {
                    className: config.className,
                    label: config.label,
                    options: config.options,
                    help: config.help,
                    checked: props.attributes[name],
                    onChange: function(value) {
                        var attrs = {};
                        attrs[name] = value;
                        props.setAttributes(attrs)
                    },
                });
            } else if (config.control === 'select') {
                element = el(components.SelectControl, {
                    className: config.className,
                    label: config.label,
                    options: config.options,
                    help: config.help,
                    value: props.attributes[name],
                    onChange: function(value) {
                        var attrs = {};
                        attrs[name] = value;
                        props.setAttributes(attrs)
                    },
                });
            } else if (config.control === 'select2') {
                element = EasyDoc.Block.getSelect2Control(name, config, props);
            } else if (config.control === 'textarea') {
                element = el(components.TextareaControl, {
                    className: config.className,
                    label: config.label,
                    help: config.help,
                    value: props.attributes[name],
                    placeholder: config.placeholder,
                    onChange: function(value) {
                        var attrs = {};
                        attrs[name] = value;
                        props.setAttributes(attrs)
                    },
                })
            } else if (config.control === 'autocomplete') {
                element = EasyDoc.Block.getAutocompleteControl(name, config, props);
            } else if (config.control === 'input') {
                element = el(components.TextControl, {
                    className: config.className,
                    label: config.label,
                    value: props.attributes[name],
                    onChange: function(value) {
                        var attrs = {};
                        attrs[name] = value;
                        props.setAttributes(attrs)
                    },
                })
            }

            if (callback) {
                callback(element);
            }

            return element;
        };
    });
})(wp, window);
