<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;
use EasyDocLabs\Library\ObjectConfigJson;

class ControllerBatchscan extends Library\ControllerView implements Library\ControllerModellable
{
    /**
     * Model object or identifier (com://APP/COMPONENT.model.NAME)
     *
     * @var	string|object
     */
    protected $_model;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Set the model identifier
        $this->_model = $config->model;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'formats'   => array('json', 'binary'),
            'model'     => 'model.empty',
        ));
        
        parent::_initialize($config);
    }

    protected function _actionRender(Library\ControllerContextInterface $context)
    {
        if (!$this->getObject('com:easydoc.controller.behavior.scannable')->isSupported()) {
            $error = $this->getObject('translator')->translate('Scanning documents is only available with Ait Theme Club Connect');
            $context->response->addMessage($error);

            return;
        }
        
        return parent::_actionRender($context);
    }

    protected function _actionScan(Library\ControllerContextInterface $context)
    {
        try {
            $id = $this->getObject('request')->query->get('id', 'int');
            $controller = $this->getObject('com://admin/easydoc.controller.document', [
                'behaviors' => ['permissible' => [
                    'permission' => 'com:easydoc.controller.permission.yesman'
                ]],
            ]);
    
            $entity = $controller->id($id)->read();
    
            if ($entity->isNew()) {
                throw new Library\ControllerExceptionRequestInvalid('Invalid document id');
            }
            
            $response = $controller->id($id)->edit(['force_scan' => 1, 'force_scan_response' => 1]);

            try {
                if (!is_string($response)) {
                    throw new \Exception('Invalid response');    
                }
                $response = json_decode($response, true);
            } catch (\Exception $e) {
                $response = ["error" => "unknown response"];
            }

            // get document again to return updated data
            $docs = $this->_getDocuments([$id]);
            $doc = !empty($docs) ? $docs[0] : null;

            return new ObjectConfigJson(['result' => true, 'document' => $doc, 'response' => $response]); 
        } catch (\Exception $e) {
            return new ObjectConfigJson(['result' => false, 'error' => $e->getMessage()]); 
        }
    }

    protected function _unhalt()
    {
        try {
            $query = $this->getObject('database.query.select')
                ->columns(['state'])
                ->table('scheduler_jobs')
                ->where('identifier = :identifier')
                ->bind(['identifier' => 'com://admin/easydoc.job.scans']);
            
            $state = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_FIELD);

            $json   = json_decode($state, true);

            if (isset($json['halt']) && $json['halt'] === true) {
                unset($json['halt']);

                $jsonString = json_encode($json);

                if ($jsonString === "[]" || !$jsonString) {
                    $jsonString = '{}';
                }

                $query = $this->getObject('database.query.update')
                    ->table('scheduler_jobs')
                    ->values('state = :state')
                    ->where('identifier = :identifier')
                    ->bind(['identifier' => 'com://admin/easydoc.job.scans', 'state' => $jsonString]);

                $this->getObject('database.driver.mysqli')->update($query);
            }
        } catch (\Exception $e) {
            if (JDEBUG) {
                throw $e;
            }
        }
    }

    protected function _clearThumbnails()
    {
        $query = $this->getObject('database.query.select')
            ->columns(['tbl.image'])
            ->table(['tbl' => 'easydoc_documents'])
            ->where('tbl.image <> :image')
            ->bind([
                'image' => ''
            ]);

        $list    = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_FIELD_LIST);
        $missing = [];

        $thumbnail_path = $this->getObject('com:files.model.containers')->slug('easydoc-images')->fetch()->fullpath;

        foreach ($list as $image) {
            if (!is_file($thumbnail_path.'/'.$image)) {
                $missing[] = $image;
            }
        }

        $missing = array_unique($missing);
        $count   = count($missing);
        $offset  = 0;
        $limit   = 10;
        $query   = $this->getObject('database.query.update')
            ->table('easydoc_documents')
            ->values('image = :empty')
            ->where('image IN :image');

        while ($offset < $count)
        {
            $current  = array_slice($missing, $offset, $limit);

            $query->bind(['empty' => '', 'image' => $current]);

            $this->getObject('database.driver.mysqli')->update($query);

            $offset += $limit;
        }
    }

    protected function _actionStartup(Library\ControllerContextInterface $context)
    {
        $show_scanned = $this->getObject('request')->query->get('show_scanned', 'boolean');
        
        $this->_unhalt();
        $this->_clearScans();
        $this->_clearThumbnails();

        $ids = $this->_findDocuments($show_scanned);

        $result = $this->_getDocuments($ids);

        return new ObjectConfigJson(['result' => true, 'documents' => $result]);
    }

    protected function _getDocuments($ids)
    {
        $query = $this->getObject('database.query.select')
            ->columns([
                'tbl.easydoc_document_id', 'tbl.title', 'tbl.storage_path', 'tbl.storage_type', 'tbl.created_on', 'tbl.image',
                'categoryTitle' => 'category.title',
                'categoryId' => 'tbl.easydoc_category_id',
                'ocrable' => 'SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :ocr_extensions',
                'thumbnailable' => '("1" = :automatic_thumbnails AND SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :thumbnail_extensions)',
                'hasOcr' => 'contents.easydoc_document_id',
                'hasThumbnail' => '(tbl.image <> :image)',
                'isScannedBefore' => '(tbl.params LIKE :scanned)',
                ])
            ->table(['tbl' => 'easydoc_documents'])
            ->where('tbl.easydoc_document_id IN :ids')
            ->join(['contents' => 'easydoc_document_contents'], 'contents.easydoc_document_id = tbl.easydoc_document_id')
            ->join(['category' => 'easydoc_categories'], 'category.easydoc_category_id = tbl.easydoc_category_id')
            ->bind([
                'ids' => $ids,
                'image' => '',
                'automatic_thumbnails' => $this->getObject('com://admin/easydoc.model.configs')->fetch()->thumbnails ? '1' : '2',
                'storage_type' => 'file',
                'thumbnail_extensions' => EasyDoc\ControllerBehaviorScannable::$thumbnail_extensions,
                'ocr_extensions' => EasyDoc\ControllerBehaviorScannable::$ocr_extensions,
                'scanned' => '%"scanned"%',
            ])
            ->order('tbl.created_on', 'desc');
            
        $docs = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT_LIST);

        $fullpath = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;
        foreach ($docs as $doc) {
            $doc->fileExists = file_exists($fullpath.'/'.$doc->storage_path);
            $doc->fileSize = $doc->fileExists ? @filesize($fullpath.'/'.$doc->storage_path) : 0;
            $doc->scannable = $doc->fileExists && $doc->fileSize <= EasyDoc\ControllerBehaviorScannable::MAXIMUM_FILE_SIZE;

            foreach ($doc as $key => &$value) {
                if ($key === "easydoc_document_id") {
                    $value = (int) $value;
                    continue; 
                }

                if ($key === "hasOcr") {
                    if ($value === null) {
                        $value = false;
                    }

                    if ($value === "") { // empty contents even though the file was scanned
                        $value = true;
                    }
                    
                    $value = (bool) $value;
                }

                if ($value === "0" || $value === 0) {
                    $value = false;
                } else if ($value === "1" || $value === 1) {
                    $value = true;
                }
            }
            unset($value);
        }

        return $docs;
    }
    
    protected function _findDocuments($show_scanned = false) 
    {
        $retry_failed = true;

        $query = $this->getObject('database.query.select')
            ->columns(['tbl.easydoc_document_id'])
            ->table(['tbl' => 'easydoc_documents'])
            ->join(['scan' => 'easydoc_scans'], 'scan.identifier = tbl.uuid')
            //->where('scan.identifier IS NULL')
            ->join(['contents' => 'easydoc_document_contents'], 'contents.easydoc_document_id = tbl.easydoc_document_id')
            ->where('tbl.storage_type = :storage_type')
            ->bind([
                'image' => '',
                'automatic_thumbnails' => $this->getObject('com://admin/easydoc.model.configs')->fetch()->thumbnails ? '1' : '2',
                'storage_type' => 'file',
                'thumbnail_extensions' => EasyDoc\ControllerBehaviorScannable::$thumbnail_extensions,
                'ocr_extensions' => EasyDoc\ControllerBehaviorScannable::$ocr_extensions
            ]);

        if (!$show_scanned) {
            $query->where('((contents.easydoc_document_id IS NULL AND SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :ocr_extensions)')
                ->where('("1" = :automatic_thumbnails AND tbl.image = :image AND SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :thumbnail_extensions))', 'OR');
        } else {
            $query->where('SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :ocr_extensions OR SUBSTRING_INDEX(tbl.storage_path, ".", -1) IN :thumbnail_extensions');
        }

        if (!$retry_failed) {
            $query->where('tbl.params NOT LIKE :scanned')
                ->bind(['scanned' => '%"scanned"%',]);
        }
        
        return $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_FIELD_LIST);
    }

    protected function _clearScans() 
    {
        try {
            $query = $this->getObject('database.query.update')
            ->table('easydoc_scans')
            ->values('status = :status')
            ->where('status <> :status')
            ->bind(['status' => EasyDoc\ControllerBehaviorScannable::STATUS_PENDING]);

            $this->getObject('database.driver.mysqli')->update($query);
        } catch (\Exception $e) {
            if (JDEBUG) {
                throw $e;
            }
        }
    }


    public function getView()
    {
        $view = parent::getView();
        $view->setModel($this->getModel());

        $view->scannerData = [
            'imagePath' => $this->getObject('request')->getSiteUrl().'/'.$this->getObject('com:files.model.containers')->slug('easydoc-images')->fetch()->path,
            'maximumFileSize' => EasyDoc\ControllerBehaviorScannable::MAXIMUM_FILE_SIZE,
            'thumbnailExtensions' => EasyDoc\ControllerBehaviorScannable::$thumbnail_extensions,
            'ocrExtensions' => EasyDoc\ControllerBehaviorScannable::$ocr_extensions,
        ];
        $view->easydocVersion = EasyDoc\Version::VERSION;

        return $view;
    }

    /**
     * Get the model object attached to the controller
     *
     * @throws	\UnexpectedValueException	If the model doesn't implement the ModelInterface
     * @return	Library\ModelInterface
     */
    public function getModel()
    {
        if(!$this->_model instanceof Library\ModelInterface)
        {
            //Make sure we have a model identifier
            if(!($this->_model instanceof Library\ObjectIdentifier)) {
                $this->setModel($this->_model);
            }

            $this->_model = $this->getObject($this->_model);

            if(!$this->_model instanceof Library\ModelInterface)
            {
                throw new \UnexpectedValueException(
                    'Model: '.get_class($this->_model).' does not implement Library\ModelInterface'
                );
            }

            //Inject the request into the model state
            $this->_model->getState()->insert('status', 'cmd');
            $this->_model->setState($this->getRequest()->query->toArray());
        }

        return $this->_model;
    }

    /**
     * Method to set a model object attached to the controller
     *
     * @param	mixed	$model An object that implements KObjectInterface, KObjectIdentifier object
     * 					       or valid identifier string
     * @return	KControllerView
     */
    public function setModel($model)
    {
        if(!($model instanceof Library\ModelInterface))
        {
            if(is_string($model) && strpos($model, '.') === false )
            {
                // Model names are always plural
                if(Library\StringInflector::isSingular($model)) {
                    $model = Library\StringInflector::pluralize($model);
                }

                $identifier			= $this->getIdentifier()->toArray();
                $identifier['path']	= array('model');
                $identifier['name']	= $model;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($model);

            $model = $identifier;
        }

        $this->_model = $model;

        return $this->_model;
    }
}