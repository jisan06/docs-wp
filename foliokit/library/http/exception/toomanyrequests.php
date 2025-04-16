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
 * Too Many Requests Http Exception
 *
 * The user has sent too many requests in a given amount of time.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Exception
 */
class HttpExceptionTooManyRequests extends HttpExceptionAbstract
{
    protected $code = HttpResponse::TOO_MANY_REQUESTS;
}
