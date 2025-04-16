<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
defined('FOLIOKIT') or die; ?>

<ktml:style src="assets://migrator/css/migrator.css" />

<div class="k-ui-namespace">
    <div class="migrator">
        <div class="migrator__header">
            <img class="ait_themes_logo" src="assets://migrator/img/ait_themes_logo_80px.png" alt="Ait Theme Club logo" />
            <?= translate('Easy Docs exporter') ?>
        </div>
        <div class="migrator__wrapper migrator--step1">
            <div class="alert alert-error">
                <h3><?= translate('A supported extension was not found in the system') ?></h3>
            </div>
            <div class="migrator__content">
                <p style="display:block;"><a class="migrator_button"
                     href="<?= $go_back ?>"><?= translate('Go back') ?></a></p>
            </div>
            <div class="migrator__content">
                <p>
                    <?= translate('If you run into any problems please let us know on our <a href="{url}">forums</a>.', array(
                        'url' => 'https://system.ait-themes.club/support'
                    )) ?>
                </p>
            </div>
        </div>
    </div>
</div>
