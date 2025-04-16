<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Nodes Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelEntityNodes extends Library\ModelEntityComposite
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'identity_key' => 'name'
        ]);

        parent::_initialize($config);
    }

    /**
     * Moves all rows in the rowset to the database
     *
     * @return boolean  If successful return TRUE, otherwise FALSE
     */
    public function move()
    {
        $result = false;

        if (count($this))
        {
            $result = true;

            foreach ($this as $row)
            {
                if (!$row->move())
                {
                    // Set current row status message as rowset status message.
                    $this->setStatusMessage($row->getStatusMessage());
                    $result = false;
                }
            }
        }

        return $result;
    }


    /**
     * Copies all rows in the rowset to the database
     *
     * @return boolean  If successful return TRUE, otherwise FALSE
     */
    public function copy()
    {
        $result = false;

        if (count($this))
        {
            $result = true;

            foreach ($this as $row)
            {
                if (!$row->copy())
                {
                    // Set current row status message as rowset status message.
                    $this->setStatusMessage($row->getStatusMessage());
                    $result = false;
                }
            }
        }

        return $result;
    }
}
