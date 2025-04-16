<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('behavior.jquery'); ?>
<?= helper('behavior.opengraph', array(
    'entity' => $document
)) ?>

<ktml:style src="assets://base/css/icon.css" />
<ktml:style src="assets://easydoc/css/bootstrap.css" />

<ktml:script src="assets://easydoc/js/bootstrap.js" />
<script data-inline>
    document.documentElement.className += ' kbs-ns-global kbs-ns-local kbs-h-100';
    document.body.className += ' kbs-d-flex kbs-flex-column kbs-h-100 kbs-m-0';
</script>

<?
$size_extension = [];

if ($document->storage_type == 'file') {
    $size_extension[] = escape(strtoupper($document->extension));
}

if ($document->size) {
    $size_extension[] = helper('string.humanize_filesize', ['size' => $document->size]);
}

$class = ' k-icon--size-default'.strlen($document->extension) ? ' k-icon-type-'.$document->extension : ' k-icon-type-remote';

$icon = '<span class="k-icon-document-'.$document->icon.' '.$class.'" aria-hidden="true"></span><span class="k-visually-hidden"><?= translate($icon); ?></span>';

$hits = null;
if ($document->hits)
{
    $hits = object('translator')->choose(['{number} download', '{number} downloads'], $document->hits, ['number' => $document->hits]);
    $hits = '<span class="detail-label">' . $hits . '</span>';
    $hits .= '<meta itemprop="interactionCount" content="UserDownloads:'.$document->hits.'">';
}
?>

<!-- Bootstrap row -->
<div class="kbs-container-fluid">

    <nav class="kbs-navbar kbs-navbar-expand-md kbs-navbar-light kbs-bg-light kbs-fixed-top">
    <span class="kbs-navbar-brand ">
        <span class="k-icon-document-'<?= $document->icon.' '.$class ?>" aria-hidden="true"></span><span class="k-visually-hidden"><?= translate($icon); ?></span>

        <?= $document->title ?>
    </span>
        <button class="kbs-btn kbs-ml-auto kbs-my-0 kbs-btn-primary kbs-my-2 kbs-my-sm-0" type="submit">Download</button>
    </nav>

    <div class="kbs-row"  style="padding-top: 56px;height: calc(100vh); ">
        <div class="kbs-col kbs-p-0">

            <object data="<?= route($document, 'layout=file') ?>" type="application/pdf"
                    width="100%" height="100%" style="min-width: 100%"
            ></object>

        </div>
        <div class="kbs-d-none kbs-d-md-block kbs-col-2 kbs-p-0"
             >
            <div class="kbs-list-group">
                <div class="kbs-list-group-item">
                    <h6><?= translate('Details') ?></h6>
                    <p class="kbs-mb-1">
                        <?= implode(', ',$size_extension ) ?>
                    </p>
                </div>
                <div class="kbs-list-group-item">
                    <h6><?= translate('Published on') ?></h6>
                    <p class="kbs-mb-1">
                        <time itemprop="datePublished" datetime="<?= $document->publish_date ?>">
                            <?= helper('date.format', ['date' => $document->publish_date]); ?>
                        </time>
                    </p>
                </div>
                <div href="#" class="kbs-list-group-item">
                    <h6><?= translate('Downloads') ?></h6>
                    <p class="kbs-mb-1">
                        <?= $hits ?>
                    </p>
                </div>
            </div>
        </div>


    </div>
</div>