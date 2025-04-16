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
 * Object Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Locator
 */
class ObjectLocatorLibrary extends ObjectLocatorAbstract
{
    /**
     * The locator type
     *
     * @var string
     */
    protected static $_type = 'lib';

    /**
     * Get the list of class templates for an identifier
     *
     * @param ObjectIdentifier $identifier The object identifier
     * @return array The class templates for the identifier
     */
    public function getClassTemplates(ObjectIdentifier $identifier)
    {
        $templates = array(
            __NAMESPACE__.'\<Package><Class>',
            __NAMESPACE__.'\<Package><Path>Default',
        );

        return $templates;
    }
}
