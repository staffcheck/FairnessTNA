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
 * @package API\Request
 */
class APIRequest extends APIFactory
{
    protected $main_class = 'RequestFactory';

    public function __construct()
    {
        parent::__construct(); //Make sure parent constructor is always called.

        return true;
    }

    /**
     * Get default request data for creating new requestes.
     * @return array
     */
    public function getRequestDefaultData()
    {
        Debug::Text('Getting request default data...', __FILE__, __LINE__, __METHOD__, 10);
        $data = array(
            'date_stamp' => TTDate::getAPIDate('DATE', TTDate::getTime()),
        );

        return $this->returnHandler($data);
    }

    /**
     * Get hierarchy_level and hierarchy_control_ids for authorization list.
     * @param integer $object_type_id hierarchy object_type_id
     * @return array
     */
    public function getHierarchyLevelOptions($type_id)
    {
        $type_id = (array)$type_id;
        if (is_array($type_id) and count($type_id) > 0) {
            //If "ANY" is specified for the type_id, use all type_ids.
            if (in_array(-1, $type_id)) {
                $type_id = array_keys($this->getOptions('type'));
            }
            Debug::Arr($type_id, 'Type ID: ', __FILE__, __LINE__, __METHOD__, 10);

            $blf = TTnew('RequestListFactory');
            $object_type_id = $blf->getHierarchyTypeId($type_id);
            if (isset($object_type_id) and is_array($object_type_id)) {
                $hl = new APIHierarchyLevel();
                return $hl->getHierarchyLevelOptions($object_type_id);
            } else {
                Debug::Text('Invalid Request type ID!', __FILE__, __LINE__, __METHOD__, 10);
            }
        }

        return $this->returnHandler(false);
    }

    /**
     * Get options for dropdown boxes.
     * @param string $name Name of options to return, ie: 'columns', 'type', 'status'
     * @param mixed $parent Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
     * @return array
     */
    public function getOptions($name = false, $parent = null)
    {
        if ($name == 'columns'
            and (!$this->getPermissionObject()->Check('request', 'enabled')
                or !($this->getPermissionObject()->Check('request', 'view') or $this->getPermissionObject()->Check('request', 'view_child')))
        ) {
            $name = 'list_columns';
        }

        return parent::getOptions($name, $parent);
    }

    /**
     * @param string $format
     * @param null $data
     * @param bool $disable_paging
     * @return array|bool
     */
    public function exportRequest($format = 'csv', $data = null, $disable_paging = true)
    {
        $result = $this->stripReturnHandler($this->getRequest($data, $disable_paging));
        return $this->exportRecords($format, 'export_request', $result, ((isset($data['filter_columns'])) ? $data['filter_columns'] : null));
    }

