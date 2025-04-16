<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('behavior.alpine') ?>

<div class="k-js-filters k-dynamic-content-holder">
    <div data-filter data-title="<?= translate('Status'); ?>"
         data-count="<?= (!empty(parameters()->status) || parameters()->enabled === 0 || parameters()->enabled === '0') ? 1 : 0 ?>"
         x-data="{ selected: '' }"
         x-init="() => {
            select2 = kQuery($refs.select);
            select2.on('select2:select', (event) => { selected = event.target.value; });
            selected = select2.val();
            $watch('selected', (value) => { select2.val(value).trigger('change'); });
          }"
    >
        <?= helper('listbox.status', ['attribs' => ['x-ref' => 'select']]); ?>

        <input type="hidden" name="enabled" class="k-js-scopebar-clearable"
               :value="selected ? (['published', 'pending', 'expired'].includes(selected) ? '1' : '0') : ''">
        <input type="hidden" name="status" class="k-js-scopebar-clearable"
               :value="selected ? (['published', 'pending', 'expired'].includes(selected) ? selected : '') : ''">
    </div>
    <div data-filter data-title="<?= translate('Owner'); ?>"
         data-count="<?= !empty(parameters()->created_by) ? (is_int(parameters()->created_by) ?  1 :count(parameters()->created_by)) : 0 ?>"
    >
        <?= helper('listbox.users', [
            'name' => 'created_by'
        ]) ?>
    </div>
    <div data-filter data-title="<?= translate('Tags'); ?>"
         data-count="<?= !empty(parameters()->tag) ? count(parameters()->tag) : 0 ?>"
    >
        <?= helper('listbox.tags', [
            'name' => 'tag[]',
            'value' => 'slug',
            'selected' => parameters()->tag,
            'autocreate' => false
        ]);
        ?>
    </div>
    <div data-filter data-title="<?= translate('Date'); ?>"
        <?= parameters()->search_date || parameters()->day_range ? 'data-label="1"' : '' ?>
    >

        <div class="k-form-group" x-data="{selected: <?= \EasyDocLabs\WP::wp_json_encode(parameters()->day_range) ?>}">
            <label for="day_range"><?= translate('Within') ?></label>

            <input type="hidden" name="day_range" x-model="selected" class="k-js-scopebar-clearable">
            <div class="k-input-group">
                <div class="k-input-group__button">
                    <? foreach ([1,3,7,14,30] as $day): ?>
                    <button type="button" class="k-button k-button--default"
                            :class="{ 'k-is-active': selected == <?= $day ?> }"
                            @click="selected = <?= $day ?>"
                    ><?= $day ?></button>
                    <? endforeach; ?>
                </div>
                <input class="k-form-control k-js-scopebar-clearable" type="text" placeholder="…"
                       x-bind:value="[1,3,7,14,30].includes(selected) ? '' : selected"
                       x-on:input="selected = $event.target.value">
            </div>
        </div>

        <div class="k-form-group">
            <label for="search_date"><?= translate('days of') ?></label>

            <?= helper('behavior.calendar', [
                'name' => 'search_date',
                'id'   => 'search_date',
                'type' => 'date',
                'value' => parameters()->search_date
            ]) ?>
        </div>

    </div>
	<? if (object('user')->isAdmin()): ?>
		<div data-filter data-title="<?= translate('Access'); ?>"
			<?= (parameters()->access || parameters()->group_access) ? 'data-label="1"' : '' ?>
		>
			<label><?= translate('Users'); ?></label>
			<?= helper('listbox.users',  [
				'select2' => true,
				'name' => 'access'
			]); ?>

			<label><?= translate('Groups'); ?></label>
			<?= helper('listbox.usergroups',  [
				'select2' => true,
				'name' => 'group_access'
			]); ?>
		</div>
	<? endif ?>
</div>


<!-- Scopebar -->
<div class="k-scopebar k-js-scopebar">

    <!-- Scopebar filters -->
    <div class="k-scopebar__item k-scopebar__item--filters">

        <!-- Filters wrapper -->
        <div class="k-scopebar__filters-content">

            <!-- Filters -->
            <div class="k-scopebar__filters k-js-filter-container">

                <!-- Filter -->
                <div style="display: none;" class="k-scopebar__item--filter k-scopebar-dropdown k-js-filter-prototype k-js-dropdown">
                    <button type="button" class="k-scopebar-dropdown__button k-js-dropdown-button">
                        <span class="k-scopebar__item--filter__title k-js-dropdown-title"></span>
                        <span class="k-scopebar__item--filter__icon k-icon-chevron-bottom" aria-hidden="true"></span>
                        <span class="k-scopebar__item-label k-js-dropdown-label"></span>
                    </button>
                    <div class="k-scopebar-dropdown__body k-js-dropdown-body">
                        <div class="k-scopebar-dropdown__body__buttons">
                            <button type="button" class="k-button k-button--default k-js-clear-filter"><?= translate('Clear') ?></button>
                            <button type="button" class="k-button k-button--primary k-js-apply-filter"><?= translate('Apply filter') ?></button>
                        </div>
                    </div>
                </div>

            </div><!-- .k-scopebar__filters -->

        </div><!-- .k-scopebar__filters-content -->

    </div><!-- .k-scopebar__item--filters -->

    <!-- Search -->
    <div class="k-scopebar__item k-scopebar__item--search">
        <?= helper('grid.search', ['submit_on_clear' => true]) ?>
    </div><!-- .k-scopebar__item--search -->

</div><!-- .k-scopebar -->
