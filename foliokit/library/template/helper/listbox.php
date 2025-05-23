<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Listbox Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Helper
 */
class TemplateHelperListbox extends TemplateHelperSelect
{
    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using the helper identifier. The model name
     * will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead. If no 'text'  option is specified the
     * 'value' option will be used instead.
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     * @see __call()
     */
    public function render($config = array())
    {
        $config = new ObjectConfig($config);
        $config->append(array(
            'autocomplete' => false,
            'model'        => StringInflector::pluralize($this->getIdentifier()->package)
        ));

        if(!$config->model instanceof ModelInterface)
        {
            if(is_string($config->model) && strpos($config->model, '.') === false) {
                $identifier = 'com:'.$this->getIdentifier()->package.'.model.'.StringInflector::pluralize($config->model);
            } else {
                $identifier = $config->model;
            }

            $model  = $this->getObject($identifier);

            if(!$model instanceof ModelInterface)
            {
                throw new \UnexpectedValueException(
                    'Model: '.get_class($model).' does not implement ModelInterface'
                );
            }

            //Set the model
            $config->model = $model;
        }

        if($config->autocomplete) {
            $result = $this->__autocomplete($config);
        } else {
            $result = $this->__listbox($config);
        }

        return $result;
    }

    /**
     * Adds the option to enhance the select box using Select2
     *
     * @param array|ObjectConfig $config
     * @return string
     */
    public function optionlist($config = array())
    {
        $translator = $this->getObject('translator');

        $config = new ObjectConfigJson($config);
        $config->append(array(
            'prompt'    => '- '.$translator->translate('Select').' -',
            'deselect'  => false,
            'options'   => array(),
            'select2'   => true,
            'attribs'   => array(),
        ));

        if ($config->deselect && !$config->attribs->multiple)
        {
            $deselect = $this->option(array('value' => '', 'label' => $config->prompt));
            $options  = $config->options->toArray();
            array_unshift($options, $deselect);
            $config->options = $options;
        }

        if ($config->attribs->multiple && $config->name && substr($config->name, -2) !== '[]') {
            $config->name .= '[]';
        }

        $html = '';

        if ($config->select2)
        {
            if (!$config->name) {
                $config->attribs->append(array(
                    'id' => 'select2-element-'.mt_rand(1000, 100000)
                ));
            }
            
            if ($config->deselect) {
                $config->attribs->append(array(
                    'data-placeholder' => $config->prompt
                ));
            }

            $config->append(array(
                'select2_options' => array(
                    'element' => $config->attribs->id ? '#'.$config->attribs->id : 'select[name=\"'.$config->name.'\"]',
                    'options' => array(
                        'allowClear'  => $config->deselect
                    )

                )
            ));

            $html .= $this->createHelper('behavior')->select2($config->select2_options);
        }

        $html .= parent::optionlist($config);

        return $html;
    }

