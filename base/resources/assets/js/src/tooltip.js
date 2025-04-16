/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

class EasyDocTooltip
{
    constructor(options)
    {
        this.options = EasyDoc.append({}, {
            enabled: true,
            message: 'This is a tooltip!',
            selector: '.tooltippable',
            direction: 'n'
        }, options);

        this.class = 'tooltipped-' + this.options.direction;

        this.container = EasyDoc.type(this.options.selector) === 'string' ? document.querySelector(this.options.selector) : this.options.selector;

        this.enabled = this.options.enabled;

        if (this.options.handler) {
            this.options.handler.call(this);
        }
    }

    static getInstance(options)
    {
        let instance = false;

        if (!EasyDoc.isMobile()) {
            instance = new EasyDocTooltip(options);
        }

        return instance;
    }

    clear()
    {
        this.container.classList.remove('tooltipped', this.class);
        this.container.removeAttribute('aria-label');

        return this;
    }

    show(message = null)
    {
        if (this.enabled)
        {
            this.container.classList.add('tooltipped', this.class);
            this.container.setAttribute('aria-label', message === null ? this.options.message : message);
        }

        return this;
    }

    enable()
    {
        this.enabled = true;
        return this;
    }

    disable()
    {
        this.enabled = false;

        // Also clear the container
        return this.clear();
    }
}