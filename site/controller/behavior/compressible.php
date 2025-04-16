<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

/**
 * Class ControllerBehaviorCompressible
 *
 * Example workflow:
 *
 * * Send a JSON POST request with {action: 'compress'} in the body and a query string such as ?slug[]=foo&slug[]=bar&slug[]=baz
 * * In the response, you will have {archive: 'foo'}
 * * Send a GET request to download controller: /download/?archive=foo
 *
 * @package EasyDocLabs\EasyDoc\Site
 */
class ControllerBehaviorCompressible extends Library\ControllerBehaviorAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => static::PRIORITY_NORMAL
        ]);

        parent::_initialize($config);
    }

    public function isSupported()
    {
        return parent::isSupported() && class_exists('\ZipArchive');
    }

    public function purgeExpiredFiles()
    {
        $folder = $this->_getFolder();

        if (is_dir($folder)) {
            foreach (new \DirectoryIterator($folder) as $file) {
                if ($file->isDot()) continue;

                $name = \Foliokit\basename($file->getPathname(), '.'.$file->getExtension());

                list($ids, $expire) = explode('_', $name, 2);

                if (!empty($ids) && !empty($expire) && time() > $expire) {
                    unlink($file->getPathname());
                }
            }
        }
    }

    protected function _beforeRender(Library\ControllerContext $context)
    {
        if ($archive = $context->getRequest()->getQuery()->get('archive', 'raw')) {
            $archive = $this->getObject('filter.cmd')->sanitize($this->_decryptFilename($archive));

            if (!$archive) {
                throw new \UnexpectedValueException('Invalid file name');
            }

            $path = $this->_getFolder().'/'.$archive.'.zip';

            if (file_exists($path)) {
                list($ids, $expire) = explode('_', $archive, 2);

                if (time() > $expire) {
                    throw new \RuntimeException('File expired');
                }

                if (!$context->request->isStreaming()) {
                    $ids = explode('-', $ids);

                    $documents = $this->getModel()->id($ids)->fetch();

                    foreach ($documents as $document) {
                        if ($documents->isHittable()) {
                            $document->hit();
                        }
                    }
                }

                try {
                    $context->getResponse()
                        ->attachTransport('stream')
                        ->setContent($path, 'application/octet-stream')
                        ->getHeaders()->set('X-Content-Disposition-Filename', 'Documents.zip');
                }
                catch (\InvalidArgumentException $e) {
                    throw new Library\ControllerExceptionResourceNotFound('File not found');
                }
            }
            else throw new Library\ControllerExceptionResourceNotFound('File not found');

            return false;
        }

        return true;
    }

    /**
     * Redirect to the remote url
     *
     * If the file is being downloaded this method offers support for rewriting dropbox, google drive and google
     * docs urls to their correct download URL's.
     *
     * @param  Library\ControllerContext $context
     * @return Library\ObjectConfigJson
     */
    protected function _actionCompress(Library\ControllerContext $context)
    {
        $container = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch();
        $folder    = $container->fullpath.'/.multidownload';

        if (!is_dir($folder))
        {
            $result = mkdir($folder, 0755);

            if (!$result || !is_dir($folder)) {
                throw new Library\ControllerExceptionActionFailed('Unable to create directory for multidownload files');
            }
        }

        $query = $context->getRequest()->getQuery();

        // If the query has both id and uuid, pick id as uuid usually comes from the current category
        if ($query->has('id') && $query->has('uuid')) {
            $query->set('uuid', null);
        }

        $documents = $this->browse();

        $context->setEntity($documents); // Make rowset available for other commands down the road

        if (!count($documents)) {
            throw new \UnexpectedValueException('Found no documents to compress');
        }

        $archive_name = $this->_getArchiveName($documents);
        $zip          = new \ZipArchive();
        $result       = $zip->open($folder.'/'.$archive_name.'.zip', \ZipArchive::CREATE);

        if ($result !== true) {
            throw new \RuntimeException("cannot open <$archive_name>\n");
        }

        $filenames = [];
        foreach ($documents as $document) {
            $filename = \Foliokit\basename($document->storage_path);
            $fullpath = $document->storage->fullpath;

            $i = 1;
            while (in_array($filename, $filenames)) {
                $pathinfo = \Foliokit\pathinfo($filename);
                $filename .= $pathinfo['filename'].' ('.$i.').'.$pathinfo['extension'];
                $i++;
            }

            if (is_file($fullpath) && file_exists($fullpath)) {
                $zip->addFile($fullpath, $filename);
            }

            $filenames[] = $filename;
        }

        $response = [
            'route' => $this->getObject('router')
                            ->generate('easydoc:', [
                                'endpoint' => '~documents',
                                'view'     => 'download',
                                'archive'  => $this->_encryptFilename($archive_name)
                            ])->toString(),
            'count' => $zip->numFiles
        ];

        $zip->close();

        return new Library\ObjectConfigJson($response);
    }

    protected function _getArchiveName($documents)
    {
        $ids = [];
        $size = 0;
        foreach ($documents as $document) {
            $ids[] = $document->id;
            $size += $document->size;
        }

        sort($ids);

        // now + 1 hour per 50MB. Minimum 30 minutes, Maximum 6 hours
        $expires   = time()+min(3600*6, max(1800, ($size/1048576)*72));
        $filename = implode('-', $ids).'_'.$expires;

        return $filename;
    }

    protected function _getEncryptionDetails()
    {
        $encrypt_method = "AES-256-CBC";

        if (!in_array($encrypt_method, openssl_get_cipher_methods())) {
            $encrypt_method = "AES-128-CBC";
        }

        return [
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            'iv' => substr(hash('sha256', AUTH_KEY), 0, 16),
            'key' => hash('sha256', AUTH_SALT),
            'method' => $encrypt_method
        ];
    }


    protected function _encryptFilename($string)
    {
        $arguments = $this->_getEncryptionDetails();

        return openssl_encrypt($string, $arguments['method'], $arguments['key'], 0, $arguments['iv']);
    }

    protected function _decryptFilename($string)
    {
        $arguments = $this->_getEncryptionDetails();

        return openssl_decrypt($string, $arguments['method'], $arguments['key'], 0, $arguments['iv']);
    }

    protected function _getFolder()
    {
        $container = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch();

        return $container->fullpath.'/.multidownload';
    }
}
