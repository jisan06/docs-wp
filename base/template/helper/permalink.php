<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class TemplateHelperPermalink extends Library\TemplateHelperGrid
{
    public function generate($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $entity = $config->entity;

        $router = $this->getObject('com://site/easydoc.dispatcher.router.site', ['request' => $this->getObject('request')]);

        return $router->generate(null, ['component' => 'easydoc', 'endpoint' => '~documents', 'view' => 'download', 'id' => $entity->id]);
    }
}