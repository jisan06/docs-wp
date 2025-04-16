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
 * Used to set hide_empty model state on categories model
 */
class ControllerBehaviorHideable extends Library\ControllerBehaviorAbstract
{
    protected $_actions = ['add_category', 'edit_category', 'delete_document', 'delete_category',  'upload_document'];

	public function isSupported()
	{
		return !$this->getMixer()->getUser()->isAdmin();
	}

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['priority' => Library\CommandHandlerInterface::PRIORITY_LOW]);

        parent::_initialize($config);
    }

    protected function _beforeBrowse(Library\ControllerContextInterface $context)
    {
        $result = $this->getObject('com:easydoc.model.categories')
                       ->permission($this->_actions)
                       ->user($this->getMixer()->getUser()->getId())
                       ->strict(false)
                       ->allowed();

        if (!$result)
        {
            $this->getView()->hide_empty = true; // Make the view aware

            $model = $this->getModel();

            if (!$model->getState()->isUnique()) {
                $model->hide_empty(true); // Only set if current state is not unique for avoiding 404s
            }
        }
    }
}
