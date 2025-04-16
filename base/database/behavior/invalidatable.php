<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Creates a cache namespace per table and automatically invalidates it after every non-safe operation
 */
class DatabaseBehaviorInvalidatable extends Library\DatabaseBehaviorAbstract
{
    protected $_namespace;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if (!$config->namespace) {
            $identifier = $this->getMixer()->getIdentifier();
            $name       = Library\StringInflector::pluralize($identifier->name);
            $config->namespace = $name;
        }

        $this->_namespace = $config->namespace;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'namespace' => null
        ));

        parent::_initialize($config);
    }

    public function cache($key, $data, $expiration = 604800)
    {
        $transient_name = $this->_getTransientName($key);

        $this->_addToIndex($transient_name);

        return WP::set_transient($transient_name, $data, $expiration);
    }

    public function getFromCache($key)
    {
        $result = false;

        if ($this->_onIndex($key)) {
            $result = WP::get_transient($this->_getTransientName($key));
        }

        return $result;
    }

    protected function _onIndex($key)
    {
        $result = false;

        if ($index = $this->_getIndex()) {
            $result = in_array($this->_getTransientName($key), $index);
        }

        return $result;
    }

    public function clearCache()
    {
        foreach ($this->_getIndex() as $key) {
            WP::delete_transient($key);
        }

        $this->_deleteIndex();
    }

    protected function _getIndex()
    {
        return WP::get_option($this->_getIndexName() , []);
    }

    protected function _deleteIndex() {
        return WP::delete_option($this->_getIndexName());
    }

    protected function _addToIndex($key)
    {
        $keys = array_unique(array_merge($this->_getIndex(), [$key]));
        sort($keys);

        return WP::update_option($this->_getIndexName(), $keys);
    }

    protected function _getIndexName()
    {
        return 'easydoc_cache_index_'.$this->_getNamespace();
    }

    protected function _getTransientName($key)
    {
        return 'easydoc_cache_data_'.$this->_getNamespace().'_'.$key;
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $this->clearCache();

        // Clear category cache on document delete to fix counts
        if ($this->_getNamespace() === 'documents') {
            $this->getObject('com:easydoc.database.table.categories')->clearCache();
        }
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        $this->clearCache();

        // Clear category cache on document delete to fix counts
        if ($this->_getNamespace() === 'documents') {
            $this->getObject('com:easydoc.database.table.categories')->clearCache();
        }
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $this->clearCache();

        // Clear category cache on document delete to fix counts
        if ($this->_getNamespace() === 'documents') {
            $this->getObject('com:easydoc.database.table.categories')->clearCache();
        }
    }

    /**
     * @return mixed
     */
    protected function _getNamespace()
    {
        return $this->_namespace;
    }
}