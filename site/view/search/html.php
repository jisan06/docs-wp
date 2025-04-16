<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewSearchHtml extends ViewHtml
{
    public function isCollection()
    {
        return true;
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $this->setOption('show_documents_header', false);

		$context->data->show_results  = true;
		$context->data->documents     = $context->data->search;
		$context->data->filter        = $this->getModel()->getState();
		$context->data->query_options = $this->getQueryOptions();

        foreach ($context->data->documents as $document) {
            $this->prepareDocument($document);
        }
    }
}
