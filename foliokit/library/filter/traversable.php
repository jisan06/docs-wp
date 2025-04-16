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
 * Filter Traversable Interface
 *
 * This interface signals FilterAbstract::getInstance() to decorate the Filter with a FilterIterator. The iterator
 * will traverse the data if it's traversable and filter each value separately.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 * @see FilterAbstract::getInstance()
 */
interface FilterTraversable {}