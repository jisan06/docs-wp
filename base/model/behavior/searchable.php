<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorSearchable extends Library\ModelBehaviorSearchable
{
    /**
     * Add search query
     *
     * @todo   Support mysql fulltext search
     *
     * @param  KModelContextInterface $context A model context object
     * @return void
     */
    protected function _buildQuery(Library\ModelContextInterface $context)
    {
        $model = $context->getSubject();

        if ($model instanceof Library\ModelDatabase && !$context->state->isUnique())
        {
            $state = $context->state;

            $search = urldecode($context->state->search ?? ''); // Decodes symbol ('+') to space character
            $context->state->search = $search;

            $mixer = $this->getMixer();   
            
            if ($mixer->getIdentifier()->getName() == 'documents')
            {
                // Join contents table when required

                if ($state->search_contents || strpos($search, 'contents:') !== false) {
                    $context->query->join(array('contents' => 'easydoc_document_contents'), 'contents.easydoc_document_id = tbl.easydoc_document_id');
                } else {
                    $context->_ignore_columns = 'contents';
                }

            }

            $matches = preg_split('#:\b(and|or)\b#i', $search, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            if ($search && (in_array('and', array_map('strtolower', $matches)) || in_array('or', array_map('strtolower', $matches))))
            {
                // Handle search operators
                array_unshift($matches, 'and');
                $matches = array_chunk($matches, 2);

                $i = 0;

                foreach($matches as $match)
                {
                    $context->_combination = strtoupper($match[0]);
                    $context->_prefix      = $i;

                    $context->state->search = trim($match[1]);

                    parent::_buildQuery($context);

                    $i++;
                }

                $context->state->search = $search;  // Reinstate the search state
            }
            else parent::_buildQuery($context);
        }
    }
}
