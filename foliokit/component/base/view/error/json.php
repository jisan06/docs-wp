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
 * Exception Json View
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ViewErrorJson extends Library\ViewJson
{
    protected function _actionRender(Library\ViewContext $context)
    {
        if(\Foliokit::isDebug()) {
            $this->message = $this->exception ." with message '".$this->message."' in ".$this->file.":".$this->line;
        }

        $properties = [
            'message' => $this->message,
            'code'    => $this->code
        ];

        if(\Foliokit::isDebug())
        {
            $properties['data'] = [
                'file'	   => $this->file,
                'line'     => $this->line,
                'function' => $this->function,
                'class'	   => $this->class,
                'args'	   => $this->args,
                'info'	   => $this->info
            ];
        }

        $content = json_encode([
            'version'  => '1.0',
            'errors'   => [$properties]
        ]);

        $context->content = $content;

        return parent::_actionRender($context);
    }
}