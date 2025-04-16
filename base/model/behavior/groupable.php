<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorGroupable extends Library\ModelBehaviorAbstract
{
    public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        $mixer->getState()->insert('group', 'int');
    }

    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $query = $context->getQuery();

        $query->join('easydoc_usergroups_users AS rel', 'rel.wp_user_id = tbl.ID', 'INNER')
              ->join('easydoc_usergroups AS usergroups', 'rel.easydoc_usergroup_id = usergroups.easydoc_usergroup_id', 'INNER');

        $state = $context->getState();

        if ($group = $state->group) {
            $query->where('usergroups.easydoc_usergroup_id IN :group')->bind(['group' => (array) $group]);
        }
    }
}