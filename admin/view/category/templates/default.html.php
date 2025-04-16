<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Loading necessary Markup, CSS and JS ?>
<?= helper('ui.load') ?>

<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.vue', ['entity' => $category]); ?>

<ktml:script src="assets://easydoc/js/discard.js" />

<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Toolbar -->
            <ktml:toolbar type="actionbar">

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <form class="k-component k-js-component k-js-form-controller" action="" method="post">

                    <!-- Container -->
                    <div class="k-container">

                        <!-- Main information -->
                        <div class="k-container__main">

                            <fieldset>

                                <div class="k-form-group">
                                    <div class="k-input-group k-input-group--large">
                                        <?= helper('behavior.icon', array(
                                            'name'  => 'parameters[icon]',
                                            'id' => 'params_icon',
                                            'value' => $category->getParameters()->get('icon', 'folder'),
                                            'link'  => route('option=com_easydoc&view=files&layout=select&container=easydoc-icons&types[]=image')
                                        ))?>
                                        <input required
                                               id="easydoc_form_title"
                                               class="k-form-control"
                                               type="text"
                                               name="title"
                                               maxlength="255"
                                               value="<?= escape($category->title) ?>"/>
                                    </div>
                                </div>

                                <div class="k-form-group">
                                    <div class="k-input-group k-input-group--small">
                                        <label class="k-input-group__addon" for="easydoc_slug_input"><?= translate('URL slug') ?></label>
                                        <input type="text" name="slug" id="easydoc_slug_input" class="k-form-control" maxlength="255" placeholder="<?= translate('Will be created automatically') ?>" value=" <?= $category->slug ?>" />
                                    </div>
                                </div>

                                <div class="k-form-group">
                                    <label><?= translate('Parent Category') ?></label>
                                    <?= helper('listbox.categories', [
                                        'deselect'    => $category_deselect,
                                        'permissions' => ['add_category', 'view_category'],
                                        'filter'      => $category_filter,
                                        'name'        => 'parent_id',
                                        'attribs'     => ['id' => 'category'],
                                        'selected'    => $parent ? $parent->id : null,
                                        'ignore'      => $ignored_parents
                                    ]) ?>
                                </div>


                                <div class="k-tabs-container">
                                    <div class="k-tabs-wrapper">
                                        <ul class="k-tabs">
                                            <li class="k-is-active">
                                                <a href="#description" data-k-toggle="tab"><?= translate('Description') ?></a>
                                            </li>
                                            <li>
                                                <a href="#permissions" data-k-toggle="tab"><?= translate('Permissions') ?></a>
                                            </li>
                                            <? if (!$category->isNew()): ?>
                                                <li>
                                                    <a href="#notifications" data-k-toggle="tab"><?= translate('Notifications') ?></a>
                                                </li>
                                            <? endif ?>
                                        </ul>
                                    </div>
                                    <div class="k-tabs-content">

                                        <div id="description" class="k-tab k-is-active">
                                            <?= import('com:easydoc/editor/field.html', ['content' => $category->description]); ?>
                                        </div>

                                        <div id="permissions" class="k-tab">
                                            <?= import('permissions.html') ?>
                                        </div>

                                        <? if (!$category->isNew()): ?>
                                            <div id="notifications" class="k-tab">
                                                <?= import('notifications.html') ?>
                                            </div>
                                        <? endif ?>
                                    </div>
                                </div>


                            </fieldset>

                        </div><!-- .k-container__main -->


                        <!-- Other information -->
                        <div class="k-container__sub">

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Publishing') ?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <label><?= translate('Status'); ?></label>
                                        <?= helper('select.booleanlist', [
                                            'name' => 'enabled',
                                            'selected' => $category->enabled,
                                            'true' => 'Published',
                                            'false' => 'Unpublished'
                                        ]); ?>
                                    </div>

                                    <div class="k-form-group">
                                        <label><?= translate('Date'); ?></label>
                                        <?= helper('behavior.calendar', [
                                            'name' => 'created_on',
                                            'id' => 'created_on',
                                            'value' => $category->created_on,
                                            'format' => '%Y-%m-%d %H:%M:%S',
                                            'filter' => 'user_utc'
                                        ])?>
                                    </div>

                                    <div class="k-form-group">
                                        <label><?= translate('Owner'); ?></label>
                                        <?= helper('listbox.users', [
                                            'name' => 'created_by',
                                            'selected' => $category->created_by ? $category->created_by : object('user')->getId(),
                                            'deselect' => false,
                                            'attribs' => ['class' => 'input-block-level select2-users-listbox'],
                                            'select2' => true,
                                            'select2_options' => ['element' => '.select2-users-listbox']
                                        ]) ?>
                                    </div>

                                </div>

                            </fieldset>

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Featured image') ?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <?= helper('behavior.thumbnail', [
                                            'entity' => $category
                                        ]) ?>
                                    </div>

                                </div>

                            </fieldset>

                        </div><!-- .k-container__sub -->

                    </div><!-- .k-container -->

                    <input type="hidden" name="automatic_folder" value="1" />

                </form><!-- .k-component -->

            </div><!-- .k-component-wrapper -->

        </div><!-- .k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
