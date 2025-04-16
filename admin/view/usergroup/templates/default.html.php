<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load') ?>

<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>

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
                            <div class="k-container__full">

                                <fieldset>

                                    <div class="k-form-group">
                                        <label for="easydoc_usergroup_name"><?= translate('Name') ?></label>
                                        <input required
                                               class="k-form-control"
                                               id="easydoc_usergroup_name"
                                               type="text"
                                               name="name"
                                               maxlength="255"
                                               placeholder="<?= translate('Name') ?>"
                                               value="<?= escape($usergroup->name); ?>" />
                                    </div>

                                    <div class="k-form-group">
                                        <label for="easydoc_usergroup_description"><?= translate('Description') ?></label>
                                        <textarea class="k-form-control"
                                        id="easydoc_usergroup_description"
                                        name="description"
                                        rows="3"><?= escape($usergroup->description) ?></textarea>
                                    </div>

                                    <div class="k-form-group">
                                        <label for="easydoc_usergroup_users"><?= translate('Users') ?></label>
                                        <?= helper('listbox.users', ['name' => 'users', 'attribs' => ['multiple' => true, 'id' => 'easydoc_usergroup_users'], 'selected' => $usergroup->users]) ?>
                                    </div>

                                </fieldset>

                            </div><!-- .k-container__full -->

                        </div><!-- .k-container -->

                    </form><!-- .k-component -->

                </div><!-- .k-component-wrapper -->

        </div><!-- .k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
