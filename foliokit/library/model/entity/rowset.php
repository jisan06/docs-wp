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
 * Model Rowset Entity
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Model\Entity
 */
class ModelEntityRowset extends DatabaseRowsetAbstract implements ModelEntityComposable
{
    /**
     * Get the entity key
     *
     * @return string
     */
    public function getIdentityKey()
    {
        return parent::getIdentityColumn();
    }
}