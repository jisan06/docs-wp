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
 * Extension Object Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Locator
 */
class ObjectLocatorExtension extends ObjectLocatorAbstract
{
    /**
     * The locator type
     *
     * @var string
     */
    protected static $_type = 'ext';

    /**
     * Get the list of class templates for an identifier
     *
     * @param ObjectIdentifier $identifier The object identifier
     * @return array The class templates for the identifier
     */
    public function getClassTemplates(ObjectIdentifier $identifier)
    {
        $templates = [
            'EasyDocLabs\<Package>\Ext\<Class>',
            'EasyDocLabs\<Package>\Ext\<Package><Path><File>'
        ];

        return $templates;       
    }
}