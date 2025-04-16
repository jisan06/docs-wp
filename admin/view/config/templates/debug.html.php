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


<?= helper('behavior.jquery'); ?>
<?= helper('behavior.keepalive'); ?>

<script>
    kQuery(function($)
    {
        $('#sync-roles-users').click(function(e)
        {
            e.preventDefault();

            var link = $(this);

            link.attr('disabled', 'disabled');

            var getButton = function (message, type = 'success') {
                return $('<div class="k-alert k-alert--' + type + '"><button class="k-alert__close k-js-alert-close" type="button" title="Close" onclick="kQuery(this).parent().remove()"></button>' + message + '</div>');
            }

           $.ajax(link.attr('href'), {
                method: 'POST',
                data: {
                    reset: 1,
                    _action: 'sync',
                },
                success: function()
                {
                    link.removeAttr('disabled');
                    $('#sync-roles-users_message').append(getButton('Re-sync was successful ...'));
                },
                error: function()
                {
                    link.removeAttr('disabled');
                    $('#sync-roles-users_message').append(getButton('Something went wrong with the re-sync ...', 'danger'));
                }
            });
        });
    });
</script>


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

                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Pages'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <ul>
                                                <? foreach ($pages as $post): ?>
                                        <li>
                                            <a href="<?= $post->link ?>"
                                               target="_blank"
                                               >
                                                <?= $post->post_title; ?><br />
                                            </a>
                                            <small><pre><?= var_export($post->permalink, true); ?></pre></small>
                                        </li>
                                    <?  endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset class="k-form-block">
                                    <div class="k-form-block__header">
                                        <?= translate('Connect'); ?>
                                    </div>
                                    <div class="k-form-block__content">
                                        <? if (!$connect['error']): ?>
                                            <ul>
                                                <li>Is local: <?= (int)$connect['local']; ?></li>
                                                <li>Is supported: <?= (int)$connect['supported']; ?></li>
                                                <li>Referer header: <?= $connect['site']; ?></li>
                                                <li>Token: <input class="k-form-control" value="<?= $connect['token']; ?>"></li>
                                                <li>Route: <input class="k-form-control" value="<?= $connect['route']; ?>"></li>
                                            </ul>

                                            <h4>Test results:</h4>
                                            <pre id="connect-test-results" style="white-space: pre-wrap;"></pre>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', async () => {
                                                    let body;
                                                    try {
                                                        const response = await fetch('<?= $connect['route']; ?>');
                                                        body = await response.text();
                                                    } catch (err) {
                                                        body = err.toString();
                                                    }

                                                    document.getElementById('connect-test-results').innerHTML = body;
                                                });
                                            </script>
                                        <? else: ?>
                                            <?= $connect['error'] ?>
                                        <? endif ?>
                                    </div>
                                </fieldset>

                                <fieldset class="k-form-block">
                                    <div class="k-form-block__header">
                                        <?= translate('License'); ?>
                                    </div>
                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <label>Site key</label>
                                            <input type="text"
                                                   class="k-form-control"
                                                   name="site_key"
                                                   value="<?= $license->getSiteKey() ?>" />
                                        </div>

                                        <div class="k-form-group">
                                            <label>API key</label>
                                            <input type="text"
                                                   class="k-form-control"
                                                   name="api_key"
                                                   value="<?= $license->getApiKey() ?>" />
                                        </div>

                                        <div class="k-form-group">
                                            <label>Public key</label>
                                            <input type="text"
                                                   class="k-form-control"
                                                   name="public_key"
                                                   value="<?= $license->getPublicKey() ?>" />
                                        </div>

                                        <div class="k-form-group">
                                            <label>License</label>
                                            <input type="text"
                                                   class="k-form-control"
                                                   name="license"
                                                   value="<?= $license->getLicense() ?>" />
                                            <li><strong>Load error:</strong> <pre><?= $license_error ?: 'none' ?></pre></li>
                                            <li><strong>License decoded:</strong> <pre><?= $license_claims ?></pre></li>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Permalinks'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <pre>

                                            <?php
                                            $wp_rewrite = \EasyDocLabs\WP::global('wp_rewrite');

                                            echo '<h4>Permalink structure</h4>';
                                            echo var_export( $wp_rewrite->permalink_structure, true );

                                            echo '<h4>Page permastruct</h4>';
                                            echo var_export( $wp_rewrite->get_page_permastruct(), true );

                                            echo '<h4>Rewrite rules</h4>';
                                            echo var_export( $wp_rewrite->wp_rewrite_rules(), true );

                                            ?>
                                            </pre>
                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset class="k-form-block">
                                    <div class="k-form-block__header">
                                        <?= translate('Options'); ?>
                                    </div>
                                    <div class="k-form-block__content">
                                        <? if ($wp_options): ?>
                                        <ul>
                                            <? foreach ($wp_options as $option): ?>
                                                
                                                <h4><?= $option->option_name; ?> <small>ID: <?= $option->option_id; ?>, Autoload: <?= $option->autoload; ?></small></h4>
                                                <pre><?= (\EasyDocLabs\WP::is_serialized($option->option_value) ? var_export( unserialize($option->option_value), true) : $option->option_value); ?></pre>
                                                <hr>
                                            <? endforeach; ?>
                                        </ul>
                                        <? endif ?>
                                    </div>
                                </fieldset>
                            </div><!-- .k-container__main -->

                            <!-- Other information -->
                            <div class="k-container__sub">

                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('General'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <ul>
                                                <li><a href="<?= route('view=config&layout=debug_phpinfo') ?>">PHP info</a></li>
                                                <li>Document count: <?= $document_count ?></li>
                                                <li>Category count: <?= $category_count ?></li>
                                                <li>Tag count: <?= $tag_count ?></li>
                                                <li>User count: <?= $user_count ?></li>
                                                <li>Folder count: <?= $folder_count ?></li>
                                                <li>File count: <?= $file_count ?></li>
                                                <li>Pending scan count: <?= $scan_count ?>
                                                    <small>
                                                    </small>
                                                </li>
                                                <li><a href="<?= route('view=scans&format=json') ?>" target="_blank">View scans</a></li>
                                                <li><a href="<?= route('view=containers&format=json&routed=1') ?>" target="_blank">View file containers</a></li>
                                                <li><a href="<?= route('view=permissions&format=json') ?>" target="_blank">View permissions</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Scheduler'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <p>
                                                <a class="k-button k-button--default"
                                                        href="<?= route('view=config&layout=debug_scheduler_log') ?>">Check Scheduler log</a>
                                            </p>
                                            <ul>
                                                <li>Last run: <?= $scheduler_metadata ? $scheduler_metadata->last_run : 'none' ?></li>
                                                <li>Sleep until: <?= $scheduler_metadata ? $scheduler_metadata->sleep_until : 'none' ?></li>
                                            </ul>
                                            <h4>Jobs</h4>
                                            <ul>
                                                <? foreach ($jobs as $job): ?>
                                                    <li>
                                                        <?= $job->id; ?> (<?= $job->frequency; ?>)<br />
                                                        <small>last run on: <?= $job->modified_on; ?></small><br />
                                                        <small>completed on: <?= $job->completed_on; ?></small><br />
                                                        <small>state:</small><br />
                                                        <pre><?= $job->state ?></pre>
                                                    </li>
                                                <? endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Permissions'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <p>
                                                <a id="sync-roles-users" class="k-button k-button--default"
                                                   href="<?= route('view=users') ?>"><?= translate('Force groups users re-sync') ?></a>
                                            </p>
                                            <span class="k-form-info">
                                                <?= translate('Deletes internal groups (roles) to users relations and re-syncs them') ?>
                                            </span>
                                            <div id="sync-roles-users_message"></div>
                                        </div>

                                    </div>
                                </fieldset>

                                <fieldset class="k-form-block">

                                    <div class="k-form-block__header">
                                        <?= translate('Trigger reinstall'); ?>
                                    </div>

                                    <div class="k-form-block__content">

                                        <div class="k-form-group">
                                            <p>
                                                <a id="sync-roles-users" class="k-button k-button--default"
                                                   href="<?= route('reinstall=1') ?>"><?= translate('Trigger reinstall') ?></a>
                                            </p>
                                            <span class="k-form-info">
                                                <?= translate('Runs the install logic again') ?>
                                            </span>
                                        </div>

                                    </div>
                                </fieldset>
                            </div><!-- .k-container__sub -->

                        </div><!-- .k-container -->

                        <input type="hidden" name="_context" value="debug"/>

                    </form><!-- .k-component -->

                </div><!-- .k-component-wrapper -->

        </div><!-- .k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
