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
 * @package Modules\Company
 */
class BranchListFactory extends BranchFactory implements IteratorAggregate
{
    public static function getByCompanyIdArray($company_id, $include_blank = true, $include_disabled = true)
    {
        $blf = new BranchListFactory();
        $blf->getByCompanyId($company_id);

        return $blf->getArrayByListFactory($blf, $include_blank, $include_disabled);
    }

    public function getByCompanyId($id, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        if ($order == null) {
            $order = array('status_id' => 'asc', 'name' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'id' => (int)$id,
        );


        $query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND deleted = 0';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getArrayByListFactory($lf, $include_blank = true, $include_disabled = true)
    {
        if (!is_object($lf)) {
            return false;
        }
        $list = array();
        if ($include_blank == true) {
            $list[0] = '--';
        }

        foreach ($lf as $obj) {
            if ($obj->getStatus() == 20) {
                $status = '(DISABLED) ';
            } else {
                $status = null;
            }

            if ($include_disabled == true or ($include_disabled == false and $obj->getStatus() == 10)) {
                $list[$obj->getID()] = $status . $obj->getName();
            }
        }

        if (empty($list) == false) {
            return $list;
        }

        return false;
    }

    public function getAll($limit = null, $page = null, $where = null, $order = null)
    {
        $query = '
					select	*
					from	' . $this->getTable() . '
					WHERE deleted = 0';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, null, $limit, $page);

        return $this;
    }

    public function getById($id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $this->rs = $this->getCache($id);
        if ($this->rs === false) {
            $ph = array(
                'id' => (int)$id,
            );

            $query = '
						select	*
						from	' . $this->getTable() . '
						where	id = ?
							AND deleted = 0';
            $query .= $this->getWhereSQL($where);
            $query .= $this->getSortSQL($order);

            $this->ExecuteSQL($query, $ph);

            $this->saveCache($this->rs, $id);
        }

        return $this;
    }

    public function getByCompanyIdAndStatus($company_id, $status_id, $order = null)
    {
        if ($company_id == '') {
            return false;
        }
        if ($status_id == '') {
            return false;
        }

        $ph = array(
            'company_id' => (int)$company_id,
            'status_id' => (int)$status_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	status_id = ?
						AND deleted = 0';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyIdAndLongitudeAndLatitude($company_id, $longitude, $latitude, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($order == null) {
            $order = array('longitude' => 'asc', 'latitude' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'company_id' => (int)$company_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ? ';

        //isset() returns false on NULL.
        $query .= $this->getWhereClauseSQL('longitude', $longitude, 'numeric', $ph);
        $query .= $this->getWhereClauseSQL('latitude', $latitude, 'numeric', $ph);
        $query .= '	AND deleted = 0';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getByIdAndCompanyId($id, $company_id, $order = null)
    {
        if ($id == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        $ph = array(
            'company_id' => (int)$company_id,
            'id' => (int)$id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	id = ?
						AND deleted = 0';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByManualIdAndCompanyId($id, $company_id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        $ph = array(
            'id' => (int)$this->Validator->stripNon32bitInteger($id),
            'company_id' => (int)$company_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	manual_id = ?
						AND company_id = ?
						AND deleted = 0';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyIDAndStatusAndDate($company_id, $status_id, $date = null, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($status_id == '') {
            return false;
        }

        if ($date == '') {
            $date = 0;
        }

        if ($order == null) {
            $order = array('a.id' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'company_id' => (int)$company_id,
            'status_id' => (int)$status_id,
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.status_id = ?
					';

        if (isset($date) and $date > 0) {
            //Append the same date twice for created and updated.
            $ph[] = $date;
            $ph[] = $date;
            $query .= ' AND ( a.created_date >= ? OR a.updated_date >= ? )';
        }

        $query .= ' AND ( a.deleted = 0 )';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getByCompanyIDAndStatusAndDateAndValidIDs($company_id, $status_id, $date = null, $valid_ids = array(), $limit = null, $page = null, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($status_id == '') {
            return false;
        }

        if ($date == '') {
            $date = 0;
        }

        if ($order == null) {
            $order = array('a.id' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'company_id' => (int)$company_id,
            'status_id' => (int)$status_id,
        );

        //Make sure we return distinct rows so there aren't duplicates.
        $query = '
					select	distinct a.*
					from	' . $this->getTable() . ' as a

					where	a.company_id = ?
						AND a.status_id = ?
						AND (
								1=1
							';

        if (isset($date) and $date > 0) {
            //Append the same date twice for created and updated.
            $ph[] = (int)$date;
            $ph[] = (int)$date;
            $query .= '		AND ( a.created_date >= ? OR a.updated_date >= ? ) ';
        }

        if (isset($valid_ids) and is_array($valid_ids) and count($valid_ids) > 0) {
            $query .= ' OR a.id in (' . $this->getListSQL($valid_ids, $ph, 'int') . ') ';
        }

        $query .= '	)
					AND ( a.deleted = 0 )';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getHighestManualIDByCompanyId($id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $ph = array(
            'id' => (int)$id,
            'id2' => $id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND id = ( select id
									from ' . $this->getTable() . '
									where company_id = ?
										AND manual_id IS NOT NULL
										AND deleted = 0
									ORDER BY manual_id DESC
									LIMIT 1
									)
						AND deleted = 0
					LIMIT 1
					';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($date == '') {
            return false;
        }

        $ph = array(
            'company_id' => (int)$company_id,
            'created_date' => $date,
            'updated_date' => $date,
            'deleted_date' => $date,
        );

        //INCLUDE Deleted rows in this query.
        $query = '
					select	*
					from	' . $this->getTable() . '
					where
							company_id = ?
						AND
							( created_date >=  ? OR updated_date >= ? OR ( deleted = 1 AND deleted_date >= ? ) )
					LIMIT 1
					';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);
        if ($this->getRecordCount() > 0) {
            Debug::text('Rows have been modified: ' . $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

            return true;
        }
        Debug::text('Rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);
        return false;
    }

    public function getAPISearchByCompanyIdAndArrayCriteria($company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if (!is_array($order)) {
            //Use Filter Data ordering if its set.
            if (isset($filter_data['sort_column']) and $filter_data['sort_order']) {
                $order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
            }
        }

        $additional_order_fields = array('status_id');

        $sort_column_aliases = array(
            'status' => 'status_id',
        );

        $order = $this->getColumnsFromAliases($order, $sort_column_aliases);
        if ($order == null) {
            $order = array('status_id' => 'asc', 'name' => 'asc');
            $strict = false;
        } else {
            //Always try to order by status first so INACTIVE employees go to the bottom.
            if (!isset($order['status_id'])) {
                $order = Misc::prependArray(array('status_id' => 'asc'), $order);
            }
            //Always sort by last name, first name after other columns
            if (!isset($order['name'])) {
                $order['name'] = 'asc';
            }
            $strict = true;
        }
        //Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
        //Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

        $uf = new UserFactory();

        $ph = array(
            'company_id' => (int)$company_id,
        );

        $query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?';

        $query .= (isset($filter_data['permission_children_ids'])) ? $this->getWhereClauseSQL('a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['id'])) ? $this->getWhereClauseSQL('a.id', $filter_data['id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['exclude_id'])) ? $this->getWhereClauseSQL('a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph) : null;

        if (isset($filter_data['status']) and !is_array($filter_data['status']) and trim($filter_data['status']) != '' and !isset($filter_data['status_id'])) {
            $filter_data['status_id'] = Option::getByFuzzyValue($filter_data['status'], $this->getOptions('status'));
        }
        $query .= (isset($filter_data['status_id'])) ? $this->getWhereClauseSQL('a.status_id', $filter_data['status_id'], 'numeric_list', $ph) : null;

        $query .= (isset($filter_data['name'])) ? $this->getWhereClauseSQL('a.name', $filter_data['name'], 'text_metaphone', $ph) : null;

        $query .= (isset($filter_data['country'])) ? $this->getWhereClauseSQL('a.country', $filter_data['country'], 'upper_text_list', $ph) : null;
        $query .= (isset($filter_data['province'])) ? $this->getWhereClauseSQL('a.province', $filter_data['province'], 'upper_text_list', $ph) : null;
        $query .= (isset($filter_data['city'])) ? $this->getWhereClauseSQL('a.city', $filter_data['city'], 'text', $ph) : null;
        $query .= (isset($filter_data['manual_id'])) ? $this->getWhereClauseSQL('a.manual_id', $filter_data['manual_id'], 'numeric', $ph) : null;
        $query .= (isset($filter_data['work_phone'])) ? $this->getWhereClauseSQL('a.work_phone', $filter_data['work_phone'], 'phone', $ph) : null;
        $query .= (isset($filter_data['fax_phone'])) ? $this->getWhereClauseSQL('a.work_phone', $filter_data['fax_phone'], 'phone', $ph) : null;
        $query .= (isset($filter_data['address1'])) ? $this->getWhereClauseSQL('a.address1', $filter_data['address1'], 'text', $ph) : null;
        $query .= (isset($filter_data['address2'])) ? $this->getWhereClauseSQL('a.address2', $filter_data['address2'], 'text', $ph) : null;
        $query .= (isset($filter_data['postal_code'])) ? $this->getWhereClauseSQL('a.postal_code', $filter_data['postal_code'], 'text', $ph) : null;

        $query .= (isset($filter_data['tag'])) ? $this->getWhereClauseSQL('a.id', array('company_id' => (int)$company_id, 'object_type_id' => 110, 'tag' => $filter_data['tag']), 'tag', $ph) : null;

        $query .= (isset($filter_data['created_date'])) ? $this->getWhereClauseSQL('a.created_date', $filter_data['created_date'], 'date_range', $ph) : null;
        $query .= (isset($filter_data['updated_date'])) ? $this->getWhereClauseSQL('a.updated_date', $filter_data['updated_date'], 'date_range', $ph) : null;

        /*
        $query .= ( isset($filter_data['created_by']) AND is_array($filter_data['created_by']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['created_by'], 'numeric_list', $ph ) : NULL;

        $query .= ( isset($filter_data['updated_by']) AND is_array($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( 'a.updated_by', $filter_data['updated_by'], 'numeric_list', $ph ) : NULL;

        if ( isset($filter_data['created_by']) AND !is_array($filter_data['created_by']) AND trim($filter_data['created_by']) != '' ) {
            $ph[] = $ph[] = $this->handleSQLSyntax(strtolower(trim($filter_data['created_by'])));
            $query	.=	' AND (lower(y.first_name) LIKE ? OR lower(y.last_name) LIKE ? ) ';
        }
        if ( isset($filter_data['updated_by']) AND !is_array($filter_data['updated_by']) AND trim($filter_data['updated_by']) != '' ) {
            $ph[] = $ph[] = $this->handleSQLSyntax(strtolower(trim($filter_data['updated_by'])));
            $query	.=	' AND (lower(z.first_name) LIKE ? OR lower(z.last_name) LIKE ? ) ';
        }
        */
        $query .= (isset($filter_data['created_by'])) ? $this->getWhereClauseSQL(array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph) : null;
        $query .= (isset($filter_data['updated_by'])) ? $this->getWhereClauseSQL(array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph) : null;

        $query .= ' AND a.deleted = 0 ';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict, $additional_order_fields);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }
}
