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
 * Model State Parser Url
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelStateParserUrl extends Library\ObjectAbstract implements Library\ObjectMultiton
{
    public function parse($value)
    {
        $value = rawurldecode($value);

        $result = new \stdClass();

        $parts = explode('://', $value);

        $result->container = null;

        if (count($parts) > 1)
        {
            $result->scheme = $parts[0];

            if ($result->scheme == 'file' && strpos($parts[1], '/') !== 0) {
                $result->container = substr($parts[1],0, strpos($parts[1], '/'));
            }

            $path = $parts[1];

            if ($container = $result->container) {
                $path = str_replace($container, '', $path);
            }

            $result->path = $path;
        }
        else
        {
            $result->scheme = null;
            $result->path   = $value;
        }

        return $result;
    }
}