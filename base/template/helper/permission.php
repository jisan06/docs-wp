<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class TemplateHelperPermission extends TemplateHelperListbox
{
    static $_select2_ready = false;

    public function users($config = array())
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'options'          => ['allowClear' => false],
            'options_callback' => 'setEasyDocPermissionsOptions',
            'init_callback'    => 'initEasyDocPermissions',
            'type'             => 'users',
            'inherited'        => false,
            'attribs'          => array('multiple' => true)
        ]);

        return $this->_prepareSelect2(parent::users($config));
    }

    public function usergroups($config = array())
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'inherited' => false,
            'type'      => 'usergroups',
            'filter'    => array('sort' => 'name', 'hide_admin' => true),
            'attribs'   => array('multiple' => true, 'fixed' => true),
            'label'     => 'displayname'
        ]);

        if ($config->attribs->fixed)
        {
            $config->append([
                'select2_options' => [
                    'options'          => [
                        'allowClear' => false,
                        'inherited'  => $config->inherited
                    ],
                    'options_callback' => 'setEasyDocPermissionsOptions',
                    'init_callback'    => 'initEasyDocPermissions',
                ]
            ]);

            $html = $this->_prepareSelect2(parent::usergroups($config));
        }
        else $html = parent::usergroups($config);

        return $html;
    }

    protected function _prepareSelect2($html)
    {
        if (!self::$_select2_ready)
        {
            $html .= '<script>
            function setEasyDocPermissionsOptions(options)
            {
                var fixed = ' . \EasyDocLabs\WP::wp_json_encode(ModelEntityUsergroup::getFixed()) . '

                options.templateSelection = function(option, container)
                {
                    var $option = kQuery(\'.select2 option[value="\'+option.id+\'"]\');

                    if (kQuery(option.element).attr(\'locked\'))
                    {
                        kQuery(container).addClass("select2-option__locked");
                        option.locked = true;
                    }

                    if (fixed[option.id] !== undefined)
                    {
                        var class_name = "select2-option__" + fixed[option.id];

                        if (options.inherited) class_name = class_name + "--inherited";

                        kQuery(container).addClass(class_name);
                    }

                    if (kQuery(option.element).attr(\'hidden\'))
                    {
                        kQuery(container).addClass("select2-results--hidden");
                        //option.disabled = true;
                    }

                    return option.text;
                };

                options.templateResult = function(option, container)
                {
                    var $option = kQuery(\'.select2 option[value="\'+option.id+\'"]\');

                    if (kQuery(option.element).data(\'usergroup-type\') == \'fixed\')
                    {
                        var class_name = "select2-results__" + fixed[option.id];

                        if (options.inhertied) class_name = class_name + "--inherited";

                        kQuery(container).addClass(class_name);
                    }

                    if (kQuery(option.element).attr(\'hidden\'))
                    {
                        kQuery(container).addClass("select2-results--hidden");
                        //option.disabled = true;
                    }

                    return option.text;
                };

                return options;
            };

            function initEasyDocPermissions(instance)
            {
                instance.on("select2:unselect", function(e)
                {
                    // Prevents the open event from triggering when unselecting

                    kQuery(this).on("select2:opening.cancelOpen", function (e)
                    {
                        e.preventDefault();

                        kQuery(this).off("select2:opening.cancelOpen");
                    });
                });

                instance.on("select2:unselecting", function(e)
                {
                   if (kQuery(e.params.args.data.element).attr("locked")) {
                       e.preventDefault();
                    }
                });
            }
            </script>';

            self::$_select2_ready = true;
        }

        return $html;
    }

    public function optionlist($config = array())
    {
        $config = new Library\ObjectConfig($config);

        // Add options for fixed usergroups

        if ($config->type == 'usergroups' && $config->attribs->fixed)
        {
            $t = $this->getObject('translator');

            $options = $config->options->toArray();

            foreach (ModelEntityUsergroup::FIXED as $name => $values)
            {
				$option = $this->option([
					'value'   => $values['id'],
					'label'   => $t->translate(ucfirst($name)),
					'attribs' => [
						'data-usergroup-type'      => 'fixed',
						'data-usergroup-exclusive' => (int) $values['exclusive'],
						'data-usergroup-syncable'  => (int) $values['syncable']
					]
				]);

                array_unshift($options, $option);
            }

            $config->options = $options;
        }

        $locked = $config->locked;

        if (in_array($config->type, ['users', 'usergroups']) && strpos($config->action ?: '', 'view') === 0 && !empty($locked))
        {
            foreach ($config->options as $option)
            {
                if (in_array($option->value, $config->selected->toArray()) && in_array($option->value, $locked->toArray()))
				{
					$attribs = $option->attribs;

					$is_fixed    = isset($attribs['data-usergroup-type']) && ($attribs['data-usergroup-type'] == 'fixed');
					$is_syncable = $is_fixed && ($attribs['data-usergroup-syncable'] == 1);

					if (!$is_fixed || $is_syncable) {
						$option->attribs['locked'] = 'locked';
					}
                }
            }
        }

        return parent::optionlist($config);
    }
}
