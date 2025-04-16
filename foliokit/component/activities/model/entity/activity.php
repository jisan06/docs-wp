<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activity Entity.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ModelEntityActivity extends Library\ModelEntityRow implements ActivityInterface
{
    /**
     * An associative list containing the find call results.
     *
     * @var array
     */
    static protected $_find_results = [];

    /**
     * The activity format.
     *
     * @var string
     */
    protected $_format;

    /**
     * A list of required columns.
     *
     * @var array
     */
    protected $_required = ['package', 'name', 'action', 'status'];

    /**
     * The activity object database table name.
     *
     * @var string
     */
    protected $_object_table;

    /**
     * The activity object database table id column.
     *
     * @var string
     */
    protected $_object_column;

    /**
     * A list of supported activity object names.
     *
     * @var array
     */
    protected $_objects;

    /**
     * The activities translator identifier.
     *
     * @var mixed
     */
    protected $_translator;

    /**
     * The activity locale.
     *
     * @var string
     */
    protected $_locale;

    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_format        = $config->format;
        $this->_objects       = Library\ObjectConfig::unbox($config->objects);
        $this->_object_table  = $config->object_table;
        $this->_object_column = $config->object_column;
        $this->_translator    = $config->translator;
    }

    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $data = $config->data;

        $config->append([
            'format'        => '{actor} {action} {object.type} title {object}',
            'object_table'  => $data->package . '_' . Library\StringInflector::pluralize($data->name),
            'object_column' => $data->package . '_' . $data->name . '_id',
            'translator'    => 'com:activities.activity.translator',
            'objects'       => ['actor', 'action', 'object', 'target', 'generator', 'provider']
        ]);

        parent::_initialize($config);
    }

    public function save()
    {
        // Activities are immutable.
        if (!$this->isNew()) {
            throw new \RuntimeException('Activities cannot be modified.');
        }

        if (!$this->status)
        {
            // Attempt to provide a default status.
            switch ($this->verb)
            {
                case 'add':
                    $status = Library\Database::STATUS_CREATED;
                    break;
                case 'edit':
                    $status = Library\Database::STATUS_UPDATED;
                    break;
                case 'delete':
                    $status = Library\Database::STATUS_DELETED;
                    break;
                default:
                    $status = null;
            }

            if ($status) {
                $this->status = $status;
            }
        }

        foreach ($this->_required as $column)
        {
            if (empty($this->$column))
            {
                $this->setStatus(Library\Database::STATUS_FAILED);
                $this->setStatusMessage($this->getObject('translator')->translate('Missing required data'));
                return false;
            }
        }

        return parent::save();
    }

    /**
     * Get the activity format.
     *
     * An activity format consist on a template for rendering activity messages.
     *
     * @return string The activity string format.
     */
    public function getActivityFormat()
    {
        // Avoid errors due to magic getter recursions
        if (!$format = Library\ObjectArray::offsetGet('format')) {
            $format = $this->format;
        }

        return $format;
    }

    /**
     * Get the activity objects
     *
     * @return array An array containing ActivityObjectInterface objects.
     */
    public function getActivityObjects()
    {
        return $this->objects;
    }

    /**
     * Get the activity icon.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See icon property.
     *
     * @return ActivityMedialinkInterface|null The activity icon, null if the activity does not have an
     *                                                      icon.
     */
    public function getActivityIcon()
    {
        return $this->icon;
    }

    /**
     * Get the activity id.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See id property.
     *
     * @return string The activity ID.
     */
    public function getActivityId()
    {
        return $this->uuid;
    }

    /**
     * Get the activity published date.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See published property.
     *
     * @return KDateInterface The published date.
     */
    public function getActivityPublished()
    {
        return $this->getObject('lib:date', ['date' => $this->created_on]);
    }

    /**
     * Get the activity verb.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See verb property.
     *
     * @return string The activity verb.
     */
    public function getActivityVerb()
    {
        return $this->verb;
    }

    /**
     * Get the activity actor.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See actor property.
     *
     * @return ActivityObjectInterface The activity actor object.
     */
    public function getActivityActor()
    {
        return $this->actor;
    }

    /**
     * Get the activity object.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See object property.
     *
     * @return ActivityObjectInterface|null The activity object, null if the activity does not have an
     *                                                   object.
     */
    public function getActivityObject()
    {
        return $this->object;
    }

    /**
     * Get the activity target.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See target property.
     *
     * @return ActivityObjectInterface|null The activity target object, null if the activity does no have
     *                                                   a target.
     */
    public function getActivityTarget()
    {
        return $this->target;
    }

    /**
     * Get the activity generator.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See generator property.
     *
     * @return ActivityObjectInterface|null The activity generator object, null if the activity does not
     *                                                   have a generator.
     */
    public function getActivityGenerator()
    {
        return $this->generator;
    }

    /**
     * Get the activity provider.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#activity See provider property.
     *
     * @return ActivityObjectInterface|null The activity provider object, null if the activity does not
     *                                                   have a provider.
     */
    public function getActivityProvider()
    {
        return $this->provider;
    }

    /**
     * Get the activity action.
     *
     * @return ActivityObjectInterface
     */
    public function getActivityAction()
    {
        return $this->_getObject($this->_getConfig('action'));
    }

    /**
     * Get the format property.
     *
     * @return string
     */
    public function getPropertyFormat()
    {
        // Make un-translated format available for recursive calls.
        Library\ObjectArray::offsetSet('format', $this->_format);

        return $this->getTranslator()->translateActivityFormat($this);
    }

    /**
     * Locale setter.
     *
     * @param string $locale The activity locale.
     * @return ActivityInterface
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Locale getter.
     *
     * @return string The activity locale.
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->getActivityFormat(); // Locale gets calculated and set when translating format
        }

        return $this->_locale;
    }

    /**
     * Get the activity objects
     *
     * @return array An array containing ActivityObjectInterface objects.
     */
    public function setTranslator(ActivityTranslatorInterface $translator)
    {
        $this->_translator = $translator;
        return $this;
    }

    /**
     * Set the activity translator.
     *
     * @param ActivityTranslatorInterface $translator
     * @return ActivityInterface
     */
    public function getTranslator()
    {
        if (!$this->_translator instanceof ActivityTranslatorInterface)
        {
            $translator = $this->getObject('translator');

            if (!$translator instanceof ActivityTranslatorInterface) {
                $translator = $translator->decorate($this->_translator);
                $this->getObject('manager')->setObject('translator', $translator);
            }

            $this->setTranslator($translator);
        }

        return $this->_translator;
    }

    /**
     * Get the verb property.
     *
     * @return string
     */
    public function getPropertyVerb()
    {
        return $this->action;
    }

    /**
     * Get the icon property.
     *
     * @return null
     */
    public function getPropertyIcon()
    {
        return null; // No icon by default.
    }

    /**
     * Get the actor property.
     *
     * @return null
     */
    public function getPropertyActor()
    {
        return $this->_getObject($this->_getConfig('actor'));
    }

    /**
     * Get the target property.
     *
     * @return null
     */
    public function getPropertyTarget()
    {
        return null; // Activities do not have targets by default.
    }

    /**
     * Get the object property.
     *
     * @return ActivityObject
     */
    public function getPropertyObject()
    {
        return $this->_getObject($this->_getConfig('object'));
    }

    /**
     * Get the generator property.
     *
     * @return ActivityObject
     */
    public function getPropertyGenerator()
    {
        return $this->_getObject($this->_getConfig('generator'));
    }

    /**
     * Get the provider property.
     *
     * @return ActivityObject
     */
    public function getPropertyProvider()
    {
        return $this->_getObject($this->_getConfig('provider'));
    }

    /**
     * Get the activity objects.
     *
     * @return array An array containing ActivityObjectInterface objects.
     */
    public function getPropertyObjects()
    {
        return $this->_getObjects($this->_objects);
    }

    /**
     * Prevent setting the package property.
     *
     * @return array An array containing ActivityObjectInterface objects.
     */
    public function setPropertyPackage($value)
    {
        if ($this->package && $this->package != $value) {
            throw new \RuntimeException('Entity package cannot be modified.');
        }

        return $value;
    }

    /**
     * Prevent removing the package property.
     *
     * @param string $name The property name.
     *
     * @throws RuntimeException When attempting to remove the package property.
     * @return Library\DatabaseRowAbstract
     */
    public function removeProperty($name)
    {
        if ($name == 'package') {
            throw new \RuntimeException('Entity package property cannot be removed.');
        }

        return parent::removeProperty($name);
    }

    /**
     * Returns a list of activity objects provided their labels.
     *
     * @param array $labels The object labels.
     *
     * @return array An array containing ActivityObjectInterface objects.
     */
    protected function _getObjects(array $labels = [])
    {
        $objects = [];

        foreach ($labels as $label)
        {
            $method = 'getActivity' . ucfirst($label);

            if (method_exists($this, $method))
            {
                $object = $this->$method();

                if ($object instanceof ActivityObjectInterface) {
                    $objects[$label] = $object;
                }
            }
        }

        return $objects;
    }

    /**
     * Get an activity object
     *
     * @param array $config An optional configuration array. The configuration array may contain activity object data as
     *                      defined by ActivityObjectInterface. Additionally the following parameters may
     *                      be passed in the configuration array:
     *                      <br><br>
     *                      - find (string): the label of an object to look for. If not found the object being created
     *                      is set as deleted (with its deleted property set to true) and non-linkable (with its url
     *                      property set to null). A call to a _findActivity{Label} method will be attempted for
     *                      determining if an object with label as defined by {Label} exists. See
     *                      {@link _findActivityActor()} as an example.
     *                      <br><br>
     *                      - translate (bool): translates displayName property if set to true.
     *                      - object (bool): the configuration array may contain arrays which represent configurations
     *                      for stacked activity objects. For them to be considered as object configurations, an object
     *                      property with its value set to true must be included in the configuration array.
     *
     * @return ActivityObject The activity object.
     */
    protected function _getObject($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append([
            'attributes' => [],
            'translate'  => true
        ]);

        // Process all object sub-properties.
        foreach ($config as $key => $value)
        {
            if ($value instanceof Library\ObjectConfig && $value->object === true) {
                $config->{$key} = $this->_getObject($value);
            }
        }

        if ($config->objectName && !$config->displayName) {
            $config->displayName = $config->objectName;
        }

        if (is_string($config->url)) {
            $config->url = $this->_getRoute($config->url);
        }

        // Make object non-linkable and set it as deleted if related entity is not found.
        if ($config->find && !$this->_findObject($config->find))
        {
            $config->url     = null;
            $config->deleted = true;
        }

        if ($config->image instanceof Library\ObjectConfig)
        {
            if (is_string($config->image->url)) {
                $config->image->url = $this->_getRoute($config->image->url);
            }

            $config->image = $this->getObject('com:activities.activity.medialink', ['data' => $config->image]);
        }

        // Cleanup config.
        foreach (['find', 'object'] as $property) {
            unset($config->$property);
        }

        return $this->getObject('com:activities.activity.object', ['data' => $config]);
    }

    /**
     * Get an activity object config.
     *
     * @param string $object The object name.
     * @return Library\ObjectConfig
     */
    protected function _getConfig($object)
    {
        $config = new Library\ObjectConfig();

        $method = '_' . strtolower($object) . 'Config';

        // Call config setter if any.
        if (method_exists($this, $method)) {
            $this->$method($config);
        }

        return $config;
    }

    /**
     * Set the actor config.
     *
     * @param Library\ObjectConfig $config The actor config.
     * @return Library\ObjectConfig
     */
    protected function _actorConfig(Library\ObjectConfig $config)
    {
        $objectName = $this->getAuthor()->getName();
        $translate  = false;

        if (!$this->created_by)
        {
            $objectName = 'Guest user';
            $translate  = true;
        }
        elseif (!$this->_findActivityActor())
        {
            $objectName = 'Deleted user';
            $translate  = true;
        }

        $config->append([
            'type' => ['objectName' => 'user', 'object' => true],
            'id'         => $this->created_by,
            'url'        => 'option=com_users&task=user.edit&id=' . $this->created_by,
            'objectName' => $objectName,
            'translate'  => $translate,
            'find'       => 'actor'
        ]);
    }

    /**
     * Set the object config.
     *
     * @param Library\ObjectConfig $config The object config.
     */
    protected function _objectConfig(Library\ObjectConfig $config)
    {
        $config->append([
            'id'         => $this->row,
            'objectName' => $this->title,
            'type'       => ['objectName' => $this->name, 'object' => true],
            'url'        => 'option=com_' . $this->package . '&view=' . $this->name . '&id=' . $this->row,
            'attributes' => ['class' => ['object']],
            'find'       => 'object',
            'translate'  => false
        ]);
    }

    /**
     * Set the generator config.
     *
     * @param Library\ObjectConfig $config The generator config.
     */
    protected function _generatorConfig(Library\ObjectConfig $config)
    {
        $config->append(['objectName' => 'com_activities', 'type' => ['objectName' =>'component', 'object' => true]]);
    }

    /**
     * Set the generator config.
     *
     * @param Library\ObjectConfig $config The generator config.
     */
    protected function _providerConfig(Library\ObjectConfig $config)
    {
        $config->append(['objectName' => 'com_activities', 'type' => ['objectName' => 'component', 'object' => true]]);
    }

    /**
     * Set the action config.
     *
     * @param Library\ObjectConfig $config The action config.
     */
    protected function _actionConfig(Library\ObjectConfig $config)
    {
        $config->append(['objectName' => $this->status]);
    }

    /**
     * Get the activity object signature.
     *
     * @return string The signature.
     */
    protected function _getObjectSignature()
    {
        return sprintf('%s.%s.%s', $this->package, $this->name, $this->row);
    }

    /**
     * Get the activity actor signature.
     *
     * @return string The signature.
     */
    protected function _getActorSignature()
    {
        return sprintf('users.user.%s', $this->created_by);
    }

    /**
     * Find an activity object.
     *
     * @param string $label The object label.
     * @return bool True if found, false otherwise.
     */
    protected function _findObject($label)
    {
        $result    = false;
        $signature = null;

        $method = sprintf('_get%sSignature', ucfirst($label));

        if (method_exists($this, $method)) {
            $signature = $this->$method();
        }

        if (is_null($signature) || !isset(self::$_find_results[$signature]))
        {
            $method = '_findActivity' . ucfirst($label);

            if (method_exists($this, $method)) {
                $result = (bool) $this->$method();
            }

            if ($signature) {
                self::$_find_results[$signature] = $result;
            }
        }
        else $result = self::$_find_results[$signature];

        return $result;
    }

    /**
     * Finds the activity object.
     *
     * This method may be overridden for activities persisting objects on storage systems other than local database
     * tables.
     *
     * @return boolean True if found, false otherwise.
     */
    protected function _findActivityObject()
    {
        $db     = $this->getTable()->getAdapter();
        $table  = $this->_object_table;
        $column = $this->_object_column;

        $query = $this->getObject('database')->getQuery('select');
        $query->columns('COUNT(*)')->table($table)->where($column . ' = :value')
              ->bind(['value' => $this->row]);

        // Need to catch exceptions here as table may not longer exist.
        try {
            $result = $db->select($query, Library\Database::FETCH_FIELD);
        } catch (\Exception $e) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Finds the activity actor.
     *
     * @return boolean True if found, false otherwise.
     */
    protected function _findActivityActor()
    {
        $user = $this->getObject('user.provider')->fetch($this->created_by);

        return is_null($user) ? false : true;
    }

    /**
     * Route getter.
     *
     * @param string $url The URL to route.
     * @return Library\HttpUrl The routed URL object.
     */
    protected function _getRoute($url)
    {
        if (!is_string($url)) throw new \InvalidArgumentException('The URL must be a query string');

        return $this->getObject('lib:dispatcher.router.route', ['url' => ['query' => $url]]);
    }
}
