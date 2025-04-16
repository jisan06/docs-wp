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
 * Activity Translator Interface.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
interface ActivityTranslatorInterface
{
    /**
     * Translates an activity format.
     *
     * @param string $string The activity format to translate.
     * @return string The translated activity format.
     */
    public function translateActivityFormat(ActivityInterface $activity);

    /**
     * Translates an activity token.
     *
     * @param string|ActivityObjectInterface $token    The activity token.
     * @param ActivityInterface              $activity The activity object.
     * @return string The translated token.
     */
    public function translateActivityToken($token, ActivityInterface $activity);

    /**
     * Activities token
     *
     * Tokens are activity objects being referenced in the activity format. They represent variables contained
     * in an activity message.
     *
     * @param ActivityInterface $activity
     * @return array A list containing ActivityObjectInterface objects.
     */
    public function getActivityTokens(ActivityInterface $activity);
}