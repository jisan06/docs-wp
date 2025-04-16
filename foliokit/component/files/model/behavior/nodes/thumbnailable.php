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
 * Nodes Thumbnailable Model behavior
 *
 * Handles Nodes thumbnailable requests by adding the thumbnails state.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelBehaviorNodesThumbnailable extends ModelBehaviorThumbnailable
{
    protected function _afterFetch(Library\ModelContextInterface $context)
    {
        // Do nothing ... Nodes model fetches files from the files model which is also thumbnailable
        // The thumbnails state is forwarded to files model at this time
    }
}