    /**
     * Generates an HTML enabled listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function enabled( $config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $translator = $this->getObject('translator');
        $options    = array();

        $options[] = $this->option(array('label' => $translator->translate( 'Enabled' ) , 'value' => 1 ));
        $options[] = $this->option(array('label' => $translator->translate( 'Disabled' ), 'value' => 0 ));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML published listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function published($config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $translator = $this->getObject('translator');
        $options    = array();

        $options[] = $this->option(array('label' => $translator->translate('Published'), 'value' => 1 ));
        $options[] = $this->option(array('label' => $translator->translate('Unpublished') , 'value' => 0 ));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML timezones listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function timezones($config = array())
    {
        $config = new ObjectConfig($config);
        $config->append(array(
            'name'      => 'timezone',
            'attribs'   => array(),
            'deselect'  => true,
            'prompt'    => '- '.$this->getObject('translator')->translate('Select Time Zone').' -',
        ));

        if ($config->deselect) {
            $options[] = $this->option(array('label' => $config->prompt, 'value' => ''));
        }

        foreach (\DateTimeZone::listIdentifiers() as $identifier)
        {
            if (strpos($identifier, '/'))
            {
                list($group, $locale) = explode('/', $identifier, 2);
                $groups[$group][] = str_replace('_', ' ', $locale);
            }
        }

        $options[] = $this->option(array('label' => 'Coordinated Universal Time', 'value' => 'UTC'));
        foreach ($groups as $group => $locales)
        {
            foreach ($locales as $locale) {
                $options[$group][] = $this->option(array('label' => $locale, 'value' => str_replace(' ', '_', $group.'/'.$locale)));
            }
        }

        $list = $this->optionlist(array(
            'options'   => $options,
            'name'      => $config->name,
            'selected'  => $config->selected,
            'attribs'   => $config->attribs
        ));

        return $list;
    }

    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in
     * cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using
     * the helper identifier. The model name will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead.
     * If no 'label' option is specified the 'value' option will be used instead.
     *
     * @param   array|ObjectConfig  $config An optional array with configuration options
     * @return  string  Html
     * @see __call()
     */
    private function __listbox($config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'name'       => '',
            'attribs'    => array(),
            'deselect'   => true
        ))->append(array(
            'value'      => $config->name,
            'selected'   => $config->{$config->name},
        ))->append(array(
            'label'      => $config->value,
        ))->append(array(
            'filter'     => array('sort' => $config->label),
        ));

        //Create the model
        $model = $config->model;

        $options = array();
        $state   = ObjectConfig::unbox($config->filter);
        $count   = $model->setState($state)->count();
        $offset  = 0;
        $limit   = 100;

        /*
         * We fetch data gradually here and convert it directly into options
         * This only loads 100 entities into memory at once so that
         * we do not run into memory limit issues
         */
        while ($offset < $count)
        {
            $entities = $model->setState($state)->limit($limit)->offset($offset)->fetch();

            foreach ($entities as $entity) {
                $options[] = $this->option(array('label' => $entity->{$config->label}, 'value' => $entity->{$config->value}));
            }

            $offset += $limit;
        }

        //Compose the selected array
        if($config->selected instanceof ModelEntityInterface)
        {
            $selected = array();
            foreach($config->selected as $entity) {
                $selected[] = $entity->{$config->value};
            }

            $config->selected = $selected;
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Renders a listbox with autocomplete behavior
     *
     * @param  array|ObjectConfig    $config
     * @return string   The html output
     */
    private function __autocomplete($config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'name'     => '',
            'attribs'  => array(
                'id' => 'select2-element-'.mt_rand(1000, 100000)
            ),
            'validate' => true,
            'prompt'   => '- '.$this->getObject('translator')->translate('Select').' -',
            'deselect' => true,
        ))->append(array(
            'element'    => '#'.$config->attribs->id,
            'options'    => array('multiple' => (bool) $config->attribs->multiple),
            'value'      => $config->name,
            'selected'   => $config->{$config->name},
        ))->append(array(
            'label'      => $config->value,
        ))->append(array(
            'text'       => $config->label,
            'filter'     => array('sort' => $config->label),
        ));

        if (!$config->ajax_url)
        {
            $identifier = $config->model->getIdentifier();
            $parts      = array(
                'component' => $identifier->package,
                'view'      => $identifier->name,
                'format'    => 'json'
            );

            if ($config->filter) {
                $parts = array_merge($parts, ObjectConfig::unbox($config->filter));
            }

            $config->ajax_url = $this->getObject('lib:dispatcher.router.route')->setQuery($parts);
        }

        $html = '';
        $html .= $this->createHelper('behavior')->autocomplete($config);

        $config->attribs->name = $config->name;

        $options = array();

        if ((is_scalar($config->selected) && $config->selected) || (is_countable($config->selected) && count($config->selected)))
        {
            $selected = $config->selected;

            if(!$selected instanceof ModelEntityInterface)
            {
                $selected = $config->model
                    ->setState(ObjectConfig::unbox($config->filter))
                    ->setState(array($config->value => ObjectConfig::unbox($selected)))
                    ->fetch();
            }

            foreach($selected as $entity)
            {
                $options[]  = $this->option(array(
                    'value' => $entity->{$config->value},
                    'label' => $entity->{$config->label},
                    'attribs' => array('selected' => true)
                ));
            }
        }

        $config->options  = $options;
        $config->deselect = false;
        $config->select2  = false;

         $html .= $this->optionlist($config);

        return $html;
    }

    /**
     * Search the mixin method map and call the method or trigger an error
     *
     * This function check to see if the method exists in the mixing map if not it will call the 'listbox' function.
     * The method name will become the 'name' in the config array.
     *
     * This can be used to auto-magically create select filters based on the function name.
     *
     * @param  string   $method The function name
     * @param  array    $arguments The function arguments
     * @throws \BadMethodCallException   If method could not be found
     * @return mixed The result of the function
     */
    public function __call($method, $arguments)
    {
        if(!in_array($method, $this->getMethods()))
        {
            $config = $arguments[0];
            if(!isset($config['name'])) {
                $config['name']  = StringInflector::singularize(strtolower($method));
            }

            return $this->render($config);
        }

        return parent::__call($method, $arguments);
    }
}
