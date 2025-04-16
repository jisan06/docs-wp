<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * External block script
 *
 * Returns a file path that contains the block scripts
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockScriptExternal extends BlockScriptAbstract
{
    /**
     * External script path relative to wp-plugins/ directory
     * @var string
     */
    protected $_path;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPath($config->path);
    }

    public function getBlockConfiguration()
    {
        $result = parent::getBlockConfiguration();

        $dispatcher = $this->getObject('com:base.dispatcher.fragment');

        $request = $dispatcher->getRequest();
        $request->getQuery()->set('endpoint', '~documents');

        $block = $this->getBlock();

        $attributes = $block->getAttributes();

        $result['routes'] = [];

        foreach ($attributes as $name => $config)
        {
            if (isset($config['control']) && $config['control'] == 'autocomplete')
            {
                $plugin = $block->getIdentifier()->getPackage();

                if (isset($config['resource'])) {
                    $view = Library\StringInflector::pluralize($config['resource']);
                } else {
                    $view = $name;
                }

                $value = $config['value'] ?? 'id';
                $title = $config['title'] ?? 'title';

                $fields = sprintf('%s,%s', $value, $title);

                $route = (string) $dispatcher->getRouter()->generate($plugin . ':', [
                    'component' => $plugin, 'view'  => $view , 'format' => 'json',
                    'sort'      => $title, 'fields' => [$view => $fields]]);
            
                $result['routes'][$name] = $route;    
            }
        }

        return $result;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'path' => null
        ]);

        parent::_initialize($config);
    }

    public function getScript()
    {
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

}