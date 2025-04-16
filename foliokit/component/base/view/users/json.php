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
 * Users JSON view
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ViewUsersJson extends Library\ViewJson
{
    /**
     * Overridden to use id instead of slug for links
     *
     * {@inheritdoc}
     */
    protected function _getEntityRoute(Library\ModelEntityInterface $entity)
    {
        $package = $this->getIdentifier()->package;
        $view    = 'users';

        return $this->getRoute(sprintf('component=com_%s&view=%s&id=%s&format=json', $package, $view, $entity->id));
    }
}