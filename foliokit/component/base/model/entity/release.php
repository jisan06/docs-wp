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

/**
 * Release entity
 *
 * @package EasyDocLabs\Component\Base
 */
class ModelEntityRelease extends Library\ModelEntityAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            // Map to Foreign Properties (fetched from Github or get_plugin_data) into standardized local properties
            'property_map' => [
                'Version'      => 'installed_version',
                'tag_name'     => 'version',
                'body'         => 'changelog',
                'zipball_url'  => 'download_url',
                'published_at' => 'publish_date',
                'Name'         => 'slug',
                'Title'        => 'title',
                'Description'  => 'description',
                'Author'       => 'author',
                'AuthorURI'    => 'author_url',
                'PluginURI'    => 'homepage',
                'TextDomain'   => 'text_domain',
                'DomainPath'   => 'domain_path',
                'Network'      => 'multisite',
                'author'       => 'author_profile'
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Extended from KModelEntityAbstract so it maps foreign properties into standardized local properties
     *
     * @param   string  $name       The property name.
     * @param   mixed   $value      The property value.
     * @param   boolean $modified   If TRUE, update the modified information for the property
     *
     * @return  $this
     */
    public function setProperty($name, $value, $modified = true)
    {
        if($this->getConfig()->property_map->has($name)) {
            $name = $this->getConfig()->property_map->get($name);
        }

        return parent::setProperty($name, $value, $modified);
    }
}
