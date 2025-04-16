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
 * Bead Request Http Exception
 *
 * The request itself or the data supplied along with the request is invalid and could not be processed by the server.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Exception
 */
class HttpExceptionBadRequest extends HttpExceptionAbstract
{
    protected $code = HttpResponse::BAD_REQUEST;
}