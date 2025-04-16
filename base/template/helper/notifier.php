<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class TemplateHelperNotifier extends TemplateHelperListbox
{
    static $_select2_ready = false;

    public function notifiers($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'name'            => null,
            'attribs'         => [
                'class'            => 'k-select2-resettable',
                '@notifiable:sync' => '() => {
                    data.notifier = $event.detail.value;
                    if (typeof current.onSelect === "function") current.onSelect();
                }',
            ],
            'deselect'        => true,
            'select2_options' => [
                'init_callback' => 'EasyDoc.registerNotifiableEvents'
            ]
        ]);

        $options = [];

        foreach ($config->notifiers as $notifier)
        {
            $data = $notifier->getData();

            $options[] = $this->option(array('label' => $data['name'], 'value' => $data['identifier']));
        }

        $config->options = $options;

        return $this->_prepareSelect2($this->optionlist($config));
    }

    public function actions($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $notifier = $config->notifier;

        $config->append([
            'deselect'        => true,
            'name'            => null,
            'attribs'         => [
                'multiple'         => true,
                'class'            => 'k-select2-resettable',
                '@notifiable:sync' => '() => {
                    data.parameters.actions = $event.detail.value;
                    if (typeof current.setActions === "function") current.setActions($event.detail.value);   
                }'
            ],
            'select2_options' => [
                'init_callback' => 'EasyDoc.registerNotifiableEvents'
            ]
        ]);

        $options = [];

        foreach ($notifier->getData()['actions'] as $action => $data) {
            $options[] = $this->option(array('label' => $data['label'] , 'value' => $action));
        }

        $config->options = $options;

        return $this->optionlist($config);
    }

    public function groups($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'name'       => null,
            'resettable' => true,
            'syncable'   => true,
            'type'       => 'groups',
            'fixed'      => true,
            'attribs'    => [
                'multiple' => true
            ],
            'select2_options' => [
                'options_callback' => 'EasyDoc.setGroupsOptions'
            ]
        ]);

        if ($config->resettable) {
            $config->append(['attribs' => ['class' => 'k-select2-resettable']]);
        }

        if ($config->syncable) {
            $config->append([
                'attribs'         => [
                    '@notifiable:sync' => 'data.parameters.groups = $event.detail.value',
                ],
                'select2_options' => [
                    'init_callback' => 'EasyDoc.registerNotifiableEvents'
                ]
            ]);
        }

        return parent::usergroups($config);
    }

    public function users($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'name'       => null,
            'type'       => 'users',
            'resettable' => true,
            'syncable'   => true,
            'attribs'    => [
                'multiple' => true
            ]
        ]);

        if ($config->resettable) {
            $config->append(['attribs' => ['class' => 'k-select2-resettable']]);
        }

        if ($config->syncable) {
            $config->append([
                'attribs'       => [
                    '@notifiable:sync' => 'data.parameters.users = $event.detail.value',
                ],
                'init_callback' => 'EasyDoc.initNotifiableUsersSelector'
            ]);
        }

        return parent::users($config);
    }

    protected function _prepareSelect2($html)
    {
        if (!self::$_select2_ready)
        {
            $html .= '<script>

                var EasyDoc= EasyDoc|| {};
                
                EasyDoc.proxyEvent = (event, name, data = {})  => {

                    event.currentTarget.dispatchEvent(new CustomEvent(name, {detail: data}));

                };
                            
                EasyDoc.registerNotifiableEvents = instance => {

                    instance.on("select2:select", event => EasyDoc.proxyEvent(event, "notifiable:sync", {value: kQuery(event.currentTarget).val()}));             
                    instance.on("change", event => EasyDoc.proxyEvent(event, "notifiable:sync", {value: kQuery(event.currentTarget).val()}));

                };  
                
                EasyDoc.initNotifiableUsersSelector = instance => {

                    EasyDoc.registerNotifiableEvents(instance);

                    // Reset selected values (used for pre-loading user options

                    instance.val([]);
                    instance.trigger("change");

                };
                

                EasyDoc.setGroupsOptions = options => {

                    let fixed = ' . \EasyDocLabs\WP::wp_json_encode(array_flip(NotifierAbstract::FIXED_GROUPS)) . ';

                    options.templateSelection = (option, container) => {

                        let $option = kQuery(\'.select2 option[value="\'+option.id+\'"]\');

                        if (fixed[option.id] !== undefined)
                        {
                            let class_name = "select2-option__" + fixed[option.id];

                            kQuery(container).addClass(class_name);
                        }

                        return option.text;
                    };


                    options.templateResult = (option, container) => {

                        let $option = kQuery(\'.select2 option[value="\'+option.id+\'"]\');
    
                        if (kQuery(option.element).data(\'group-type\') == \'fixed\')
                        {
                            let class_name = "select2-results__" + fixed[option.id];
        
                            kQuery(container).addClass(class_name);
                        }
       
                        return option.text;

                    };
    
                    return options;                  
                }

            </script>';
        }

        self::$_select2_ready = true;

        return $html;
    }

    public function optionlist($config = array())
    {
        $config = new Library\ObjectConfig($config);

        // Add options for fixed usergroups

        if ($config->type == 'groups' && $config->fixed)
        {
            $t = $this->getObject('translator');

            $options = $config->options->toArray();

            foreach (NotifierAbstract::FIXED_GROUPS as $name => $value)
            {
				$option = $this->option([
					'value'   => (string) $value,
					'label'   => $t->translate(ucfirst(str_replace('_', ' ', $name))),
                    'attribs' => [
						'data-group-type' => 'fixed'
					]
				]);

                array_unshift($options, $option);
            }

            $config->options = $options;
        }

        return parent::optionlist($config);
    }
}