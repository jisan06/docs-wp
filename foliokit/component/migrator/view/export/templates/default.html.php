<?
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
defined('FOLIOKIT') or die; ?>

<?= helper('behavior.bootstrap', array('javascript' => true, 'css' => true)) ?>
<?= helper('ui.load', array('styles' => false)) ?>

<ktml:script src="assets://migrator/js/migrator.js" />
<ktml:script src="assets://migrator/js/export.js" />

<ktml:style src="assets://migrator/css/migrator.css" />

<script type="text/javascript">

    if (typeof Ait Theme ClubMigrator === 'undefined') {
        Ait Theme ClubMigrator = {};
    }

    Ait Theme ClubMigrator.base_url = '<?= route('component=migrator&view=export&format=json', true, false); ?>';
    Ait Theme ClubMigrator.base_url = '<?= route('component=easydoc&view=export&format=json', true, false); ?>';
    Ait Theme ClubMigrator.export_url = '<?= route('component=migrator&view=export&format=binary', true, false); ?>';
    Ait Theme ClubMigrator.export_url = '<?= route('component=easydoc&view=export&format=binary', true, false); ?>';
    Ait Theme ClubMigrator.exporters = <?= json_encode($exporters) ?>;
</script>
<script type="text/javascript">

</script>

<div class="k-migrator-container">
    <div class="migrator">
        <div class="migrator__header">
            <img class="ait_themes_logo" src="assets://migrator/img/ait_themes_logo_80px.png" alt="Ait Theme Club logo" />
            <?= translate('Easy Docs exporter') ?>
        </div>
        <div class="migrator__steps">
            <ul class="migrator__steps__list">
                <li class="migrator__steps__list__item item--active"><?= translate('Start') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Cleanup') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Export') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Packing') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Completed') ?></li>
            </ul>
        </div>
        <div class="migrator__wrapper migrator--step1">
            <h1><?= translate('Start export process') ?></h1>
            <?
            if (EasyDocLabs\Library\ObjectConfig::unbox($missing_dependencies)): ?>
                <div class="alert alert-error">
                    <h3><?= translate('Missing Requirements') ?></h3>
                    <ul>
                        <? foreach ($missing_dependencies as $key => $error): ?>
                            <li><?= $error; ?></li>
                        <? endforeach; ?>
                    </ul>
                </div>
            <? else: ?>
                <div class="migrator__content"
                    <?= count($labels) === 1 ? 'style="display: none"' : '' ?>
                    >
                    <p><?= translate('Please select the extension to export data from') ?></p>
                    <select id="extension">
                        <? foreach ($labels as $name => $label): ?>
                        <option value="<?= $name ?>"><?= $label; ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="migrator__content">
                    <p style="display:block;"><a id="export-btn" class="migrator_button" href="#">
                            <?= translate('Export') ?>
                        </a></p>
                </div>
                <div class="migrator__content">
                    <p>
                        <?= translate('If you run into any problems please let us know on our <a href="{url}">forums</a>.', array(
                            'url' => 'https://system.ait-themes.club/support'
                        )) ?>
                    </p>
                </div>
                <div class="migrator__content">
                    <p><a class="migrator_go_to" href="<?= $go_back; ?>">
                            <?= translate('Go back') ?></a></p>
                </div>
            <? endif; ?>
        </div>
        <div class="migrator__wrapper migrator--step2" style="display: none">
            <h1><?= translate('Preparing for export') ?></h1>
            <div id="message-container" class="migrator__content"></div>
            <div class="migrator_alert">
                <p><?= translate('Do not close this page or use the back button during export process!') ?></p>
            </div>
            <div class="migrator__content">
                <h3><?= translate('Cleaning up the export folder') ?></h3>
                <div class="progress progress-striped active">
                    <div class="bar" style="width: 0" id="progress-bar-cleanup"></div>
                </div>
            </div>
        </div>
        <div class="migrator__wrapper migrator--step3" style="display: none">
            <h1><?= translate('Exporting') ?></h1>
            <div id="message-container" class="migrator__content"></div>
            <div class="migrator_alert">
                <p><?= translate('Do not close this page or use the back button during export process!') ?></p>
            </div>
        </div>
        <div class="migrator__wrapper migrator--step4" style="display: none">
            <h1><?= translate('Preparing export package') ?></h1>
            <div class="migrator__content">
                <h3><?= translate('Generating export package for download') ?></h3>
                <div class="progress progress-striped active">
                    <div class="bar" style="width: 0" id="progress-bar-package"></div>
                </div>
            </div>
        </div>
        <div class="migrator__wrapper migrator--step5" style="display: none">
            <h1><?= translate('Export completed') ?></h1>
            <div class="migrator__content migrator_success">
                <?= translate('The export has been successfully completed! Your browser should automatically download the exported file now.
                You will be asked for this file in EasyDocLabs Importer.') ?>
            </div>
            <div class="migrator__content">
                <p>
                <?= translate('If you run into any problems please let us know on our <a {href}>forums</a>.', array(
                    'href' => 'href="https://system.ait-themes.club/support"'
                )) ?>
                </p>
            </div>
            <div class="migrator__content">
                <p><a class="migrator_button" href="<?= $go_back ?>">
                <?= translate('Go back') ?></a></p>
            </div>
        </div>
    </div>
</div>