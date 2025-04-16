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
 * Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Helper
 */
class TemplateHelperFactory extends ObjectAbstract implements ObjectSingleton
{
    /**
     * Get a template helper
     *
     * @param    mixed $helper ObjectIdentifierInterface
     * @param    array $config An optional associative array of configuration settings
     * @throws  \UnexpectedValueException
     * @return  TemplateHelperInterface
     */
    public function createHelper($helper, array $config = array())
    {
        if(is_string($helper) && strpos($helper, '.') === false )
        {
            $identifier = $this->getIdentifier()->toArray();
            $identifier['name'] = $helper;
        }
        else $identifier = $helper;

        //Create the template helper
        $helper = $this->getObject($identifier, $config);

        //Check the helper interface
        if (!($helper instanceof TemplateHelperInterface))
        {
            throw new \UnexpectedValueException(
                "Template helper $identifier does not implement TemplateHelperInterface"
            );
        }

        return $helper;
    }
}
