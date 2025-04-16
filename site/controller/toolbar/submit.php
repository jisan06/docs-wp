<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/fyigoto/easydocs for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ControllerToolbarSubmit extends EasyDoc\ControllerToolbarActionbar
{
    protected function _afterRead(Library\ControllerContext $context)
    {
        $controller = $this->getController();

        if($controller->getView()->show_form) {
            $this->addCommand('save', [
                'allowed' => true,
                'label'   => 'Submit',
                'attribs' => [
                    'class' => ['btn btn-default']
                ]
            ]);
        }
    }
}
