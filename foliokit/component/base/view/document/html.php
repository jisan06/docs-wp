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
 * Page Html View
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ViewDocumentHtml extends ViewHtml
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'layout'            => 'wordpress',
            'template_filters'	=> ['style', 'link', 'meta', 'script', 'title', 'message', 'wpml', 'help'],
        ])->append([
            'decorator' => $config->layout
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $context->data->language  = $this->getObject('translator')->getLanguage();
        $context->data->direction = \EasyDocLabs\WP::is_rtl() ? 'rtl' : 'ltr';

        parent::_fetchData($context);
    }
}
