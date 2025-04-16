<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ViewHtml extends Base\ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors'          => ['pageable', 'navigatable'],
            'template_filters'   => [
                'com://admin/easydoc.template.filter.asset'
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Set page title, add metadata
     * 
     * @deprecated Can't find a way to set page-level meta in WP yet.
     */
    // protected function _preparePage()
    // {
    //     $document = JFactory::getDocument();
    //     $params   = $this->getParameters();

    //     // Set robots
    //     if ($this->getObject('request')->query->print) {
    //         $params->robots = 'noindex, nofollow';
    //     }

    //     if ($params->robots) {
    //         $document->setMetadata('robots', $params->robots);
    //     }

    //     // Set keywords
    //     if ($params->{'menu-meta_keywords'}) {
    //         $document->setMetadata('keywords', $params->{'menu-meta_keywords'});
    //     }

    //     // Set description
    //     if ($params->{'menu-meta_description'}) {
    //         $document->setDescription($params->{'menu-meta_description'});
    //     }

    //     // Set page title
    //     $this->_setPageTitle();
    // }

    /*
     * Sets the page title
     * 
     * @deprecated Can't find a way to set page-level meta in WP yet.
     */
    // protected function _setPageTitle()
    // {
    //     $app      = JFactory::getApplication();
    //     $document = JFactory::getDocument();
    //     $menu     = $this->getActiveMenu();
    //     $params   = $this->getParameters();

    //     // Because the application sets a default page title,
    //     // we need to get it from the menu item itself

    //     $title = $params->get('page_title', $params->get('page_heading', $menu->title));

    //     $params->def('page_heading', $params->get('page_heading', $menu->title));

    //     if (empty($title)) {
    //         $title = $app->getCfg('sitename');
    //     }
    //     elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
    //         $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
    //     }
    //     elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
    //         $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
    //     }

    //     $document->setTitle($title);
    // }
}
