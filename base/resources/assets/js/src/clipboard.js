/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

class EasyDocClipboard
{
    constructor(options)
    {
        this.options = EasyDoc.append({}, {
            selector: '.clipboardable',
            message: 'Copied!',
            tooltip: {
                handler: function()
                {
                    this.container.addEventListener('mouseover', function() {
                        this.show();
                    }.bind(this));

                    this.container.addEventListener('mouseleave', function() {
                        this.clear();
                    }.bind(this));
                },
                message: "Copy to clipboard"
            }
        }, options);

        this.container = typeof this.options.selector === 'string' ? document.querySelector(this.options.selector) : this.options.selector;

        this.clipboard = new ClipboardJS(this.options.selector);

        this.options.tooltip.selector = this.container;

        this.tooltip = EasyDocTooltip.getInstance(this.options.tooltip);

        if (this.tooltip)
        {
            this.clipboard.on('success', function () {
                this.tooltip.show(this.options.message);
            }.bind(this));
        }
    }

    getTooltip()
    {
        return this.tooltip;
    }

    getClipboard()
    {
        return this.clipboard;
    }
}