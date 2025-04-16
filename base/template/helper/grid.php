<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class TemplateHelperGrid extends Library\TemplateHelperGrid
{
    public static $usersCache = [];
    public static $usergroupsCache = [];

    /**
     * Render an state field
     *
     * @param 	array $config An optional array with configuration options
     * @return string Html
     */
    public function state($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'entity'  		=> null,
            'field'		=> 'enabled',
            'clickable'  => true
        ]);
        
        if (!($config->entity instanceof ModelEntityDocument)) {
            return parent::publish($config);
        }

        $config->append([
            'data'		=> [$config->field => $config->entity->{$config->field}]
        ]);

        $entity     = $config->entity;
        $translator = $this->getObject('translator');
        $dateHelper = $this->createHelper('date');

        // Enabled, but pending
        if ($entity->status === 'pending')
        {
            $access = 0;
            $group  = $translator->translate('Pending');
            $date   = $dateHelper->humanize(['date' => $entity->publish_on]);
            $tip    = $translator->translate('Will be published {date}, click to unpublish item', [
                          'date' => $date]);
            $class  = 'k-table__item--state-pending';
        }
        // Enabled, but expired
        else if ($entity->status === 'expired')
        {
            $access = 0;
            $group  = $translator->translate('Expired');
            $date   = $dateHelper->humanize(['date' => $entity->unpublish_on]);
            $tip    = $translator->translate('Expired {on}, click to unpublish item', ['on' => $date]);
            $class  = 'k-table__item--state-expired';
        }
        elseif ($entity->status === 'unpublished')
        {
            $access = 1;
            $group  = $translator->translate('Unpublished');
            $tip    = $translator->translate('Publish item');
            $class  = 'k-table__item--state-unpublished';
        }
        else
        {
            $access = 0;
            $group  = $translator->translate('Published');
            $tip    = $translator->translate('Unpublish item');
            $class  = 'k-table__item--state-published';
        }

        $config->data->{$config->field} = $access;

        $tooltip = '';

        if ($config->clickable)
        {
            $data    = htmlentities(\EasyDocLabs\WP::wp_json_encode($config->data->toArray()));
            $tooltip = 'data-k-tooltip=\'{"container":".k-ui-container","delay":{"show":500,"hide":50}}\'
            style="cursor: pointer"
            data-action="edit" 
            data-data="'.$data.'" 
            data-original-title="'.$tip.'"';
        }

        $html = '<span class="k-table__item--state '.$class.'" '.$tooltip.'>'.$group.'</span>';
        $html .= $this->createHelper('behavior')->tooltip();

        return $html;
    }

    public function document_category($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $translator = $this->getObject('translator');

        $entity = $config->entity;

        $url = $this->getObject('lib:dispatcher.router.route')->setQuery('view=category&id=' . $entity->easydoc_category_id);
        $tip = $translator->translate('Edit {title}', ['title' => $entity->category_title]);

        return '<a data-k-tooltip=\'{"container":".k-ui-container","delay":{"show":500,"hide":50}}\' data-original-title="'.$tip.'" href="' . $url . '" >' . Library\StringEscaper::escape($entity->category_title) . '</a>';
    }

    public function access($config = [])
    {
        $config = new Library\ObjectConfig($config);

        if (!$config->entity) {
            throw new \RuntimeException('Entity is missing');
        }

        $translator = $this->getObject('translator');

        $entity = $config->entity;

        if ($entity instanceof ModelEntityDocument)
        {
            $category = $entity->category;
            $action = 'view_document';
        }
        else
        {
            $category = $entity;
            $action = 'view_category';
        }

        $access = [];

        if (!$category->isNew())
        {
            $referrer = urlencode($this->getTemplate()->getParameters()->url->toString(Library\HttpUrl::FULL, false));

            $permissions = $category->getPermission()->computed;

            if (!empty($permissions))
            {
                $model = $this->getObject('com:easydoc.model.usergroups');

                if (isset($permissions['users'][$action]))
                {
                    foreach ($permissions['users'][$action] as $user)
                    {
                        if (!isset(static::$usersCache[$user])) {
                            static::$usersCache[$user] = WP::get_user_by('ID', $user);
                        }

                        $user = static::$usersCache[$user];

                        $url = WP::admin_url('/user-edit.php?user_id=' . $user->get('ID') . '&wp_http_referrer=' . $referrer);

                        $access[] = sprintf('<span class="access_user"></span><a href="%s">%s</a></span>', $url, htmlspecialchars($user->get('user_login')));
                    }
                }

                if (!empty($permissions['usergroups'][$action]))
                {
                    $key = json_encode($permissions['usergroups'][$action]);

                    if (!isset(static::$usergroupsCache[$key])) {
                        static::$usergroupsCache[$key] = $model->id($permissions['usergroups'][$action])->fetch();
                    }

                    $usergroups = static::$usergroupsCache[$key];

                    $fixed_groups = ModelEntityUsergroup::getFixed();

                    foreach ($permissions['usergroups'][$action] as $usergroup)
                    {
                        if (!in_array($usergroup, array_keys($fixed_groups)))
                        {
                            $usergroup = $usergroups->find(['id' => $usergroup]);

                            if (!is_null($usergroup))
                            {
                                $url      = $this->getObject('lib:dispatcher.router.route')
                                                 ->setQuery('page=easydoc-usergroups&component=easydoc&view=usergroup&id=' .
                                                            $usergroup->id);
                                $access[] = sprintf('<span class="access_usergroup"><a href="%s">%s</a></span>', $url, htmlspecialchars($usergroup->name));
                            }
                        }
                        else $access[] = $translator->translate($fixed_groups[$usergroup]);
                    }
                }
            }
            else $access[] = $translator->translate('Private');
        }

        return implode(',', $access);
    }
}
