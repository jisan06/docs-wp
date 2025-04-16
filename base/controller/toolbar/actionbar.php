<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerToolbarActionbar extends Base\ControllerToolbarActionbar
{
	protected function _commandDiscard(Library\ControllerToolbarCommand $command)
    {
		$command->label = 'Cancel';
		$command->href  = 'javascript:;';
		$command->icon  = 'k-icon-action-undo';
    }
}
