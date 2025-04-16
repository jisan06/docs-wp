<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Listbox Template Helper
 *
 * @author  Tom Janssens <http://github.com/tomjanssens>
 * @package Koowa\Component\Tags
 */
class TemplateHelperListbox extends Library\TemplateHelperListbox
{
    /**
     * Tags listbox helper
     *
     * @param array $config
     * @return string
     */
    public function tags($config = [])
    {
        $config = new Library\ObjectConfig($config);

        // '0' is false in PHP but true in JavaScript so we need to cast to boolean here
        if (isset($config->autocreate)) {
            $config->autocreate = (boolean) $config->autocreate;
        }

        $config->append([
            'autocomplete' => false,
            'autocreate'   => true,
            'component'    => $this->getIdentifier()->package,
            'entity'   => null,
            'filter'   => [],
            'name'     => 'tags',
            'value'    => 'title',
            'prompt'   => false,
            'deselect' => false,
            'attribs'  => [
                'multiple' => true
            ],
        ])->append([ // For autocomplete helper
            'model'  => $this->getObject('com:tags.model.tags', ['table' => $config->component.'_tags']),
            'options' => [
                'tokenSeparators' => ($config->autocreate) ? [',', ' '] : [],
                'tags' => $config->autocreate,
            ],
        ])->append([ // For listbox helper
            'select2'      => true,
            'select2_options' => [
                'options' => $config->options
            ]
        ]);

        $entity = $config->entity;

        //Set the selected tags
        if ($entity instanceof Library\ModelEntityInterface && $entity->isTaggable() && !$entity->isNew())
        {
            $config->append([
                'selected' => $entity->getTags()
            ]);
        }

        //Set the autocompplete url
        if ($config->autocomplete)
        {
            $parts = [
                'component' => $config->component,
                'view'      => 'tags',
                'format'    => 'json'
            ];

            if ($config->filter) {
                $parts = array_merge($parts, Library\ObjectConfig::unbox($config->filter));
            }

            $config->url = $this->getTemplate()->route($parts, false, false);
        }

        //Do not allow to override label and sort
        $config->label = 'title';
        $config->sort  = 'title';

        $html = parent::_render($config);

        $html .= "<script>
        kQuery(function($) {
            var element = $('select[name=\"{$config->name}[]\"]');
            
            if (element.length) {
                var form = $(element[0].form);
                
                form.submit(function() {
                    if (!element.val()) {
                        $('<input />')
                            .attr('name', '{$config->name}')
                            .attr('type', 'hidden')
                            .val('')
                            .appendTo(form);
                    }
                });
            }
        });</script>";

        return $html;
    }
}
