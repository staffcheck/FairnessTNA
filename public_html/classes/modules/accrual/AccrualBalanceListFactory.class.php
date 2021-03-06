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
 * @package Modules\Accrual
 */
class AccrualBalanceListFactory extends AccrualBalanceFactory implements IteratorAggregate
{
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

        return $this;
    }

    public function getByCompanyId($company_id, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        $uf = new UserFactory();

        $ph = array(
            'company_id' => (int)$company_id,
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByIdAndCompanyId($id, $company_id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        $uf = new UserFactory();

        $ph = array(
            'id' => (int)$id,
            'company_id' => (int)$company_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserIdAndCompanyId($user_id, $company_id, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($user_id == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        $additional_order_fields = array('a.balance', 'c.name');
        if ($order == null) {
            $order = array('c.name' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $uf = new UserFactory();
        $apaf = new AccrualPolicyAccountFactory();
        $af = new AccrualFactory();

        $ph = array(
            'company_id' => (int)$company_id,
        );

        $query = '
					select
							_ADODB_COUNT
							a.*
							_ADODB_COUNT
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b,
							' . $apaf->getTable() . ' as c
					where	a.user_id = b.id
						AND a.accrual_policy_account_id = c.id
						AND b.company_id = ?
						AND a.user_id in (' . $this->getListSQL($user_id, $ph, 'int') . ')
						AND EXISTS ( select 1 from ' . $af->getTable() . ' as af WHERE af.accrual_policy_account_id = a.accrual_policy_account_id AND a.user_id = af.user_id AND af.deleted = 0 )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict, $additional_order_fields);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_id, $company_id, $enable_pay_stub_balance_display, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($user_id == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        $additional_order_fields = array('a.balance', 'c.name');
        if ($order == null) {
            $order = array('c.name' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $uf = new UserFactory();
        $apaf = new AccrualPolicyAccountFactory();

        $ph = array(
            'company_id' => (int)$company_id,
            'enable_pay_stub_balance_display' => (int)$enable_pay_stub_balance_display,
        );

        $query = '
					select	a.*,
							c.name as name
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b,
							' . $apaf->getTable() . ' as c
					where	a.user_id = b.id
						AND a.accrual_policy_account_id = c.id
						AND b.company_id = ?
						AND c.enable_pay_stub_balance_display = ?
						AND a.user_id in (' . $this->getListSQL($user_id, $ph, 'int') . ')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict, $additional_order_fields);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }

    public function getByUserIdAndAccrualPolicyAccount($user_id, $accrual_policy_account_id, $where = null, $order = null)
    {
        if ($user_id == '') {
            return false;
        }

        if ($accrual_policy_account_id == '') {
            return false;
        }

        $ph = array(
            'user_id' => (int)$user_id,
            'accrual_policy_account_id' => (int)$accrual_policy_account_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND accrual_policy_account_id = ?
						AND deleted = 0';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
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

        $additional_order_fields = array('accrual_policy_account', 'first_name', 'last_name', 'name', 'default_branch', 'default_department');
        $sort_column_aliases = array(
            //'accrual_policy_type' => 'accrual_policy_type_id',
            'group' => 'e.name',
        );
        $order = $this->getColumnsFromAliases($order, $sort_column_aliases);

        if ($order == null) {
            $order = array('last_name' => 'asc', 'accrual_policy_account_id' => 'asc',);
            $strict = false;
        } else {
            //Always sort by last name, first name after other columns
            /*
            if ( !isset($order['effective_date']) ) {
                $order['effective_date'] = 'desc';
            }
            */
            $strict = true;
        }
        //Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
        //Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

        $uf = new UserFactory();
        $bf = new BranchFactory();
        $df = new DepartmentFactory();
        $ugf = new UserGroupFactory();
        $utf = new UserTitleFactory();
        $apaf = new AccrualPolicyAccountFactory();
        $af = new AccrualFactory();

        $ph = array(
            'company_id' => (int)$company_id,
        );

        $query = '
					select	a.*,
							ab.name as accrual_policy_account,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as "group",
							f.id as title_id,
							f.name as title
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $apaf->getTable() . ' as ab ON ( a.accrual_policy_account_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN ' . $bf->getTable() . ' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as f ON ( b.title_id = f.id AND f.deleted = 0 )

					where	b.company_id = ?
						AND EXISTS ( select 1 from ' . $af->getTable() . ' as af WHERE af.accrual_policy_account_id = a.accrual_policy_account_id AND a.user_id = af.user_id AND af.deleted = 0 )
					';

        $query .= (isset($filter_data['permission_children_ids'])) ? $this->getWhereClauseSQL('a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['user_id'])) ? $this->getWhereClauseSQL('a.user_id', $filter_data['user_id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['id'])) ? $this->getWhereClauseSQL('a.id', $filter_data['id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['exclude_id'])) ? $this->getWhereClauseSQL('a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph) : null;

        $query .= (isset($filter_data['accrual_policy_account_id'])) ? $this->getWhereClauseSQL('a.accrual_policy_account_id', $filter_data['accrual_policy_account_id'], 'numeric_list', $ph) : null;

        if (isset($filter_data['status']) and !is_array($filter_data['status']) and trim($filter_data['status']) != '' and !isset($filter_data['status_id'])) {
            $filter_data['status_id'] = Option::getByFuzzyValue($filter_data['status'], $this->getOptions('status'));
        }
        $query .= (isset($filter_data['status_id'])) ? $this->getWhereClauseSQL('b.status_id', $filter_data['status_id'], 'numeric_list', $ph) : null;

        $query .= (isset($filter_data['group_id'])) ? $this->getWhereClauseSQL('b.group_id', $filter_data['group_id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['group'])) ? $this->getWhereClauseSQL('e.name', $filter_data['group'], 'text', $ph) : null;

        $query .= (isset($filter_data['default_branch_id'])) ? $this->getWhereClauseSQL('b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['default_branch'])) ? $this->getWhereClauseSQL('c.name', $filter_data['default_branch'], 'text', $ph) : null;

        $query .= (isset($filter_data['default_department_id'])) ? $this->getWhereClauseSQL('b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['default_department'])) ? $this->getWhereClauseSQL('d.name', $filter_data['default_department'], 'text', $ph) : null;

        $query .= (isset($filter_data['title_id'])) ? $this->getWhereClauseSQL('b.title_id', $filter_data['title_id'], 'numeric_list', $ph) : null;
        $query .= (isset($filter_data['title'])) ? $this->getWhereClauseSQL('f.name', $filter_data['title'], 'text', $ph) : null;

        $query .= (isset($filter_data['first_name'])) ? $this->getWhereClauseSQL('b.first_name', $filter_data['first_name'], 'text_metaphone', $ph) : null;
        $query .= (isset($filter_data['last_name'])) ? $this->getWhereClauseSQL('b.last_name', $filter_data['last_name'], 'text_metaphone', $ph) : null;

        $query .= (isset($filter_data['country'])) ? $this->getWhereClauseSQL('b.country', $filter_data['country'], 'upper_text_list', $ph) : null;
        $query .= (isset($filter_data['province'])) ? $this->getWhereClauseSQL('b.province', $filter_data['province'], 'upper_text_list', $ph) : null;

        $query .= '
						AND ( a.deleted = 0 AND ab.deleted = 0 )
					';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict, $additional_order_fields);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }
}
