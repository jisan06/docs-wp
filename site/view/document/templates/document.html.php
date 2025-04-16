<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? if ($document->isDocument()): ?>

    <?= import('com://site/easydoc/document/document_document.html') ?>

<? elseif ($document->isArchive()): ?>

    <?= import('com://site/easydoc/document/document_archive.html') ?>

<? elseif ($document->isImage()): ?>

    <?= import('com://site/easydoc/document/document_image.html') ?>

<? elseif ($document->isVideo()): ?>

    <?= import('com://site/easydoc/document/document_video.html') ?>

<? elseif ($document->isAudio()): ?>

    <?= import('com://site/easydoc/document/document_audio.html') ?>

<? elseif ($document->isExecutable()): ?>

    <?= import('com://site/easydoc/document/document_executable.html') ?>

<? else: ?>

    <?= import('com://site/easydoc/document/document_default.html') ?>

<? endif; ?>
