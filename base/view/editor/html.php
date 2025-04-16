<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

 namespace EasyDocLabs\EasyDoc;

 use EasyDocLabs\Component\Base;
 use EasyDocLabs\Library;

class ViewEditorHtml extends Base\ViewHtml
{
    protected $_field = null;

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $context->data->publisher = sprintf('easy-docs-ait/editor/%s', $this->getField() ?? 'description');
    }

    public function setField($value)
    {
        $this->_field = $value;

        return $this;
    }

    public function getField()
    {
        return $this->_field;
    }
}