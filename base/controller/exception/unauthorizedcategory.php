<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Unauthorized Category Controller Exception
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Library\Controller\Exception
 */
class ControllerExceptionUnauthorizedCategory extends Library\HttpExceptionUnauthorized implements Library\ControllerException
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        if (!$message) {
            $message = 'You have not sufficient rights to access this resource';
        }

        parent::__construct($message, $code, $previous);
    }
}