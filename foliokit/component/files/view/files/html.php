<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Files Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ViewFilesHtml extends Base\ViewHtml
{
	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->auto_fetch = false;

        $config->append([
            'decorator'  => 'foliokit',
        ]);

		parent::_initialize($config);
	}

    protected function _fetchData(Library\ViewContextTemplate $context)
	{
        $context->data->debug     = \Foliokit::isDebug();

		parent::_fetchData($context);
    }
}
