<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class Dispatcher extends Base\Dispatcher
{
    // FIXME: this is here because forwarded dispatchers still render results
    protected function _actionSend(Library\DispatcherContext $context)
    {
        if (!$context->getRequest()->isGet() || $context->getResponse()->getContentType() !== 'text/html') {
            return parent::_actionSend($context);
        }
    }
}
