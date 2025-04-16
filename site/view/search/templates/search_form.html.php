<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<script>
    kQuery(function($) {
        $('.k-js-search-reset').click(function(event) {
            event.preventDefault();

            var button = $(this),
                form   = button[0].form;

            $('.k-filters')
                .find('input:not(:checkbox), textarea').val('').end()
                .find('select').val(null).trigger('change');

            $(form).append($('<input type="hidden" />').val('1')
                .attr('name', 'reset'));

            form.submit();
        });
    });
</script>

<div class="well well-small k-filters k-filters--toggleable">
    <input class="k-checkbox-dropdown-toggle" id="k-checkbox-dropdown-toggle" type="checkbox" checked>
    <label class="k-checkbox-dropdown-label" for="k-checkbox-dropdown-toggle"><?= translate('Search for documents'); ?></label>
    <div class="k-checkbox-dropdown-content">
        <div class="form-group">
            <label for="search">
                <?= translate('Find by title or description') ?>
            </label>
            <input
                id="search"
                class="form-control input-block-level"
                type="search"
                name="<?= isset($filter_group) ? $filter_group . '[search]' : 'dmsearch' ?>"
                value="<?= escape($filter->search) ?>" />
        </div>

        <div class="form-group">
            <label class="control-label"><?= translate('Search in') ?></label>
            <?= helper('listbox.optionlist', [
                'name'     => isset($filter_group) ? $filter_group . '[search_contents]' : 'dmsearch_contents',
                'select2'  => true,
                'selected' => $filter->search_contents,
                'options'  => [
                    ['value' => 0, 'label' => translate('Document title and description')],
                    ['value' => 1, 'label' => translate('Document contents, title, and description')],
                ]
            ]); ?>
        </div>

        <? // Temp JS until new UI arrived. Basically we're adding old Bootstrap 2.3.2 classes to the datepickers; ?>
        <script>
            kQuery(document).ready(function() {
                kQuery('.easydoc-search-date').find('.k-input-group').addClass('input-append').find('input').addClass('input-block-level');
            });
        </script>

        <div class="form-group" style="padding-top: 5px">
            <button class="btn btn-lg k-js-search-submit" type="submit"><?= translate('Search') ?></button>

            <button class="btn btn-link k-js-search-reset"><?= translate('Reset') ?></button>
        </div>
    </div>
</div>