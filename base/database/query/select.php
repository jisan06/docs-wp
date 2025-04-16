<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseQuerySelect extends Library\DatabaseQuerySelect
{
    public $exists;

    protected $_query;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_query = $config->query;
    }

    public function exists(bool $state = true)
    {
        $this->exists = $state;
        return $this;
    }

    public function toString()
    {
        if (is_null($this->_query))
        {
            $query = parent::toString();

            if ($this->exists) {
                $query = sprintf('SELECT EXISTS(%s)', $query);
            }
        }
        else $query = $this->_query;

        return $query;
    }
}
