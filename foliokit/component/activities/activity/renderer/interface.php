<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

/**
 * Activity Renderer Interface.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
interface ActivityRendererInterface
{
    /**
     * Renders an activity.
     *
     * @param ActivityInterface $activity The activity object.
     * @param array                          $config   An optional configuration array.
     *
     * @return string The rendered activity.
     */
    public function render(ActivityInterface $activity, $config = []);
}