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
 * Method Not Allowed Http Exception
 *
 * The request URL does not support the specific request method.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Exception
 */
class HttpExceptionMethodNotAllowed extends HttpExceptionAbstract
{
    protected $code = HttpResponse::METHOD_NOT_ALLOWED;
}