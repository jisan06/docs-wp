<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewDocumentsHtml extends ViewHtml
{
    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

		$options = $this->getOptions();

		$options->show_document_icon = $options->show_icon; // Map icons settings

        if ($context->data->documents)
        {
            foreach ($context->data->documents as $document) {
                $this->prepareDocument($document);
            }
        }
    }
}
