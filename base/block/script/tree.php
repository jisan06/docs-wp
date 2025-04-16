<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/easydoc for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class BlockScriptTree extends BlockScriptList
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPath('base/resources/assets/js/block/tree.js');
    }
}