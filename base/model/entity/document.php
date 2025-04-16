<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Files;
use EasyDocLabs\Library;

class ModelEntityDocument extends Library\ModelEntityRow
{
    protected static $_image_path = null;

    protected static $_icon_path = null;

    /**
     * A map of extension=>mimetype
     * @var array
     */
    protected static $_mimetype_cache = [];

    /**
     * A map of category_id=>ModelEntityCategory
     * @var array
     */
    protected static $_category_cache = [];

    const STATE_PUBLISHED = 1;
    const STATE_DRAFTED   = 0;
    const STATE_SCHEDULED = 2;

    public static $extension_type_map = [
        'archive'     => ['7z','gz','rar','tar','zip'],
        'audio'       => ['mp3', '3gp', 'act', 'aiff', 'aac', 'amr', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'm4a', 'm4p', 'ogg', 'oga', 'ra', 'rm', 'raw', 'tta', 'vox', 'wav', 'wma', 'wv', 'webm'],
        'document'    => ['pdf', 'csv', 'doc','docx','odc','odg','odp','ods', 'odt', 'otc','otg', 'otp','ott', 'rtf','txt','ppt','pptx','pps','tsv', 'tab','xls', 'xlsx','xml'],
        'image'       => ['ai','bmp','cr2','crw','eps','erf','gif','jpg','jpeg','nef','orf','png','pbm','pgm', 'ppm','psd','svg','tif','tiff','x3f','xbm'],
        'video'       => ['webm','mkv','flv','vob','ogv','ogg','avi','rm','rmvb','mp4','m4p','m4v','asf','mpg','mpeg','mpv','mpe','3gp','3g2','roq','nsv'],
        'executable'  => ['cmd', 'exe','bat','bin','apk','msi', 'dmg']
    ];

