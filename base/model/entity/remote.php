<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityRemote extends Library\ModelEntityAbstract
{
    public function getPropertyExtension()
    {
        $path = parse_url($this->path ?: '', PHP_URL_PATH);

        return pathinfo($path ?: '', PATHINFO_EXTENSION);
    }

    public function getPropertyFilename()
    {
        return pathinfo($this->path ?: '', PATHINFO_EXTENSION);
    }

    public function getPropertyScheme()
    {
        return parse_url($this->path ?: '', PHP_URL_SCHEME);
    }

    public function getPropertyFullpath()
    {
        return $this->path;
    }
}
