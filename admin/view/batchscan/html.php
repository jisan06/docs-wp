<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ViewBatchscanHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$config->append([
			'auto_fetch' => false,
			'decorator'  => 'foliokit'
		]);

        parent::_initialize($config);
    }
}
