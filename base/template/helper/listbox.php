<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class TemplateHelperListbox extends Base\TemplateHelperListbox
{
    public function usergroups($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'model' => 'usergroups',
            'name'  => 'usergroups',
            'label' => 'name',
            'value' => 'id'
        ]);

        return $this->render($config);
    }

    public function users($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append(['model' => 'com:base.model.users']);

        $parts = [
            'component' => 'easydoc',
            'view'      => 'users',
            'format'    => 'json'
        ];

        if ($config->filter) {
            $parts = array_merge($parts, ObjectConfig::unbox($config->filter));
        }

        $config->ajax_url = $this->getObject('lib:dispatcher.router.route')->setQuery($parts);

        return parent::users($config);
    }

    public function tags($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'identifier' => 'com://admin/easydoc.model.tags',
            'component'  => 'easydoc'
        ]);
        return $this->createHelper('com:tags.template.helper.listbox')->tags($config);
    }

    public function registration_date($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'name' => 'filter_range',
            'select2' => true,
            'options' => [],
            'deselect' => true
        ]);

        $translator = $this->getObject('translator');
        $options    = [];
        $values     = [
            ''                  => $translator->translate('Select'),
            'today'             => $translator->translate('Today'),
            'last-week'         => $translator->translate('In the last week'),
            'last-month'        => $translator->translate('In the last month'),
            'last-three-months' => $translator->translate('In the last 3 months'),
            'last-six-months'   => $translator->translate('In the last 6 months'),
            'last-year'         => $translator->translate('In the last year'),
            'over-a-year'       => $translator->translate('More than a year ago'),
        ];

        foreach ($values as $value => $label) {
            $options[] = $this->option(['label' => $label, 'value' => $value]);
        }
        $config->options->append($options);

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML enabled listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function status($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'name'      => '',
            'deselect'  => true,
            'select2'   => true,
            'selected'  => $config->status
        ]);

        if (empty($config->status) && ($config->enabled === 0 || $config->enabled === '0')) {
            $config->selected = 'unpublished';
        }

        $translator = $this->getObject('translator');
        $options    = [];

        $options[] = $this->option(['label' => $translator->translate('Published') , 'value' => 'published']);
        $options[] = $this->option(['label' => $translator->translate('Unpublished'), 'value' => 'unpublished']);
        $options[] = $this->option(['label' => $translator->translate('Pending'), 'value' => 'pending']);
        $options[] = $this->option(['label' => $translator->translate('Expired'), 'value' => 'expired']);

        //Add the options to the config object
        $config->options = $options;

        $html = $this->optionlist($config);

        return $html;
    }

    public function categories($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'model'    => 'categories',
            'value'    => 'id',
            'label'    => 'title',
            'select2'  => true,
            'filter'   => [
                'sort' => 'custom'
            ]
        ]);

        if ($config->permissions)
        {
            if (!$config->user)  $config->user = $this->getObject('user')->getId();

			unset($config->filter->access); // Prevent current model state from overriding the permission state being set

            $config->append(['filter' => ['permission' => $config->permissions, 'strict' => true, 'user' => $config->user]]);
        }

        return $this->_treelistbox($config);
    }

    protected function _treelistbox($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'name'     => '',
            'attribs'  => [],
            'model'    => Library\StringInflector::pluralize($this->getIdentifier()->package),
            'deselect' => true,
            'prompt'   => '- ' . $this->getObject('translator')->translate('Select') . ' -',
            'unique'   => false, // Overridden since there can be categories in different levels with the same name
        ])->append([
            'select2'  => false,
            'value'    => $config->name,
            'selected' => $config->{$config->name},
        ])->append([
            'label' => $config->value,
        ])->append([
            'filter' => ['sort' => $config->label],
        ])->append([
            'indent' => '- ',
            'ignore' => [],
        ]);

        if (is_string($config->model))
        {
            $model = 'com:%s'.$this->getIdentifier()->package.'.model.'.Library\StringInflector::pluralize($config->model);

            if ($domain = $this->getIdentifier()->getDomain()) {
                $model = sprintf($model, '//' . $domain . '/');
            } else {
                $model = sprintf($model, '');
            }

            $config->model = $model;
        }

        $allow = true; $config->cache = true;

        //Add the options to the config object

        $ignore = Library\ObjectConfig::unbox($config->ignore);

        $filter = function($category, $config) use ($ignore, $allow)
        {
            if (in_array($category->id, $ignore)) {
                return false;
            }

            return true;
        };

        $self = $this;

        $map = function(&$data, $category, $config) use ($self) {
            $data[$category->id] = $self->option([
                'label' => str_repeat($config->indent, $category->level - 1) . $category->{$config->label},
                'value' => $category->{$config->value}
            ]);
        };

        $config->options = $this->fetchCategories($config, $map, $filter);

        if ($config->disable_if_empty && !count($config->options)) {
            $config->required = false;
            $config->attribs->disabled = true;
        }

        $html = '';

        if($config->autocomplete) {
            $html .= $this->render($config);
        } else {
            $html .= $this->optionlist($config);
        }

        return $html;
    }

    /**
     * Returns an array of categories for listbox
     *
     * Fetches categories in batches of 100 to not load every category row into memory at once
     *
     * @param mixed $config
     * @param callable $map Maps categories into an array
     * @param callable $filter Filters categories
     * @param callable $sort Sort categories
     *
     * @return array $options
     */
    public function fetchCategories($config, $map, $filter = null, $sort = null)
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'model' => 'com:easydoc.model.categories',
            'state' => $config->filter
        ]);

        $state  = Library\ObjectConfigJson::unbox($config->state);
        $ignore = Library\ObjectConfigJson::unbox($config->ignore);

        /** @var $model KModelInterface */

        $model = $config->model;

        if (is_string($model) || $model instanceof Library\ObjectIdentifier) {
            $model = $this->getObject($model);
        }

        $model->setState($state);

        $key = $config->identifier.'-'.$config->value.'-'.$config->label.'-'.$config->indent.'-'.$config->document_count;
        $key .= '-'.$this->getObject('user')->getId();

        if (is_array($ignore) && count($ignore)) {
            sort($ignore);
            $key .= md5(implode(',', $ignore));
        }
        $key .= md5(serialize($model->getState()->getValues()));

        $signature = md5($key);

        $table = $model->getTable();

        if (0 && $config->cache !== false && ($cached_data = $table->getFromCache($signature))) {
            $data = $cached_data;
        }
        else
        {
            $count  = $model->setState($state)->count();
            $offset = 0;
            $limit  = 100;
            $data   = [];

            while ($offset < $count)
            {
                $entities = $model->setState($state)->limit($limit)->offset($offset)->fetch();

                foreach ($entities as $entity)
                {
                    if (is_callable($filter) && !call_user_func_array($filter, [$entity, $config, $entities])) {
                        continue;
                    }

                    call_user_func_array($map, [&$data, $entity, $config, $entities]);
                }

                $offset += $limit;
            }

            if ($sort) {
                call_user_func_array($sort, [&$data, $config]);
            }

            $table->cache($signature, $data);
        }

        return $data;
    }
}
