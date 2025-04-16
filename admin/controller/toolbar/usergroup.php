<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ControllerToolbarUsergroup extends ControllerToolbarActionbar
{
    protected function _afterRead(Library\ControllerContext $context)
    {
        parent::_afterRead($context);

        $referrer = $this->getController()->getReferrer($context);

        $this->removeCommand('cancel');

        $this->addCommand('discard', [
            'data' => [
                'referrer' => $referrer
            ]
        ]);
    }
}
