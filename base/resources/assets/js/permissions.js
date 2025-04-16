/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

var EasyDoc= EasyDoc|| {};

(function($)
{
    EasyDoc.permissions = function (options)
    {
        var me = arguments.callee;

        if (this instanceof me)
        {
            // Constructor

            var my =
                {
                    init: function (options)
                    {
                        var that = this;

                        this.options = $.extend(true, {
                            selector_template: '#{type}-{modifier}-{action}',
                            toggle_template: '#{type}-{action}-toggle',
							clear_template: '#{type}-{action}-clear',
                            type: 'usergroups',
                            callbacks: {},
                            modifiers: {
                                current: 'permissions',
                                inherited: 'inherited',
                            },
                            fixed_groups: [],
                            default: false,
                            auto_bind: true,
                            actions: [],
                        }, options);

                        this.options.exclusive_groups = []

                        $.each(this.options.fixed_groups, function(name, config)
                        {
                            if (config.exclusive) {
                                that.options.exclusive_groups.push(config.id);
                            }
                        });

                        this.view_selectors = {};
                        this.selectors = [];

                        $.each(this.options.actions, function (idx, action)
                        {
                            if (action.indexOf('view_') === -1)
                            {
                                var selector = $(that.options.selector_template
                                    .replace('{type}', that.options.type)
                                    .replace('{modifier}', that.options.modifiers.current)
                                    .replace('{action}', action));

                                if (selector.length)
                                {
                                    selector.data('action', action);

                                    var parts = action.split('_');

                                    selector.data('resource', parts[1]);

                                    selector.data('type', that.options.type)
                                    that.selectors.push($(selector));
                                }
                            }
                            else
                            {
                                var view_selector = $(that.options.selector_template
                                    .replace('{type}', that.options.type)
                                    .replace('{modifier}', that.options.modifiers.current)
                                    .replace('{action}', action));

                                if (view_selector.length)
                                {
                                    var resource = action.split('_')[1];
                                    view_selector.data('action', action);
                                    view_selector.data('resource', resource);
                                    view_selector.data('type', that.options.type);
                                    that.view_selectors[resource] = view_selector;
                                }
                            }
                        });

                        this.all_selectors = $.merge($.merge([], this.selectors), Object.values(this.view_selectors));

                        if (this.options.auto_bind) this.bind();

                        this.setOptions();

                        return this;
                    },
                    bind: function ()
                    {
                        var that = this;

                        $.each(this.all_selectors, function (idx, selector)
                        {
                            selector.on('select2:select', function (e)
                            {
                                var data = e.params.data,
                                    view_selector = that.view_selectors[selector.data('resource')];

                                if (!that.isExclusive(data.id))
                                {
                                    var values = selector.val();

                                    $.each(values, function (idx, value) {
                                        if (that.isExclusive(value)) delete values[idx]; // Remove any exclusive fixed group that was previosly set
                                    });

                                    selector.val(values);

                                    if (!that.hasSelection(view_selector, ['public', 'registered'], false))
                                    {
                                        // Set the value in view

                                        var view_values = view_selector.val();

                                        view_values.push(data.id);

                                        if (that.options.type == 'users') {
                                            that.setOption(that.getOption(selector, data.id), selector.data('resource'));
                                        }

                                        //that.view_selector.val($.unique(view_values));
                                    }
                                }
                                else
                                {
                                    selector.val(data.id); // Remove other selections when selecting a fixed group

                                    if (data.id == that.options.fixed_groups['public']['id'] && !that.hasSelection(view_selector, 'public')) {
                                        view_selector.val(data.id); // Set view public
                                    }
                                }

                                // Usergroups/users must view if they can do other stuff

                                if (!selector.data('no-sync') && (that.isSyncable(that.getOption(selector, data.id)))) {
                                    that.sync(selector.data('resource'));
                                }

								that.setOptions();

                                //that.setLocks();

                                if (typeof that.options.callbacks.select === 'function') {
                                    that.options.callbacks['select'].call(that, selector);
                                }

                                selector.trigger('change');
                            });
                        });

                        $.each(this.all_selectors, function (idx, selector)
                        {
                            selector.on('select2:unselect', function (e)
                            {
                                var value = selector.val();

                                if (!value.length)
                                {
                                    if (that.options['default'])
                                    {
                                        var value = that.options.fixed_groups[that.options['default']]['id'];

                                        selector.val(value);

                                        // Lock default values

                                        var option = that.getOption(selector, value);

                                        $(option).attr('locked', 'locked');

                                        selector.trigger({
                                            type: 'select2:select',
                                            params: {
                                                data: {id: value}
                                            }
                                        });
                                    }
                                }

                                //that.sync();

                                that.setOptions(selector.data('resource'));

                                that.setLocks(selector.data('resource'));

                                if (typeof that.options.callbacks.unselect === 'function') {
                                    that.options.callbacks['unselect'].call(that, selector);
                                }

                                selector.trigger('change');
                            });

                            selector.on('select2:close', function ()
                            {
                                if (!selector.val().length) {
                                    that.showToggle(selector);
                                }
                            });

                            that.getButton(selector).click(function (e)
                            {
                                e.preventDefault();
                                that.hideToggle(selector);
                            });

							that.getButton(selector, 'clear').click(function (e)
                            {
                                e.preventDefault();
                                that.showToggle(selector);
								selector.val([]);
								selector.trigger('change');
								that.setOptions(selector.data('resource'));
								that.setLocks(selector.data('resource'));
                            });
                        });
                    },
                    isExclusive: function(value)
                    {
                        return this.options.exclusive_groups.includes(value);
                    },
					isSyncable: function(option)
					{
						let el = $(option);

						return typeof el.data('usergroup-type') === "undefined" || el.data('usergroup-syncable');
					},
                    showToggle: function (selector)
                    {
                        var toggle = this.getButton(selector);

                        if (toggle.length)
                        {
                            selector.parent().addClass('k-hidden');
							this.getInherited(selector).parent().removeClass('k-hidden');
                            toggle.removeClass('k-hidden');
							this.getButton(selector, 'clear').addClass('k-hidden');
                        }
                    },
                    hideToggle: function (selector)
                    {
                        var toggle = this.getButton(selector);

                        if (toggle.length)
                        {
							this.getInherited(selector).parent().addClass('k-hidden');
							toggle.addClass('k-hidden');
							selector.parent().removeClass('k-hidden');
							this.getButton(selector, 'clear').removeClass('k-hidden');
                        }
                    },
                    getButton: function (selector, type = 'toggle')
                    {
						let template = type == 'toggle' ? this.options.toggle_template : this.options.clear_template;

                        return $(template
                            .replace('{type}', selector.data('type'))
                            .replace('{action}', selector.data('action')));
                    },
                    getValues: function(selector, inherit = true)
                    {
                        var values = selector.val();

                        if (!selector.val().length && inherit)
                        {
                            var inherited = this.getInherited(selector);

                            if (inherited.length) {
                                values = inherited.val();
                            }
                        }

                        return values;
                    },
                    sync: function(resource)
                    {
                        var view_selector = this.view_selectors[resource],
                            inherited = this.getValues(view_selector),
                            current = this.getValues(view_selector, false),
                            public = this.options.fixed_groups['public']['id'],
                            registered = this.options.fixed_groups['registered']['id'],
                            that = this;

                        // Sync with current permissions

                        if (!this.hasSelection(view_selector, ['public', 'registered'], false))
                        {
                            $.each(this.selectors.filter(selector => selector.data('resource') === resource), function(idx, selector)
                            {
								if (!selector.data('no-sync'))
								{
									$.each(selector.val(), function(idx, value)
									{
										if (!inherited.includes(value))
										{
											if (that.options.type == 'users' && value > 0) {
												that.setOption(that.getOption(selector, value), selector.data('resource'));
											}

											if (that.isSyncable(that.getOption(selector, value))) current.push(value);
										}
									});
								}
                            });

                            if (!view_selector.val().length && current.length)
                            {
                                // Go through all inherited selectors and push inherited values if there are no overrides
                                // on the corresponding selector

                                $.each(this.all_selectors.filter(selector => selector.data('resource') === resource), function(idx, selector)
                                {
									if (!selector.data('no-sync'))
									{
										var inherited = that.getInherited(selector);

										if (!selector.val().length && inherited.val().length)
										{
											$.each(inherited.val(), function(idx, value)
											{
												if (!current.includes(value))
												{
													if (that.options.type == 'users') {
														that.setOption(that.getOption(inherited, value), selector.data('resource'));
													}

													if (that.isSyncable(that.getOption(selector, value))) current.push(value);
												}
											});
										}
									}
                                });
                            }
                        }

                        $.each([public, registered], function(key, value)
                        {
                            if (current.includes(value))
                            {
                                var parent_inherited = that.getInherited(view_selector);

                                if (parent_inherited.length && parent_inherited.val().length) {
                                    current = parent_inherited.val().includes(value) ? [] : [value];
                                }

                                return false;
                            }
                        });

                        view_selector.val(current);

                        if (current.length) {
                            this.hideToggle(view_selector);
                        } else {
                            this.showToggle(view_selector);
                        }

                        this.setOptions(resource);

                        this.setLocks(resource);

                        view_selector.trigger('change');
                    },
                    hasSelection: function(selector, values, strict = true, inherit = true)
                    {
                        var result = false;

                        if (!$.isArray(values)) {
                            values = [values];
                        }

                        var selection = selector.val(), that = this;

                        $.each(values, function(idx, value)
                        {
                            if ((typeof value === 'string' || value instanceof String) && typeof that.options.fixed_groups[value] !== "undefined") {
                                value = that.options.fixed_groups[value]['id'];
                            }

                            if (selection && selection.includes(value))
                            {
                                result = true;

                                if (!strict) return false;
                            }
                            else
                            {
                                if (strict)
                                {
                                    result = false;
                                    return false;
                                }
                            }
                        });

                        if (inherit)
                        {
                            // Check if selection is made on inherited selector

                            var inherited = this.getInherited(selector);

                            if (!result && inherited.length && !selection.length)  {
                                result = this.hasSelection(inherited, values, strict);
                            }
                        }

                        return result;
                    },
                    resetOptions: function(resource = null)
                    {
                        var selectors = resource === null ? this.all_selectors : this.all_selectors.filter(selector => selector.data('resource') === resource);

                        $.each(selectors, function (idx, selector)
                        {
                            selector.find('option').each(function (idx, option)
                            {
                                if ($(option).attr('hidden') == 'hidden') {
                                    $(option).removeAttr('hidden');
                                }
                            });

                            selector.trigger('change');
                        });
                    },
                    setOptions: function(resource = null)
                    {
                        this.resetOptions(resource); // Reset all options first

                        // Hide public from selectors if not set or inherited

                        var hide_public = this.options.default ? false : true,
                            that = this;

                        var selectors = resource === null ? this.all_selectors : this.all_selectors.filter(selector => selector.data('resource') === resource);

                        $.each(selectors, function (idx, selector)
                        {
							if (!selector.data('no-sync'))
							{
								var view_selector = that.view_selectors[selector.data('resource')];

								if (selector.data('action') != view_selector.data('action')
									&& that.hasSelection(selector, ['public', 'registered'], false, false))
								{
									var exclude = [that.options.fixed_groups['public']['id']];

									if (that.hasSelection(selector, ['registered'], false, false)) {
										exclude.push(that.options.fixed_groups['registered']['id']);
									}

									view_selector.find('option').each(function (idx, option)
									{
										if (!exclude.includes(option.value)) {
											$(option).attr('hidden', 'hidden');
										}
									});

									view_selector.trigger('change');
								}


								if (hide_public && !that.hasSelection(that.getInherited(view_selector), 'public'))
								{
									selector.find('option').each(function (idx, option)
									{
										if (option.value == that.options.fixed_groups['public']) {
											$(option).attr('hidden', 'hidden');
										}
									});

									selector.trigger('change');
								}
							}
                        });
                    },
                    setLocks: function (resource)
                    {
                        this.resetLocks(resource); // Reset all locks first

                        var that = this,
							has_locks = false,
                            view_selector = this.view_selectors[resource],
                            view_values = view_selector.val(),
							public = this.options.fixed_groups['public']['id'],
							registered = this.options.fixed_groups['registered']['id'];

                        view_selector.find('option').each(function (idx, option)
                        {
                            $.each(that.selectors.filter(selector => selector.data('resource') === resource), function (idx, selector)
                            {
                                if (!selector.data('no-sync'))
                                {
                                    var values = selector.val();

                                    if (view_values.includes(option.value) && values.includes(option.value))
									{
                                        $(option).attr('locked', 'locked');
										has_locks = true;
                                    }

									if (option.value == public && (values.includes(registered) && view_values.includes(public)))
									{
										// In this case the view public value should be locked

										$(option).attr('locked', 'locked');
										has_locks = true;
									}

                                    // Check on inherited selectors

                                    /*if (!values.length)
                                    {
                                        var inherited = that.getInherited(selector);

                                        if (inherited.length)
                                        {
                                            var inherited_values = inherited.val() === null ? [] : inherited.val();

                                            if (option.value > 0 && inherited_values.includes(option.value)) {
                                                $(option).attr('locked', 'locked');
                                            }
                                        }
                                    }*/
                                }
                            });
                        });

                        view_selector.trigger('change');

						if (has_locks) {
							this.getButton(view_selector, 'clear').prop("disabled","disabled");
						} else {
							this.getButton(view_selector, 'clear').prop("disabled","");
						}
                    },
                    resetLocks: function (resource)
                    {
                        var view_selector = this.view_selectors[resource],
                            view_value = view_selector.val();

                        // Only proceed disabling locks if view selector selection isn't default

                        if(view_value.length && (view_value[0] != this.options.fixed_groups[this.options.default]))
                        {
                            var that = this;

                            var values = [];

                            $.each(that.selectors.filter(selector => selector.data('resource') === resource), function (idx, selector)
                            {
                                if (!selector.data('no-sync'))
                                {
                                    var selected = selector.val();

                                    $.merge(values, selected);
                                }
                            });

                            view_selector.find('option').each(function (idx, option) {
                                if (!values.includes(option.value)) {
                                    $(option).removeAttr('locked');
                                }
                            });

                            view_selector.trigger('change');
                        }
                    },
                    setOption: function (option, resource) // Sets a new option on view selector if it doesn't exists already
                    {
                        var view_selector = this.view_selectors[resource];

                        if (view_selector.find('option[value="' + option.value + '"]').length == 0) {
                            var option = new Option(option.text, option.value, false, false);
                            view_selector.append(option);
                            view_selector.trigger('change');
                        }
                    },
                    getOption: function (selector, value) // Returns option object for a given value and selector
                    {
                        var result = false;

                        selector.find('option').each(function (idx, option)
                         {
                            if (option.value === value) {
                                result = option;
                                return false;
                            }
                        });

                        return result;
                    },
                    getInherited: function (selector)
                    {
                        var inherited = $(); // Empty object

                        if (this.options.selector_template.search('{modifier}') !== -1)
                        {
                            inherited = $(this.options.selector_template
                                .replace('{type}', selector.data('type'))
                                .replace('{modifier}', this.options.modifiers.inherited)
                                .replace('{action}', selector.data('action')));
                        }

                        return inherited;
                    }
                };

            if (options) {
                my.init(options);
            } else {
                my.init({});
            }

            return my;
        }
        else return new me(options); // Factory
    }
})(kQuery);