    /**
     * viewable extensions
     *
     * @var array
     */
    public static $viewable_extensions = ['mp3','ogg','mp4','wav','webm','mse', 'jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'xbm', 'bmp'];

    public function save()
    {
        $this->storage_path = trim($this->storage_path);

        if ($this->isNew() && empty($this->storage_type)) {
            $this->storage_type = 'file';
        }

        if (!in_array($this->storage_type, ['file', 'remote']))
        {
            $this->setStatusMessage($this->getObject('translator')->translate('Storage type is not available'));
            $this->setStatus(Library\Database::STATUS_FAILED);

            return false;
        }

        // Sanitize dates

        $dates = ['publish_on', 'unpublish_on'];

        $filter = $this->getObject('com:easydoc.filter.calendar');

        foreach ($dates as $date) {
            $this->{$date} = $filter->sanitize($this->{$date});
        }

        if ($this->storage_type == 'remote')
        {
            $schemes = $this->getSchemes();
            $scheme  = parse_url($this->storage_path, PHP_URL_SCHEME);

            if (isset($schemes[$scheme]) && $schemes[$scheme] === false)
            {
                $this->setStatusMessage($this->getObject('translator')->translate('Storage type is not allowed'));
                $this->setStatus(Library\Database::STATUS_FAILED);

                return false;
            }
        }

        if (empty($this->easydoc_category_id))
        {
            if ($this->isNew())
            {
                $this->setStatusMessage($this->getObject('translator')->translate('Category cannot be empty'));
                $this->setStatus(Library\Database::STATUS_FAILED);

                return false;
            }
            else
            {
                unset($this->easydoc_category_id);
                unset($this->_modified['easydoc_category_id']);
            }
        }

        $params = $this->getParameters();

        if (!$params->icon)
        {
            $icon = $this->getIcon($this->extension);

            if (empty($icon)) {
                $icon = 'default';
            }

            if ($this->isNew())
            {
                // We need to set it on the entity itself for the parameters to get saved (parameterizable limitation)

                $params['icon'] = $icon;

                $this->setProperty('parameters', $params);
            }
            else $this->getParameters()->icon = $icon;
        }

        $result = parent::save();

        if (!$this->isNew() && isset($this->contents))
        {
            $model = $this->getObject('com:easydoc.model.document_contents');
            $contents = $model->id($this->id)->fetch();

            if ($contents->isNew()) {
                $contents = $model->create();
                $contents->id = $this->id;
            }

            $contents->contents = $this->contents;
            $contents->save();
        }

        return $result;
    }

    public function toArray()
    {
        $data              = parent::toArray();
        $data['extension'] = $this->extension;
        $data['size']      = $this->size;
        $data['kind']      = $this->kind;

        unset($data['storage']);

        return $data;
    }

    public function getStorageInfo()
    {
        if (!isset($this->_data['storage']))
        {
            if (!empty($this->_data['storage_type']))
            {
                $this->_data['storage'] = $this->getObject('com:easydoc.model.storages')
                    ->container('easydoc-files')
                    ->storage_type($this->_data['storage_type'])
                    ->storage_path($this->_data['storage_path'])
                    ->fetch();
            }
            else  $this->_data['storage'] = null;
        }

        return $this->_data['storage'];
    }

    /**
     * Get a list of the supported streams.
     *
     * We use a whitelist approach to be secure against unknown streams
     *
     * @return array
     */
    public function getSchemes()
    {
        $streams = stream_get_wrappers();
        $allowed  = [
            'http'  => true,
            'https' => true,
            'file'  => false,
            'ftp'   => false,
            'sftp'  => false,
            'php'   => false,
            'zlib'  => false,
            'data'  => false,
            'glob'  => false,
            'expect'=> false
        ];

        if (in_array('file', $streams)) {
            $allowed['file'] = true;
        }

        // Following streams depend on allow_url_fopen
        if (ini_get('allow_url_fopen'))
        {
            foreach (['ftp', 'sftp'] as $stream)
            {
                if (in_array($stream, $streams)) {
                    $allowed[$stream] = true;
                }
            }
        }

        return $allowed;
    }

    public function getIcon($extension)
    {
        $extension = strtolower($extension);

        foreach (Files\TemplateHelperIcon::getIconExtensionMap() as $type => $extensions)
        {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return false;
    }

    public function getProperty($name)
    {
        if ($name === 'alias') {
            return isset($this->_data['alias']) ? $this->_data['alias'] : $this->id.'-'.$this->slug;
        }

        if ($name === 'contents') {
            if (isset($this->_data['contents'])) {
                return $this->_data['contents'];
            }
            elseif (!$this->isNew()) {
                $model = $this->getObject('com:easydoc.model.document_contents');

                return $model->id($this->id)->fetch()->contents;
            }
        }

        return parent::getProperty($name);
    }

    public function getPropertyImagePath()
    {
        if ($this->image)
        {
            if (static::$_image_path === null) {
                $container = $this->getObject('com:files.model.containers')->slug('easydoc-images')->fetch();

                static::$_image_path = $this->getObject('request')->getSiteUrl().'/'.$container->path;
            }

            $image = implode('/', array_map('rawurlencode', explode('/', $this->image)));

            return static::$_image_path.'/'.$image;
        }

        return null;
    }

    public function getPropertyIcon()
    {
        $icon = $this->getParameters()->get('icon', 'default');

        // Backwards compatibility: remove .png from old style icons
        if (substr($icon, 0, 5) !== 'icon:' && substr($icon, -4) === '.png') {
            $icon = substr($icon, 0, strlen($icon)-4);
        }

        return $icon;
    }

    public function getPropertyIconPath()
    {
        $path = $this->icon;

        if (substr($path, 0, 5) === 'icon:')
        {
            if (static::$_icon_path === null) {
                $container = $this->getObject('com:files.model.containers')->slug('easydoc-icons')->fetch();

                static::$_icon_path = $this->getObject('request')->getSiteUrl().'/'.$container->path;
            }

            $icon = implode('/', array_map('rawurlencode', explode('/', substr($path, 5))));
            $path = static::$_icon_path.'/'.$icon;
        } else {
            $path = null;
        }

        return $path;
    }

    public function getPropertyStorage()
    {
        return $this->getStorageInfo();
    }

    public function getPropertyCategory()
    {
        if (!isset(static::$_category_cache[$this->easydoc_category_id])) {
            $model = $this->getObject('com:easydoc.model.categories');

            static::$_category_cache[$this->easydoc_category_id] = $model->id($this->easydoc_category_id)->fetch();
        }

        return static::$_category_cache[$this->easydoc_category_id];
    }

    public function getPropertyDescriptionSummary()
    {
        $description = $this->description;

        if ($description) 
        {
            $position    = strpos($description, '<!--more-->');
            if ($position !== false) {
                return substr($description, 0, $position);
            }
        }
        
        return $description;
    }

    public function getPropertyDescriptionFull()
    {
        return str_replace('<!--more-->', '', $this->description ?: '');
    }

    public function getPropertySize()
    {
        if ($this->getStorageInfo()) {
            return $this->storage->size;
        }

        return null;
    }

    public function getPropertyExtension()
    {
        if ($this->getStorageInfo()) {
            return $this->storage->extension;
        }

        return null;
    }

    public function getPropertyMimetype()
    {
        $result = null;

        if ($this->getStorageInfo())
        {
            $result = $this->storage->mimetype;

            if (!$result && $this->extension)
            {
                if (!isset(static::$_mimetype_cache[$this->extension])) {
                    $entity = $this->getObject('com:files.model.mimetypes')
                        ->extension($this->extension)
                        ->fetch();

                    if ($entity && $entity->mimetype) {
                        static::$_mimetype_cache[$this->extension] = $entity->mimetype;
                    }
                }

                if (isset(static::$_mimetype_cache[$this->extension])) {
                    return static::$_mimetype_cache[$this->extension];
                }
            }
        }

        return $result;
    }

    public function getPropertyFiletype()
    {
        $result = null;

        if ($this->getStorageInfo())
        {
            $extension = strtolower($this->extension);

            foreach (static::$extension_type_map as $type => $extensions) {
                if (in_array($extension, $extensions)) {
                    $result = $type;
                    break;
                }
            }
        }

        return $result;
    }

    public function isArchive()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['archive']);
    }

