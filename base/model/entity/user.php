<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityUser extends Library\ModelEntityRow
{
    public function delete()
    {
        $result = parent::delete();

        if ($result)
        {
            $query = $this->getObject('lib:database.query.delete')
                          ->table('easydoc_categories_permissions')
                          ->where('wp_user_id = :user')
                          ->bind(['user' => $this->id]);

            $this->getTable()->getDriver()->delete($query);
        }

        return $result;
    }
}