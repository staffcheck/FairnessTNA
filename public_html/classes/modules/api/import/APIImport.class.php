<?php
/**********************************************************************************
 * This file is part of "FairnessTNA", a Payroll and Time Management program.
 * FairnessTNA is copyright 2013-2017 Aydan Coskun (aydan.ayfer.coskun@gmail.com)
 * others. For full attribution and copyrights details see the COPYRIGHT file.
 *
 * FairnessTNA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation, either version 3 of the License, or (at you option )
 * any later version.
 *
 * FairnessTNA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *********************************************************************************/


/**
 * @package API\Import
 */
class APIImport extends APIFactory
{
    public $import_obj = null;

    public function __construct()
    {
        parent::__construct(); //Make sure parent constructor is always called.

        //When APIImport()->getImportObjects() is called directly, there won't be a main class to call.
        if (isset($this->main_class) and $this->main_class != '') {
            $this->import_obj = new $this->main_class;
            $this->import_obj->company_id = $this->getCurrentCompanyObject()->getID();
            $this->import_obj->user_id = $this->getCurrentUserObject()->getID();
            Debug::Text('Setting main class: ' . $this->main_class . ' Company ID: ' . $this->import_obj->company_id, __FILE__, __LINE__, __METHOD__, 10);
        } else {
            Debug::Text('NOT Setting main class... Company ID: ' . $this->getCurrentCompanyObject()->getID(), __FILE__, __LINE__, __METHOD__, 10);
        }

        return true;
    }

    public function getImportObjects()
    {
        $retarr = array();

        if ($this->getPermissionObject()->Check('user', 'add') and ($this->getPermissionObject()->Check('user', 'edit') or $this->getPermissionObject()->Check('user', 'edit_child'))) {
            $retarr['-1010-user'] = TTi18n::getText('Employees');
        }
        if ($this->getPermissionObject()->Check('user', 'edit_bank') and $this->getPermissionObject()->Check('user', 'edit_child_bank')) {
            $retarr['-1015-bank_account'] = TTi18n::getText('Employee Bank Accounts');
        }
        if ($this->getPermissionObject()->Check('branch', 'add') and $this->getPermissionObject()->Check('branch', 'edit')) {
            $retarr['-1020-branch'] = TTi18n::getText('Branches');
        }
        if ($this->getPermissionObject()->Check('department', 'add') and $this->getPermissionObject()->Check('department', 'edit')) {
            $retarr['-1030-department'] = TTi18n::getText('Departments');
        }
        if ($this->getPermissionObject()->Check('wage', 'add') and ($this->getPermissionObject()->Check('wage', 'edit') or $this->getPermissionObject()->Check('wage', 'edit_child'))) {
            $retarr['-1050-userwage'] = TTi18n::getText('Employee Wages');
        }
        if ($this->getPermissionObject()->Check('pay_period_schedule', 'add') and $this->getPermissionObject()->Check('pay_period_schedule', 'edit')) {
            $retarr['-1060-payperiod'] = TTi18n::getText('Pay Periods');
        }
        if ($this->getPermissionObject()->Check('pay_stub_amendment', 'add') and $this->getPermissionObject()->Check('pay_stub_amendment', 'edit')) {
            $retarr['-1200-paystubamendment'] = TTi18n::getText('Pay Stub Amendments');
        }
        if ($this->getPermissionObject()->Check('accrual', 'add') and ($this->getPermissionObject()->Check('accrual', 'edit') or $this->getPermissionObject()->Check('accrual', 'edit_child'))) {
            $retarr['-1300-accrual'] = TTi18n::getText('Accruals');
        }

        return $this->returnHandler($retarr);
    }

    public function generateColumnMap()
    {
        if ($this->getImportObject()->getRawDataFromFile() == false) {
            $this->returnFileValidationError();
        }

        return $this->returnHandler($this->getImportObject()->generateColumnMap());
    }

    public function getImportObject()
    {
        return $this->import_obj;
    }

    public function returnFileValidationError()
    {
        //Make sure we return a complete validation error to be displayed to the user.
        $validator_obj = new Validator();
        $validator_stats = array('total_records' => 1, 'valid_records' => 0);

        $validator_obj->isTrue('file', false, TTi18n::getText('Please upload file again'));

        $validator = array();
        $validator[0] = $validator_obj->getErrorsArray();
        return $this->returnHandler(false, 'IMPORT_FILE', TTi18n::getText('INVALID DATA'), $validator, $validator_stats);
    }

    public function mergeColumnMap($saved_column_map)
    {
        if ($this->getImportObject()->getRawDataFromFile() == false) {
            $this->returnFileValidationError();
        }

        return $this->returnHandler($this->getImportObject()->mergeColumnMap($saved_column_map));
    }

    public function getRawData($limit = null)
    {
        if (!is_object($this->getImportObject()) or $this->getImportObject()->getRawDataFromFile() == false) {
            $this->returnFileValidationError();
        }

        return $this->returnHandler($this->getImportObject()->getRawData($limit));
    }

    public function setRawData($data)
    {
        return $this->returnHandler($this->getImportObject()->saveRawDataToFile($data));
    }

    public function getParsedData()
    {
        return $this->returnHandler($this->getParsedData());
    }

    public function Import($column_map, $import_options = array(), $validate_only = false)
    {
        if ($this->getImportObject()->getRawDataFromFile() == false) {
            return $this->returnFileValidationError();
        }

        if ($this->getImportObject()->setColumnMap($column_map) == false) {
            return $this->returnHandler(false);
        }

        if (is_array($import_options) and $this->getImportObject()->setImportOptions($import_options) == false) {
            return $this->returnHandler(false);
        }

        if (Misc::isSystemLoadValid() == false) { //Check system load as the user could ask to calculate decades worth at a time.
            Debug::Text('ERROR: System load exceeded, preventing new imports from starting...', __FILE__, __LINE__, __METHOD__, 10);
            return $this->returnHandler(false);
        }

        //Force this while testing.
        //Force this while testing.
        //Force this while testing.
        //$validate_only = TRUE;

        $this->getImportObject()->setAMFMessageId($this->getAMFMessageID()); //This must be set *after* the all constructor functions are called.
        return $this->getImportObject()->Process($validate_only); //Don't need return handler here as a API function is called anyways.
    }
}
