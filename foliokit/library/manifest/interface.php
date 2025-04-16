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
 * Manifest Interface
 *
 * @link https://en.wikipedia.org/wiki/Manifest_file
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Manifest
 */
interface ManifestInterface extends ObjectInterface
{
    /**
     * Get the name
     *
     * @return string|false Returns FALSE if the manifest doesn't exist
     */
    public function getName();

    /**
     * Get the description
     *
     * @return string|false Returns FALSE if the manifest doesn't exist
     */
    public function getDescription();

    /**
     * Get the version
     *
     * See @link http://semver.org/spec/v2.0.0.html
     *
     * @return string|false Returns FALSE if the manifest doesn't exist
     */
    public function getVersion();

    /**
     * Get the license
     *
     * @return string|false
     */
    public function getLicense();

    /**
     * Get the copyright
     *
     * @return string|false Returns FALSE if the manifest doesn't exist
     */
    public function getCopyright();

    /**
     * Get the homepage
     *
     * @return string|false Returns FALSE if the manifest doesn't exist
     */
    public function getHomepage();

    /**
     * Get the homepage
     *
     * @return array|false Returns FALSE if the manifest doesn't exist
     */
    public function getAuthors();

}
