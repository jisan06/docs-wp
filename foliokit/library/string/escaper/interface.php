<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * String Escaper Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\String\Escaper
 */
interface StringEscaperInterface
{
    /**
     * Escapde a string for a specific context
     *
     * @param string $string    The string to escape
     * @param string $context   The context. Default HTML
     * @throws \InvalidArgumentException If the context is not recognised
     * @return string
     */
    public static function escape($string, $context = 'html');
}
