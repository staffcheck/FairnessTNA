<?php
/*********************************************************************************
 * This file is part of "Fairness", a Payroll and Time Management program.
 * Fairness is Copyright 2013 Aydan Coskun (aydan.ayfer.coskun@gmail.com)
 * Portions of this software are Copyright of T i m e T r e x Software Inc.
 * Fairness is a fork of "T i m e T r e x Workforce Management" Software.
 *
 * Fairness is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation, either version 3 of the License, or (at you option )
 * any later version.
 *
 * Fairness is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 ********************************************************************************/


/**
 * @package Modules\Import
 */
class ImportBranch extends Import
{
    public $class_name = 'APIBranch';

    public function _getFactoryOptions($name, $parent = null)
    {
        $retval = null;
        switch ($name) {
            case 'columns':
                $bf = TTNew('BranchFactory');
                $retval = $bf->getOptions('columns');
                break;
            case 'import_options':
                $retval = array(
                    '-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
                );
                break;
            case 'parse_hint':
                $retval = array();
                break;
        }

        return $retval;
    }


    public function _preParseRow($row_number, $raw_row)
    {
        $retval = $this->getObject()->stripReturnHandler($this->getObject()->getBranchDefaultData());
        $retval['manual_id'] += $row_number; //Auto increment manual_id automatically.

        return $retval;
    }

    public function _import($validate_only)
    {
        return $this->getObject()->setBranch($this->getParsedData(), $validate_only);
    }

    //
    // Generic parser functions.
    //
    public function parse_status($input, $default_value = null, $parse_hint = null, $raw_row = null)
    {
        if (strtolower($input) == 'e'
            or strtolower($input) == 'enabled'
        ) {
            $retval = 10;
        } elseif (strtolower($input) == 'd'
            or strtolower($input) == 'disabled'
        ) {
            $retval = 20;
        } else {
            $retval = (int)$input;
        }

        return $retval;
    }
}
