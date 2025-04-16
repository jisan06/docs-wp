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
 * Rss View
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\View
 */
class ViewRss extends ViewTemplate
{
    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param ObjectConfig $config	An optional Object object with configuration options
     * @return  void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array('localizable', 'routable'),
            'data'      => array(
                'update_period'    => 'hourly',
                'update_frequency' => 1
            )
        ));

        parent::_initialize($config);
    }

    /**
     * Get the layout to use
     *
     * @return   string The layout name
     */
    public function getLayout()
    {
        return 'default';
    }

    /**
     * Prepend the xml prolog
     *
     * @param ViewContextTemplate  $context A view context object
     * @return string  The output of the view
     */
    protected function _actionRender(ViewContext $context)
    {
        //Prepend the xml prolog
        $content  = '<?xml version="1.0" encoding="utf-8" ?>';
        $content .=  parent::_actionRender($context);

        return $content;
    }

}