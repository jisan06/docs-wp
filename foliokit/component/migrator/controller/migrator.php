<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

class ControllerMigrator extends Library\ControllerView implements Library\ControllerModellable
{
    /**
     * Model object or identifier (com://APP/COMPONENT.model.NAME)
     *
     * @var	string|object
     */
    protected $_model;

    /**
     * Temporary folder for migration files
     * @var string
     */
    protected $_temporary_folder;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Set the model identifier
        $this->_model = $config->model;

        $this->setTemporaryFolder($config->folder);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'formats'   => array('json', 'binary'),
            'model'     => 'model.empty'
        ));

        parent::_initialize($config);
    }

    /**
     * @return string
     */
    public function getTemporaryFolder()
    {
        return $this->_temporary_folder;
    }

    /**
     * @param string $temporary_folder
     * @return ControllerMigrator
     */
    public function setTemporaryFolder($temporary_folder)
    {
        $this->_temporary_folder = $temporary_folder;

        return $this;
    }

    /**
     * Extension version getter.
     *
     * @param  string $extension Extension name (without com_ prefix)
     * @return string|null The version number, null if the extension wasn't found.
     */
    public function getVersion($extension)
    {
        try {
            $version = $this->getObject(sprintf('com:%s.version', $extension))->getVersion();
        } catch (\Exception $e) {
            $version = null;
        }

        return $version;
    }

    public function getView()
    {
        $view = parent::getView();

        //Set the model in the view
        $view->setModel($this->getModel());

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
     * @param	mixed	$model An object that implements Library\ObjectInterface, Library\ObjectIdentifier object
     * 					       or valid identifier string
     * @return	Library\ControllerView
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