    /**
     * Get request data for one or more requestes.
     * @param array $data filter data
     * @return array
     */
    public function getRequest($data = null, $disable_paging = false)
    {
        if (!$this->getPermissionObject()->Check('request', 'enabled')
            or !($this->getPermissionObject()->Check('request', 'view') or $this->getPermissionObject()->Check('request', 'view_own') or $this->getPermissionObject()->Check('request', 'view_child'))
        ) {
            return $this->getPermissionObject()->PermissionDenied();
        }

        $data = $this->initializeFilterAndPager($data, $disable_paging);

        $blf = TTnew('RequestListFactory');

        //If type_id and hierarchy_level is passed, assume we are in the authorization view.
        if (isset($data['filter_data']['type_id']) and is_array($data['filter_data']['type_id']) and isset($data['filter_data']['hierarchy_level'])
            and ($this->getPermissionObject()->Check('authorization', 'enabled')
                and $this->getPermissionObject()->Check('authorization', 'view')
                and $this->getPermissionObject()->Check('request', 'authorize'))
        ) {

            //FIXME: If type_id = -1 (ANY) is used, it may show more requests then if type_id is specified to a specific ID.
            //This is because if the hierarchy objects are changed when pending requests exist, the ANY type_id will catch them and display them,
            //But if you filter on type_id = <specific value> as well a specific hierarchy level, it may exclude them.

            //If "ANY" is selected, use all type_ids.
            if (in_array(-1, $data['filter_data']['type_id'])) {
                $data['filter_data']['type_id'] = array_keys($this->getOptions('type'));
            }

            $hllf = TTnew('HierarchyLevelListFactory');
            $hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID($this->getCurrentUserObject()->getId(), $blf->getHierarchyTypeId($data['filter_data']['type_id']));
            Debug::Arr($data['filter_data']['type_id'], 'Type ID: ', __FILE__, __LINE__, __METHOD__, 10);
            Debug::Arr($blf->getHierarchyTypeId($data['filter_data']['type_id']), 'Hierarchy Type ID: ', __FILE__, __LINE__, __METHOD__, 10);
            Debug::Arr($hierarchy_level_arr, 'Hierarchy Levels: ', __FILE__, __LINE__, __METHOD__, 10);

            $data['filter_data']['hierarchy_level_map'] = false;
            if (isset($data['filter_data']['hierarchy_level']) and isset($hierarchy_level_arr[$data['filter_data']['hierarchy_level']])) {
                $data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[$data['filter_data']['hierarchy_level']];
            } elseif (isset($hierarchy_level_arr[1])) {
                $data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[1];
            }
            unset($hierarchy_level_arr);

            //Force other filter settings for authorization view.
            $data['filter_data']['authorized'] = array(0);
            $data['filter_data']['status_id'] = array(30);
        } else {
            Debug::Text('Not using authorization criteria...', __FILE__, __LINE__, __METHOD__, 10);
        }

        //Is this to too restrictive when authorizing requests, as they have to be in the permission hierarchy as well as the request hierarchy?
        $data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren('request', 'view');

        $blf->getAPISearchByCompanyIdAndArrayCriteria($this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort']);
        Debug::Text('Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
        if ($blf->getRecordCount() > 0) {
            $this->getProgressBarObject()->start($this->getAMFMessageID(), $blf->getRecordCount());

            $this->setPagerObject($blf);

            $retarr = array();
            foreach ($blf as $b_obj) {
                $retarr[] = $b_obj->getObjectAsArray($data['filter_columns'], $data['filter_data']['permission_children_ids']);

                $this->getProgressBarObject()->set($this->getAMFMessageID(), $blf->getCurrentRow());
            }

            $this->getProgressBarObject()->stop($this->getAMFMessageID());

            return $this->returnHandler($retarr);
        }

        return $this->returnHandler(true); //No records returned.
    }

    /**
     * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
     * @param array $data filter data
     * @return array
     */
    public function getCommonRequestData($data)
    {
        return Misc::arrayIntersectByRow($this->stripReturnHandler($this->getRequest($data, true)));
    }

    /**
     * Validate request data for one or more requestes.
     * @param array $data request data
     * @return array
     */
    public function validateRequest($data)
    {
        return $this->setRequest($data, true);
    }

    /**
     * Set request data for one or more requestes.
     * @param array $data request data
     * @return array
     */
    public function setRequest($data, $validate_only = false, $ignore_warning = true)
    {
        $validate_only = (bool)$validate_only;
        $ignore_warning = (bool)$ignore_warning;

        if (!is_array($data)) {
            return $this->returnHandler(false);
        }

        if (!$this->getPermissionObject()->Check('request', 'enabled')
            or !($this->getPermissionObject()->Check('request', 'edit') or $this->getPermissionObject()->Check('request', 'edit_own') or $this->getPermissionObject()->Check('request', 'edit_child') or $this->getPermissionObject()->Check('request', 'add'))
        ) {
            return $this->getPermissionObject()->PermissionDenied();
        }

        if ($validate_only == true) {
            Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
            $permission_children_ids = false;
        } else {
            //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
            $permission_children_ids = $this->getPermissionChildren();
        }

        extract($this->convertToMultipleRecords($data));
        Debug::Text('Received data for: ' . $total_records . ' Requests', __FILE__, __LINE__, __METHOD__, 10);
        Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0);
        $validator = $save_result = false;
        if (is_array($data) and $total_records > 0) {
            $this->getProgressBarObject()->start($this->getAMFMessageID(), $total_records);

            foreach ($data as $key => $row) {
                $primary_validator = $tertiary_validator = new Validator();
                $lf = TTnew('RequestListFactory');
                $lf->StartTransaction();
                if (isset($row['id']) and $row['id'] > 0) {
                    //Modifying existing object.
                    //Get request object, so we can only modify just changed data for specific records if needed.
                    $lf->getByIdAndCompanyId($row['id'], $this->getCurrentCompanyObject()->getId());
                    if ($lf->getRecordCount() == 1) {
                        //Object exists, check edit permissions
                        if (
                            $validate_only == true
                            or
                            (
                                $this->getPermissionObject()->Check('request', 'edit')
                                or ($this->getPermissionObject()->Check('request', 'edit_own') and $this->getPermissionObject()->isOwner($lf->getCurrent()->getCreatedBy()) === true)
                                or ($this->getPermissionObject()->Check('request', 'edit_child') and $this->getPermissionObject()->isChild($lf->getCurrent()->getUser(), $permission_children_ids) === true)
                            )
                        ) {
                            Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
                            $lf = $lf->getCurrent();
                            $row = array_merge($lf->getObjectAsArray(), $row);
                        } else {
                            $primary_validator->isTrue('permission', false, TTi18n::gettext('Edit permission denied'));
                        }
                    } else {
                        //Object doesn't exist.
                        $primary_validator->isTrue('id', false, TTi18n::gettext('Edit permission denied, record does not exist'));
                    }
                } else {
                    //Adding new object, check ADD permissions.
                    $primary_validator->isTrue('permission', $this->getPermissionObject()->Check('request', 'add'), TTi18n::gettext('Add permission denied'));

                    //Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
                    //so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
                    $row['id'] = $lf->getNextInsertId();
                }
                Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

                if ($validate_only == true) {
                    $lf->Validator->setValidateOnly($validate_only);
                }

                $is_valid = $primary_validator->isValid($ignore_warning);
                if ($is_valid == true) { //Check to see if all permission checks passed before trying to save data.
                    Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

                    $lf->setObjectFromArray($row);
                    //Save request_schedule here...
                    //Checking tertiary validity
                    if ($is_valid == true) {
                        $is_valid = $lf->isValid($ignore_warning);
                        if ($is_valid == true) {
                            Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
                            if ($validate_only == true) {
                                $save_result[$key] = true;
                            } else {
                                $save_result[$key] = $lf->Save(true, true);
                            }
                            $validator_stats['valid_records']++;
                        }
                    }
                }

                if ($is_valid == false) {
                    Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

                    $lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

                    $validator[$key] = $this->setValidationArray($primary_validator, $lf, $tertiary_validator);
                } elseif ($validate_only == true) {
                    $lf->FailTransaction();
                }

                $lf->CommitTransaction();

                $this->getProgressBarObject()->set($this->getAMFMessageID(), $key);
            }

            $this->getProgressBarObject()->stop($this->getAMFMessageID());

            return $this->handleRecordValidationResults($validator, $validator_stats, $key, $save_result);
        }

        return $this->returnHandler(false);
    }

    /**
     * Delete one or more requests.
     * @param array $data request data
     * @return array
     */
    public function deleteRequest($data)
    {
        if (is_numeric($data)) {
            $data = array($data);
        }

        if (!is_array($data)) {
            return $this->returnHandler(false);
        }

        if (!$this->getPermissionObject()->Check('request', 'enabled')
            or !($this->getPermissionObject()->Check('request', 'delete') or $this->getPermissionObject()->Check('request', 'delete_own') or $this->getPermissionObject()->Check('request', 'delete_child'))
        ) {
            return $this->getPermissionObject()->PermissionDenied();
        }

        Debug::Text('Received data for: ' . count($data) . ' Requests', __FILE__, __LINE__, __METHOD__, 10);
        Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

        $total_records = count($data);
        $validator = $save_result = false;
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0);
        if (is_array($data) and $total_records > 0) {
            $this->getProgressBarObject()->start($this->getAMFMessageID(), $total_records);

            foreach ($data as $key => $id) {
                $primary_validator = new Validator();
                $lf = TTnew('RequestListFactory');
                $lf->StartTransaction();
                if (is_numeric($id)) {
                    //Modifying existing object.
                    //Get request object, so we can only modify just changed data for specific records if needed.
                    $lf->getByIdAndCompanyId($id, $this->getCurrentCompanyObject()->getId());
                    if ($lf->getRecordCount() == 1) {
                        //Object exists, check edit permissions
                        if ($this->getPermissionObject()->Check('request', 'delete')
                            or ($this->getPermissionObject()->Check('request', 'delete_own') and $this->getPermissionObject()->isOwner($lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID()) === true)
                        ) {
                            Debug::Text('Record Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
                            $lf = $lf->getCurrent();
                        } else {
                            $primary_validator->isTrue('permission', false, TTi18n::gettext('Delete permission denied'));
                        }
                    } else {
                        //Object doesn't exist.
                        $primary_validator->isTrue('id', false, TTi18n::gettext('Delete permission denied, record does not exist'));
                    }
                } else {
                    $primary_validator->isTrue('id', false, TTi18n::gettext('Delete permission denied, record does not exist'));
                }

                //Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

                $is_valid = $primary_validator->isValid();
                if ($is_valid == true) { //Check to see if all permission checks passed before trying to save data.
                    Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
                    $lf->setDeleted(true);

                    $is_valid = $lf->isValid();
                    if ($is_valid == true) {
                        Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
                        $save_result[$key] = $lf->Save();
                        $validator_stats['valid_records']++;
                    }
                }

                if ($is_valid == false) {
                    Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

                    $lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

                    $validator[$key] = $this->setValidationArray($primary_validator, $lf);
                }

                $lf->CommitTransaction();

                $this->getProgressBarObject()->set($this->getAMFMessageID(), $key);
            }

            $this->getProgressBarObject()->stop($this->getAMFMessageID());

            return $this->handleRecordValidationResults($validator, $validator_stats, $key, $save_result);
        }

        return $this->returnHandler(false);
    }

    /**
     * Copy one or more requestes.
     * @param array $data request IDs
     * @return array
     */
    public function copyRequest($data)
    {
        if (is_numeric($data)) {
            $data = array($data);
        }

        if (!is_array($data)) {
            return $this->returnHandler(false);
        }

        Debug::Text('Received data for: ' . count($data) . ' Requests', __FILE__, __LINE__, __METHOD__, 10);
        Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

        $src_rows = $this->stripReturnHandler($this->getRequest(array('filter_data' => array('id' => $data)), true));
        if (is_array($src_rows) and count($src_rows) > 0) {
            Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
            foreach ($src_rows as $key => $row) {
                unset($src_rows[$key]['id'], $src_rows[$key]['manual_id']); //Clear fields that can't be copied
                $src_rows[$key]['name'] = Misc::generateCopyName($row['name']); //Generate unique name
            }
            //Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

            return $this->setRequest($src_rows); //Save copied rows
        }

        return $this->returnHandler(false);
    }
}
