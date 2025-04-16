<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Listbox Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperListbox extends Library\TemplateHelperListbox
{
    /**
     * Provides a users select box.
     *
     * You have to create a user controller to use autocomplete.
     * Autocomplete is highly recommended since a site with 10k users can make you run into memory limit issues.
     *
     * @param  array|\KObjectConfig $config An optional configuration array.
     * @return string The autocomplete users select box.
     */
    public function users($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'autocomplete' => true,
            'model'        => 'users',
            'name'         => 'user',
            'value'        => 'id',
            'label'        => 'name',
            'text'         => 'name',
            'sort'         => 'name',
            'validate'     => false
        ]);

        return $this->render($config);
    }

    /**
     * Generates an HTML access listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function access($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'autocomplete' => false,
            'name'      => 'access',
        ])->append([
            'selected'  => $config->{$config->name}
        ]);

        $options = [];

        foreach (array_reverse(WP::get_editable_roles()) as $role_name => $role_info) {
            $label     = WP::translate_user_role($role_info['name'] );
            $options[] = $this->option(['label' => $label, 'value' => $role_name]);
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }
}
