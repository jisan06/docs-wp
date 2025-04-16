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
 * The request is out of bounds—that, none of the range values overlap the extent of the resource.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Exception
 * @see http://tools.ietf.org/html/rfc2616#section-10.4.17
 */
class HttpExceptionRangeNotSatisfied extends HttpExceptionAbstract
{
    protected $code = HttpResponse::REQUESTED_RANGE_NOT_SATISFIED;
}