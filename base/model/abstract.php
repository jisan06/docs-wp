<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

abstract class ModelAbstract extends Library\ModelDatabase
{
    protected function _actionFetch(Library\ModelContext $context)
    {
        $table = $this->getTable();

        //Entity options
        $options = array(
            'identity_column' => $context->getIdentityKey()
        );

        //Select the rows
        if (!$context->state->isEmpty()){
            $data = $table->select($context->query, $context->mode ?? Library\Database::FETCH_ROWSET, $options);
        } else {
            $data = $table->createRowset($options);
        }

        return $data;
    }
}
