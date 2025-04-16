<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Modal Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class TemplateHelperUploader extends Library\TemplateHelperAbstract implements Library\TemplateHelperParameterizable
{
    /**
     * Array which holds a list of loaded Javascript libraries
     *
     * @type array
     */
    protected static $_loaded = [];

    public function container($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'container' => null,
            'options'   => [
                'multipart_params' => [
                    '_actionAdd' => 'add',
                    'folder'     => ''
                ],
                'check_duplicates' => true,
                'chunking' => true
            ]
        ]);

        // set container
        if ($config->container) {
            $container = $this->getObject('com:files.model.containers')->slug($config->container)->fetch();

            if ($container) {
                $container = $container->toArray();
            }

            $config->options->container = $container;
        }

        $html = $this->uploader($config);

        return $html;
    }

    public function scripts($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'debug' => \Foliokit::isDebug()
        ]);

        $html = '';

        if(!isset(static::$_loaded['uploader']))
        {   
            $script = $this->getObject('com:base.template.helper.script');

            // Load Plupload
            $html .= $script->load(['name' => 'plupload', 'decorator' => $config->decorator]);

            // Load jQuery UI
            $html .= $script->load(['name' => 'jquery-ui', 'decorator' => $config->decorator]);

            $html .= $this->getTemplate()->render('com:files/files/uploader_scripts.html', $config->toArray());

            static::$_loaded['uploader'] = true;
        }

        return $html;
    }

    public function uploader($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'element'   => null,
            'attributes' => [
                'class' => ['k-upload']
            ],
            'options'   => [
                'url'       => null,
                'multi_selection' => false,
                'autostart' =>  true
            ]
        ])->append([
            'selector' => $config->element
        ]);

        $html = $this->scripts($config);

        if (is_object($config->options->url)) {
            $config->options->url = (string) $config->options->url;
        }

        $html .= $this->buildElement('script', [], '
            kQuery(function($){
                $("'.$config->selector.'").uploader('.$config->options.');
            });');

        if ($config->element) {
            $element    = $config->element;
            $attributes = $config->attributes->toArray();

            if ($element[0] === '#') {
                $attributes['id'] = substr($element, 1);
            } else {
                $attributes['class'][] = substr($element, 1);
            }

            $html .= $this->buildElement('div', $attributes);
        }

        return $html;
    }
}