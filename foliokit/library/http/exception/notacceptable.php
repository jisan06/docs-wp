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
 * Not Acceptable Http Exception
 *
 * The resource identified by the request is only capable of generating response entities which have content
 * characteristics not acceptable according to the accept headers sent in the request.
 *
 * Unless it was a HEAD request, the response SHOULD include an entity containing a list of available entity
 * characteristics and location(s) from which the user or user agent can choose the one most appropriate.
 *
 * The entity format is specified by the media type given in the Content-Type header field. Depending upon the
 * format and the capabilities of the user agent, selection of the most appropriate choice MAY be performed
 * automatically. However, this specification does not define any standard for such automatic selection.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Exception
 */
class HttpExceptionNotAcceptable extends HttpExceptionAbstract
{
    protected $code = HttpResponse::NOT_ACCEPTABLE;
}