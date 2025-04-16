<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ModelEntityConfig extends Library\ModelEntityAbstract implements Library\ObjectMultiton
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setProperties($config->values);

        if (!empty($config->auto_load)) {
            $this->load();
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'auto_load' => true,
            'values'    => [
                'automatic_humanized_titles'  => true,
                'automatic_category_creation' => false,
                'automatic_document_creation' => false
            ]
        ]);

        parent::_initialize($config);
    }

    public function isNew()
    {
        return false;
    }

    public function isLockable()
    {
        return false;
    }

    public function getFilesContainer()
    {
        return $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch();
    }

    public function clearCache()
    {
        $this->getObject('com:easydoc.database.table.documents')->regenerateRoutes();

        $this->getObject('com:easydoc.database.table.categories')->clearCache();
        $this->getObject('com:easydoc.database.table.documents')->clearCache();

        $behavior = $this->getObject('com:easydoc.controller.behavior.syncable');
        $behavior->syncFolders();
        $behavior->syncFiles();

        $this->getObject('easydoc.users')->clearPermissions();

		WP::flushRewriteRules(true);
    }

    public function load()
    {
        $this->setProperties(\EasyDocLabs\WP::get_option( 'easydoc_options', []));

        $container  = $this->getFilesContainer();
        $parameters = $container->getParameters();

        $this->document_path = $container->path;

        foreach (['thumbnails', 'allowed_extensions', 'maximum_size', 'allowed_mimetypes'] as $key) {
            $this->$key = $parameters->$key;
        }

        if (!isset($this->_data['permissions'])) $this->_data['permissions'] = [];

        $this->debug = \EasyDocLabs\WP::isDebug();
        $this->debug_lang = \EasyDocLabs\WP::isLanguageDebug();
        $this->debug_wordpress = \EasyDocLabs\WP\DEBUG;
        $this->debug_folio = \EasyDocLabs\WP::isFolioDebug();
    }

    /**
     * Copied from JForm
     *
     * @param array $rules
     * @return array
     */
    protected function _filterAccessRules($rules)
    {
        $return = [];

        foreach ((array) $rules as $action => $ids)
        {
            // Build the rules array.
            $return[$action] = [];
            foreach ($ids as $id => $p)
            {
                if ($p !== '') {
                    $return[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
                }
            }
        }

        return $return;
    }

    public function save()
    {
        // System variables shoulnd't be saved
        foreach (['option', '_action', 'format', 'layout', 'task'] as $var)
        {
            unset($this->_data[$var]);
        }

        if (!empty($this->_data['allowed_extensions']) && is_string($this->_data['allowed_extensions'])) {
            $this->allowed_extensions = explode(',', $this->_data['allowed_extensions']);
        }

        // Auto-set allowed mimetypes based on the extensions
        if (!empty($this->allowed_extensions))
        {
            $mimetypes = $this->getObject('com:files.model.mimetypes')
                    ->extension($this->allowed_extensions)
                    ->fetch();

            $results = [];
            foreach ($mimetypes as $mimetype) {
                $results[] = $mimetype->mimetype;
            }

            $this->allowed_mimetypes = array_values(array_unique(array_merge($this->allowed_mimetypes, $results)));
        }

        // If the document path changed try to move the files to their new location
        $container = $this->getFilesContainer();

        // These are all going to be saved into com_files
        $data = [];
        foreach (['thumbnails', 'allowed_extensions', 'maximum_size', 'allowed_mimetypes', 'document_path'] as $var)
        {
            $value = $this->$var;

            if ($var === 'thumbnails') {
                $value = (bool) $value;
            }

            if (!empty($value) || ($value === false || $value === 0 || $value === '0')) {
                $data[$var] = $value;
            }
            unset($this->_data[$var]);
        }
        unset($data['document_path']);

        $container->getParameters()->merge($data);
        $container->save();

        if (isset($this->debug)) {
            \EasyDocLabs\WP::setDebug($this->debug);

            unset($this->_data['debug']);
        }

        if (isset($this->debug_lang)) {
            \EasyDocLabs\WP::setLanguageDebug($this->debug_lang);

            unset($this->_data['debug_lang']);
        }

        if (isset($this->api_key) || isset($this->public_key) || isset($this->site_key) || isset($this->license)) {
            try {
                /** @var \EasyDocLabs\EasyDoc\License $license */
                $license = \Foliokit::getObject('license');

                if (isset($this->api_key)) {
                    $license->setApiKey($this->api_key);
                    unset($this->api_key);
                }

                if (isset($this->public_key)) {
                    $license->setPublicKey($this->public_key);
                    unset($this->public_key);
                }

                if (isset($this->site_key)) {
                    $license->setSiteKey($this->site_key);
                    unset($this->site_key);
                }

                if (isset($this->license)) {
                    $license->setLicense($this->license);
                    unset($this->license);
                }
            }
            catch (\Exception $e) {
                if (\Foliokit::isDebug()) throw $e;
            }
        }

        foreach ($this->getConfig()->values as $property => $value) {
            if (is_bool($value) && isset($this->_data[$property]) && !is_bool($this->_data[$property])) $this->_data[$property] = (bool) $this->_data[$property];
        }

        return \EasyDocLabs\WP::update_option('easydoc_options', $this->getProperties());
    }

    public function getProperty($column)
    {
        $result = parent::getProperty($column);

        if (in_array($column, ['allowed_extensions', 'allowed_mimetypes']))
        {
            if ($result instanceof Library\ObjectConfigInterface) {
                return $result->toArray();
            }
            elseif (!is_array($result)) {
                return [];
            }
        }

        // Disable thumbnails if these cannot be generated.
        if ($column == 'thumbnails' && $result) {
            $result = $this->thumbnailsAvailable();
        }

        return $result;
    }

    /**
     * Utility function for checking if the server can generate thumbnails.
     *
     * @return bool True if it can, false otherwise.
     */
    public function thumbnailsAvailable()
    {
        return extension_loaded('gd')/* || extension_loaded('imagick')*/;
    }

    /**
     * Utility function for checking if Ait Theme Club Connect is supported
     *
     * @return bool True if it can, false otherwise.
     */
    public function connectAvailable()
    {
        $connect = $this->getObject('connect');

        return $connect->isSupported() && version_compare($connect->getVersion(), '2.0.0', '>=');
    }

	/**
	 * Performs a user permission check agains default permissions
	 *
	 * @param string $action
	 * @param User $user
	 * @return bool|null True is the user can execute the action, false otherwise, null if additional ownership validation is required
	 */
    protected function _canExecute($action, User $user)
    {
		if (!$user->isAdmin())
		{
			$result = false;

			if (isset($this->_data['permissions'][$action]))
			{
				$groups = $this->_data['permissions'][$action];

				if (in_array(ModelEntityUsergroup::FIXED['public']['id'], $groups)) {
					$result = true;
				}

				if (!$result)
				{
					if (in_array(ModelEntityUsergroup::FIXED['registered']['id'], $groups)) {
						$result = $user->isAuthentic();
					}
				}

				if (!$result)
				{
					$result = (bool) array_intersect($groups, $user->getGroups());

					if (!$result)
					{
						if (in_array(ModelEntityUsergroup::FIXED['owner']['id'], $groups)) {
							$result = null;
						}
					}
				}
			}
		}
        else $result = true;

        return $result;
    }

    public function __call($method, $arguments)
    {
        if (strpos($method, 'can') === 0)
        {
            $action = strtolower(substr(Library\StringInflector::underscore($method), 4));

            $user = $arguments[0] ?? $this->getObject('user');

            $result = $this->_canExecute($action, $user);
        }
        else $result = parent::__call($method, $arguments);

        return $result;
    }
}
