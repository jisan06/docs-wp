<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableUsers extends Library\DatabaseTableAbstract
{
    public function clearPermissions($users = [])
    {
        $driver = $this->getDriver();

        $driver->execute('START TRANSACTION;');

		if ($users)
		{
			$driver->execute(sprintf('DELETE FROM #__easydoc_categories_permissions WHERE wp_user_id IN (%s);', implode(',', $users)));
			$driver->execute(sprintf('UPDATE #__easydoc_users SET permissions_map = NULL WHERE wp_user_id IN (%s);', implode(',', $users)));
		}
		else
		{
			$driver->execute('TRUNCATE TABLE #__easydoc_categories_permissions;');
			$driver->execute('UPDATE #__easydoc_users SET permissions_map = NULL;');
		}

        $driver->execute('COMMIT;');

        $this->getObject('user')->resetData(); // Force current user to reset it's aggregate data

        return $this;
    }
}
