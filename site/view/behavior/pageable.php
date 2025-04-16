<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewBehaviorPageable extends Library\ViewBehaviorAbstract
{
    protected function _beforeRender(Library\ViewContext $context)
    {
        if ($this->getMixer() instanceof Library\ViewTemplate)
        {
            $this->getMixer()->getTemplate()->registerFunction('isRecent', [$this, 'isRecent']);
            $this->getMixer()->getTemplate()->registerFunction('prepareText', [$this, 'prepareText']);
        }

        if ($this->getMixer()->getName() !== 'document') {
            $this->setOption('show_document_title', true);
        }

        $context->data->config = $this->getObject('com://admin/easydoc.model.configs')->fetch();
    }

    /**
     * Runs a text through content plugins
     *
     * @param $text
     *
     * @return string
     */
    public function prepareText($text)
    {
        $result = $text;

        // Make sure our script filter does not screw up email cloaking
        if (strpos($result ?: '', '<script') !== false) {
            $result = str_replace('<script', '<script data-inline', $result);
        }

        return $result;
    }

    /**
     * Returns true if the document should have a badge marking it as new
     *
     * @param $document Library\ModelEntityInterface      Document row
     *
     * @return bool
     */
    public function isRecent($document)
    {
        $result = false;

        $days_for_new = $this->getOption('days_for_new', 7);

        if (!empty($days_for_new))
        {
            $post = strtotime($document->created_on);
            $new = time() - ($days_for_new*24*3600);
            if ($post >= $new) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Adds some information to the document row like download links and thumbnails
     *
     * @param $document Library\ModelEntityInterface      Document row
     */
    public function prepareDocument(&$document)
    {
        $document->document_link = $this->getMixer()->getRoute($document, ['layout' => 'default']);
        $document->file_link = $this->getMixer()->getRoute($document, ['layout' => 'file']);

        if ($this->getOption('force_download', false)) {
            $document->download_link = $this->getMixer()->getRoute($document, ['layout' => 'file', 'force-download' => 1]);
        } else {
            $document->download_link = $document->file_link;
        }

        $link_to = $this->getOption('document_title_link', 'download');

        if ($link_to === 'preview') {
            $document->title_link = $document->document_link;
        } else {
            $document->title_link = $document->download_link;
        }

        if ($document->image) {
            $document->image_download_path = $document->image_path;
        }

        if ($document->isImage()) {
            $document->image_download_path = $document->file_link;
        }
    }
}
