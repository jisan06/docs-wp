<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Attachments Template Helper
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class TemplateHelperAttachments extends Library\TemplateHelperAbstract
{
    public function manage($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        if ($entity = $config->entity)
        {
            $table = $entity->getTable();
            $config->append(['table' => $table->getBase(), 'row' => $entity->id]);
        }

        $config->append([
            'id'       => 'attachments-manage',
            'value'    => [],
            'callback' => 'attachmentsCallback',
            'multiple' => true,
            'text'     => $this->getObject('translator')->translate('Manage'),
            'attribs'  => [
                'data-k-modal' => htmlentities(json_encode(['mainClass' => 'koowa_dialog_modal koowa_dialog_modal--halfheight'])),
            ]
        ])->append([
            'link' => $this->getTemplate()->route('view=attachments&layout=manage&tmpl=koowa&table=' .
                                                  urlencode($config->table) . '&row=' . urlencode($config->row) .
                                                  '&callback=' . urlencode($config->callback))
        ]);

        $html = '<span class="input-group-btn">';
        $html .= sprintf('<a id="%s" class="btn mfp-iframe" %s href="%s">%s</a>', $config->id, $config->attribs, $config->link, $config->text);
        $html .= '</span>';

        $html .= $this->getTemplate()->createHelper('behavior')->modal();

        return $html;
    }
}