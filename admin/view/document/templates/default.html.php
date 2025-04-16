<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load') ?>

<ktml:script src="assets://easydoc/admin/js/document.js" />
<ktml:script src="assets://easydoc/js/discard.js" />
<ktml:script src="assets://easydoc/js/src/toolbox.js" />


<?= helper('behavior.alpine'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.vue', ['entity' => $document]); ?>

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
                                            'value' => $document->getParameters()->get('icon', 'default'),
                                            'link'  => route('option=com_easydoc&view=files&layout=select&container=easydoc-icons&types[]=image')
                                        ))?>
                                        <input required
                                               class="k-form-control"
                                               id="easydoc_form_title"
                                               type="text"
                                               name="title"
                                               maxlength="255"
                                               placeholder="<?= translate('Title') ?>"
                                               value="<?= escape($document->title); ?>" />
                                    </div>
                                </div>

                                <div class="k-form-group">
                                    <div class="k-input-group k-input-group--small">
                                        <label class="k-input-group__addon" for="easydoc_slug_input"><?= translate('URL slug') ?></label>
                                        <input type="text" name="slug" id="easydoc_slug_input" class="k-form-control" maxlength="255" placeholder="<?= translate('Will be created automatically') ?>" value=" <?= $document->slug ?>" />
                                    </div>
                                </div>
    
                                <div class="k-form-group">
                                    <label><?= translate('Category'); ?></label>
                                    <?= helper('com:easydoc.listbox.categories', [
                                        'permissions'      => ['upload_document'],
                                        'deselect'         => false,
                                        'required'         => true,
                                        'name'             => 'easydoc_category_id',
                                        'disable_if_empty' => true,
                                        'selected'         => $document->easydoc_category_id,
                                        'filter'           => $category_filter,
                                        'attribs'          => [
                                            'required' => true,
                                            'id'       => 'easydoc_category_id'
                                        ]
                                    ]) ?>
                                </div>

                                <?= import('default_field_file.html'); ?>

                                <div class="k-tabs-container">
                                    <div class="k-tabs-wrapper">
                                        <ul class="k-tabs">
                                            <li class="k-is-active">
                                                <a href="#description" data-k-toggle="tab"><?= translate('Description') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="k-tabs-content">
                                        <div id="description" class="k-tab k-is-active">
                                            <?= import('com:easydoc/editor/field.html', ['content' => $document->description]); ?>
                                        </div>
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

                                <div class="k-form-block__content"
                                     x-data="EasyDoc.StatusSwitcher()"
                                     x-init="init({status: '<?= $document->enabled ?>'})">
                                    <template x-if="status === '2' || status === '1'" >
                                        <input type="hidden" name="enabled" value="1" />
                                    </template>
                                    <template x-if="status === '0'" >
                                        <input type="hidden" name="enabled" value="0" />
                                    </template>

                                    <div class="k-form-group">
                                        <label><?= translate('Status'); ?></label>
                                        <?= helper('com://admin/easydoc.select.status', [
                                            'selected' => $document->enabled,
                                            'attribs'  => [
                                                'x-model' => 'status'
                                            ]
                                        ]) ?>
                                    </div>

                                    <div class="k-form-group" x-show="status === '2'">
                                        <label><?= translate('Start publishing on'); ?></label>
                                        <? $datetime = new \DateTime('now', new \DateTimeZone('UTC')) ?>
                                        <? $datetime->modify('-1 day'); ?>
                                        <?= helper('behavior.calendar', [
                                            'name' => 'publish_on',
                                            'id' => 'publish_on',
                                            'value' => $document->publish_on,
                                            'filter' => 'user_utc',
                                            'attribs' => [
                                                'x-ref' => 'publishOn'
                                            ]
                                        ])?>
                                    </div>

                                    <div class="k-form-group" x-show="status === '2'">
                                        <label><?= translate('Stop publishing on'); ?></label>
                                        <?= helper('behavior.calendar', [
                                            'name' => 'unpublish_on',
                                            'id' => 'unpublish_on',
                                            'value' => $document->unpublish_on,
                                            'filter' => 'user_utc',
                                            'attribs' => [
                                                'x-ref' => 'unpublishOn'
                                            ]
                                        ])?>
                                    </div>

                                    <div class="k-form-group">
                                        <label><?= translate('Date'); ?></label>
                                        <?= helper('behavior.calendar', [
                                            'name' => 'created_on',
                                            'id' => 'created_on',
                                            'value' => $document->created_on,
                                            'filter' => 'user_utc'
                                        ])?>
                                    </div>

                                    <? if ($show_owner_field): ?>

                                        <div class="k-form-group">
                                            <label><?= translate('Owner'); ?></label>
                                            <?= helper('listbox.users', [
                                                'name' => 'created_by',
                                                'selected' => $document->created_by ? $document->created_by : object('user')->getId(),
                                                'deselect' => false,
                                            ]) ?>
                                        </div>

                                    <? endif ?>
                                </div>




                            </fieldset>

                            <? if(empty($hide_tag_field)) : ?>
                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Tags') ?>
                                    </div>

                                    <div class="k-form-block__content">
                                        <div class="k-form-group">
                                            <?= helper('com:easydoc.listbox.tags', [
                                                'entity' => $document,
                                                'autocreate' => true
                                            ]) ?>
                                        </div>
                                    </div>
                                </fieldset>
                            <? endif ?>

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Featured image') ?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <?= helper('behavior.thumbnail', [
                                            'entity' => $document
                                        ]) ?>
                                    </div>

                                </div>

                            </fieldset>

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Audit') ?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <label><?= translate('Downloads'); ?></label>
                                        <div id="hits-container">
                                            <span><?= $document->hits; ?></span>

                                            <? if ($document->hits): ?>
                                                <small><a href="#"><?= translate('Reset'); ?></a></small>
                                            <? endif; ?>
                                        </div>
                                    </div>

                                    <? if ($document->modified_by): ?>
                                        <div class="k-form-group">
                                            <label><?= translate('Modified by'); ?></label>
                                            <p>
                                                <?= object('user.provider')->getUser($document->modified_by)->getName(); ?>
                                                <?= translate('on') ?>
                                                <?= helper('date.format', ['date' => $document->modified_on]); ?>
                                            </p>
                                        </div>
                                    <? endif; ?>

                                </div>

                            </fieldset>

                        </div><!-- .k-container__sub -->

                    </div><!-- .k-container -->

                </form><!-- .k-component -->

            </div><!-- .k-component-wrapper -->

        </div><!-- .k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
