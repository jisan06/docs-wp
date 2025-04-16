<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class TemplateHelperPaginator extends Library\TemplateHelperPaginator
{
    public function sort_documents($config = [])
    {
        $config = new Library\ObjectConfig($config);
        $config->append([
            'options'   => [],
            'attribs'   => [
                'onchange' => 'window.location = this.value;'
            ]
        ]);

        $translator = $this->getObject('translator');

        $options = array_merge([
            $translator->translate('Title Alphabetical')         => [
                'sort'      => 'title',
                'direction' => 'asc'],
            $translator->translate('Title Reverse Alphabetical') => [
                'sort'      => 'title',
                'direction' => 'desc'],
            $translator->translate('Most Recent First')          => [
                'sort'      => 'created_on',
                'direction' => 'desc'],
            $translator->translate('Oldest First')               => [
                'sort'      => 'created_on',
                'direction' => 'asc'],
            $translator->translate('Most popular first')         => [
                'sort'      => 'hits',
                'direction' => 'desc'],
            $translator->translate('Last modified first')         => [
                'sort'      => 'touched_on',
                'direction' => 'desc']
        ], Library\ObjectConfig::unbox($config->options));

        $html     = '';
        $selected = null;
        $state    = $this->getTemplate()->getParameters();
        $current = [
            'sort'      => $state->sort,
            'direction' => $state->direction,
        ];

        $select = [];
        foreach($options as $text => $value)
        {
            $route = clone $config->url;
            $route->setQuery($value, true);

            if ($selected === null && $value === $current) {
                $selected = $route;
            }

            $select[] = $this->option(['label' => $text, 'value' => $route]);
        }

        $html .= $this->optionlist([
            'options' => $select,
            'name' => '',
            'attribs' => $config->attribs,
            'selected' => $selected
        ]);

        return $html;
    }

    /**
     * Render item pagination
     *
     * @param   array|Library\ObjectConfig   $config An optional array with configuration options
     * @return string Html
     * @see     http://developer.yahoo.com/ypatterns/navigation/pagination/
     */
    public function pagination($config = [])
    {
        $config = new Library\ObjectConfig($config);
        $config->append([
            'attribs' => ['onchange' => 'this.form.submit();']
        ]);

        return parent::pagination($config);
    }
}
