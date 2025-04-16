<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Base Model that wraps a Web Service Resource
 *
 * @author  Israel Canasa <https://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
class ModelResources extends Library\ModelAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('source', 'url', null, true);
    }

    protected function _actionFetch(Library\ModelContext $context)
    {
        $identifier = $this->getIdentifier()->toArray();
        $identifier['path'] = ['model', 'entity'];
        $identifier['name'] = Library\StringInflector::pluralize($identifier['name']);

        $context->append([
            'options' => [
                'identity_key' => 'id'
            ]
        ]);

        if(empty($context->data))
        {
             $data = \EasyDocLabs\WP::wp_remote_retrieve_body(
                 \EasyDocLabs\WP::wp_remote_get($this->getState()->source)
            );

            if($data) {
                $context->options->data = (new Library\ObjectConfigJson())->fromString($data);
            }
        }
        else $context->options->data = $context->data;

        return $this->getObject($identifier, $context->options->toArray());
    }
}