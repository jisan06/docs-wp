<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

/**
 * Gallery Template Helper
 *
 * @package Koowa\Component\Files
 */
class TemplateHelperGallery extends Library\TemplateHelperAbstract
{
    public function load($config = [])
    {
        $config = new Library\ObjectConfig($config);
        $config->append([
            'params' => null
        ]);

        static $imported = false;

        $html = '';

        if (!$imported)
        {
            $html = $this->getObject('com:easydoc.view.documents.default.html')
                ->getTemplate()
                ->render('com://site/easydoc/documents/gallery_scripts.html', Library\ObjectConfig::unbox($config->params));

            $imported = true;
        }

        return $html;
    }
}