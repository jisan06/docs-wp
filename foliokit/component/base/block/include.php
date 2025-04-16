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
 * Include block
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockInclude extends BlockPage
{
    const REGEXP = '/^(\w+)(?:\/(\w+))?(?:\?(.*?))?$/i';
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'name'        => 'ktml-include',
            'title'       => 'Foliokit Include',
            'description' => 'Use with a URI like this: component/view?query=string (view and query string are optional)',
            'script'      => 'com:base.block.script.include'
        ]);

        parent::_initialize($config);
    }

    protected function _parseUri($uri) {
        $query = [];

        if (preg_match(static::REGEXP, $uri, $matches)) {
            $query['component'] = $matches[1];

            if (!empty($matches[2])) {
                $query['view'] = $matches[2];
            }

            if (!empty($matches[3])) {
                @parse_str($matches[3], $querystring);

                if (!empty($querystring) && is_array($querystring))
                {
                    foreach ($querystring as $key => $value) {
                        $query['key'] = $value;
                    }
                }
            }
        }

        return $query;
    }

    public function isSupported($context)
    {
        return preg_match(static::REGEXP, $context->attributes->uri);
    }

    public function beforeRender($context)
    {
        if ($query = $this->_parseUri($context->attributes->uri)) {
            $context->append(['query' => $query]);
        }

        parent::beforeRender($context);
    }

    public function getAttributes()
    {
        return [
            'uri' => [
                'type' => 'string',
            ],
        ];
    }
}
