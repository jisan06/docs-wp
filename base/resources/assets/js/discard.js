/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(() => {

    let discardRequired = true;
    let discardInProgress = false;
    const disableDiscard = () => {
        discardRequired = false;
        return true;
    };
	const enableDiscard = () => {
		discardRequired = true;
        return true;
	};
    const discard = function () {
        if (!discardRequired || discardInProgress || !navigator.sendBeacon) return;

        discardInProgress = true;

        var formData = new FormData();
        formData.append('_action', 'cancel');

        navigator.sendBeacon(document.querySelector('.k-js-form-controller').getAttribute('action'), formData);

        window.location = document.getElementById('command-discard').getAttribute('data-referrer');

        return false;
    };

    window.onbeforeunload = window.onunload = function (event) {
        discard();
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('command-discard').addEventListener('click', (event) => {
            event.preventDefault();
            discard();
        });

        kQuery('.k-js-form-controller').on('k:beforeApply', disableDiscard)
            .on('k:beforeDiscard', discard)
            .on('k:beforeSave', disableDiscard)
            .on('k:beforeSave2new', disableDiscard);

		kQuery('.k-js-form-controller').on('k:invalid-form-data', enableDiscard);
    });
})();
