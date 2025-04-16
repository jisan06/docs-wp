<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTablePermissions extends Library\DatabaseTableAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $clear_permissions = function(Library\DatabaseContextInterface  $context) {
            $this->getObject('easydoc.users')->clearPermissions();
        };

        $this->addCommandCallback('after.insert', $clear_permissions);
        $this->addCommandCallback('after.update', $clear_permissions);
        $this->addCommandCallback('after.delete', $clear_permissions);
    }
}