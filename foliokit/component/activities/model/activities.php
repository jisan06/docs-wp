<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activities Model.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ModelActivities extends Library\ModelDatabase
{
    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $state = $this->getState();

        $state->insert('application', 'cmd')
              ->insert('type', 'cmd')
              ->insert('package', 'cmd')
              ->insert('name', 'cmd')
              ->insert('action', 'cmd')
              ->insert('row', 'string')
              ->insert('user', 'cmd')
              ->insert('start_date', 'date')
              ->insert('end_date', 'date')
              ->insert('day_range', 'int')
              ->insert('ip', 'ip');

        $state->remove('direction')->insert('direction', 'word', 'desc');

        // Force ordering by created_on
        $state->sort = 'created_on';
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['behaviors' => ['searchable']]);
        parent::_initialize($config);
    }

    /**
     * Builds WHERE clause for the query.
     *
     * @param Library\DatabaseQueryInterface $query
     */
    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($state->application) {
            $query->where('tbl.application = :application')->bind(['application' => $state->application]);
        }

        if ($state->type) {
            $query->where('tbl.type = :type')->bind(['type' => $state->type]);
        }

        if ($state->package) {
            $query->where('tbl.package IN :package')->bind(['package' => (array) $state->package]);
        }

        if ($state->name) {
            $query->where('tbl.name = :name')->bind(['name' => $state->name]);
        }

        if ($state->action) {
            $query->where('tbl.action IN (:action)')->bind(['action' => $state->action]);
        }

        if ($state->row) {
            $query->where('tbl.row IN (:row)')->bind(['row' => $state->row]);
        }

        if ($state->start_date && $state->start_date != '0000-00-00')
        {
            $start_date = $this->getObject('lib:date', ['date' => $state->start_date]);

            $query->where('DATE(tbl.created_on) >= :start')->bind(['start' => $start_date->format('Y-m-d')]);

            if (is_numeric($state->day_range)) {
                $query->where('DATE(tbl.created_on) <= :range_start')->bind(['range_start' => $start_date->modify(sprintf('+%d days', $state->day_range))->format('Y-m-d')]);
            }
        }

        if ($state->end_date && $state->end_date != '0000-00-00')
        {
            $end_date  = $this->getObject('lib:date', ['date' => $state->end_date]);

            $query->where('DATE(tbl.created_on) <= :end')->bind(['end' => $end_date->format('Y-m-d')]);

            if (is_numeric($state->day_range)) {
                $query->where('DATE(tbl.created_on) >= :range_end')->bind(['range_end' => $end_date->modify(sprintf('-%d days', $state->day_range))->format('Y-m-d')]);
            }
        }

        if (is_numeric($state->user)) {
            $query->where('tbl.created_by = :created_by')->bind(['created_by' => $state->user]);
        }

        if ($ip = $state->ip) {
            $query->where('tbl.ip IN (:ip)')->bind(['ip' => $state->ip]);
        }
    }
}