    public function isAudio()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['audio']);
    }

    public function isDocument()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['document']);
    }

    public function isExecutable()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['executable']);
    }

    public function isImage()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['image']);
    }

    public function isPreviewableImage()
    {
        return $this->isImage() && in_array($this->extension, self::$viewable_extensions);
    }

    public function isVideo()
    {
        return in_array(strtolower($this->extension ?: ''), static::$extension_type_map['video']);
    }

    public function isYoutube()
    {
        if (strpos($this->storage->path, 'youtube.com/watch') === false
            && strpos($this->storage->path, 'youtu.be') === false) {
            return false;
        }

        return true;
    }

    public function isVimeo()
    {
        if (strpos($this->storage->path, 'vimeo.com') === false) {
            return false;
        }

        return true;
    }

    public function isPlayable()
    {
        if ($this->isVideo()) {
            return true;
        }

        if ($this->isAudio()) {
            return true;
        }

        if ($this->isVimeo()) {
            return true;
        }

        if ($this->isYoutube()) {
            return true;
        }

        return false;
    }

    public function isTopSecret()
    {
        return false;
    }

    /**
     * Returns the kind of the file
     *
     * Used in RSS:Media (audio, image, video, executable, document)
     *
     * @return string
     */
    public function getPropertyKind()
    {
        $result = null;

        if ($this->getStorageInfo())
        {
            $result = 'document';

            if ($this->isAudio()) {
                $result = 'audio';
            }
            elseif ($this->isVideo()) {
                $result = 'video';
            }
            elseif ($this->isImage()) {
                $result = 'image';
            }
            elseif ($this->isExecutable()) {
                $result = 'executable';
            }
        }

        return $result;
    }

    public function isLocal()
    {
        return $this->storage_type === 'file';
    }

    /**
    *  Show text if extension is previewable in google docs
    *
    * @return bool
    */
    public function gDocsPreviewable()
    {
        $pattern = '/https?:\/\/(docs|drive)\.google.com\/\S+/';

        if ($this->storage_type == 'remote' && preg_match($pattern, $this->storage_path)) {
            return true;
        }

        return false;
    }
}
