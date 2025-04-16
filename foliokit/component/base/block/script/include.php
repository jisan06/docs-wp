<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

/**
 * Include block script
 *
 * Used in BlockInclude
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockScriptInclude extends BlockScriptInline
{
    public function getEditScript()
    {
        return "function(props) {
            return wp.element.createElement('div', {className: ['wp-block-shortcode']}, [
                wp.element.createElement('label', {}, '{$this->getBlock()->getTitle()}'),
                wp.element.createElement( wp.editor.PlainText, {
                    className: ['input-control'],
                    value:  props.attributes.uri,
                    placeholder: '{$this->getBlock()->getDescription()}',
                    onChange: function( uri ) {
                        props.setAttributes( { uri: uri } );
                    },
                } )
            ]);
        };";
    }
}