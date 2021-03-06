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
 * @package Core
 */
class UserDateListFactory extends UserDateFactory implements IteratorAggregate
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


        $this->rs = $this->getCache($id);
        if ($this->rs === false) {
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

    public function getByIds($id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $ph = array();

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	id in (' . $this->getListSQL($id, $ph, 'int') . ')
						AND deleted = 0';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyId($company_id, $order = null)
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
						AND	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

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

        $uf = new UserFactory();

        $ph = array(
            'company_id' => (int)$company_id,
            'id' => (int)$id,
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyIdAndStartDateAndEndDateAndPayPeriodStatus($company_id, $start_date, $end_date, $status, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($start_date == '') {
            return false;
        }

        if ($end_date == '') {
            return false;
        }

        if ($order == null) {
            $order = array('a.user_id' => 'asc', 'a.date_stamp' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $uf = new UserFactory();
        $ppf = new PayPeriodFactory();

        $ph = array(
            'company_id' => (int)$company_id,
            'start_date' => $this->db->BindDate($start_date),
            'end_date' => $this->db->BindDate($end_date),
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON a.user_id = b.id
						LEFT JOIN ' . $ppf->getTable() . ' as c ON a.pay_period_id = c.id
					where	b.company_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND c.status_id in (' . $this->getListSQL($status, $ph) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserId($user_id, $order = null)
    {
        if ($user_id == '') {
            return false;
        }

        $ph = array(
            'user_id' => (int)$user_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND deleted = 0';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByPayPeriodId($pay_period_id, $order = null)
    {
        if ($pay_period_id == '') {
            return false;
        }

        $ph = array(
            'pay_period_id' => (int)$pay_period_id,
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where	pay_period_id = ?
						AND deleted = 0';
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByDate($date)
    {
        if ($date == '') {
            return false;
        }

        $uf = new UserFactory();

        $ph = array(
            'date' => $this->db->BindDate($date),
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON a.user_id = b.id
					where
						a.date_stamp = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserIdAndDate($user_id, $date)
    {
        if ($user_id === '') {
            return false;
        }

        if ($date == '' or $date <= 0) {
            return false;
        }

        $ph = array(
            'user_id' => (int)$user_id,
            'date' => $this->db->BindDate($date),
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = 0
					ORDER BY id ASC
					';

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserIdAndStartDateAndEndDate($user_ids, $start_date, $end_date, $where = null, $order = null)
    {
        if ($user_ids == '') {
            return false;
        }

        if ($start_date == '') {
            return false;
        }

        if ($end_date == '') {
            return false;
        }

        if ($order == null) {
            $order = array('date_stamp' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'start_date' => $this->db->BindDate($start_date),
            'end_date' => $this->db->BindDate($end_date),
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in (' . $this->getListSQL($user_ids, $ph, 'int') . ')
						AND deleted = 0
					';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserIdAndStartDateAndEndDateAndEmptyPayPeriod($user_ids, $start_date, $end_date, $where = null, $order = null)
    {
        if ($user_ids == '') {
            return false;
        }

        if ($start_date == '') {
            return false;
        }

        if ($end_date == '') {
            return false;
        }

        if ($order == null) {
            $order = array('date_stamp' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array(
            'start_date' => $this->db->BindDate($start_date),
            'end_date' => $this->db->BindDate($end_date),
        );

        $query = '
					select	*
					from	' . $this->getTable() . '
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in (' . $this->getListSQL($user_ids, $ph, 'int') . ')
						AND ( pay_period_id = 0 OR pay_period_id IS NULL )
						AND deleted = 0
					';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByUserIdAndPayPeriodID($user_id, $pay_period_id, $where = null, $order = null)
    {
        if ($user_id == '') {
            return false;
        }

        if ($pay_period_id == '') {
            return false;
        }

        //Order matters here, as this is mainly used for recalculating timesheets.
        //The days must be returned in order.
        if ($order == null) {
            $order = array('date_stamp' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $ph = array();

        $query = '
					select	*
					from	' . $this->getTable() . '
					where
						user_id in (' . $this->getListSQL($user_id, $ph, 'int') . ')
						AND pay_period_id in (' . $this->getListSQL($pay_period_id, $ph, 'int') . ')
						AND deleted = 0
					';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyIdAndPayPeriodID($company_id, $pay_period_id, $where = null, $order = null)
    {
        if ($company_id == '') {
            return false;
        }

        if ($pay_period_id == '') {
            return false;
        }

        //Order matters here, as this is mainly used for recalculating timesheets.
        //The days must be returned in order.
        if ($order == null) {
            $order = array('a.date_stamp' => 'asc');
            $strict = false;
        } else {
            $strict = true;
        }

        $uf = new UserFactory();

        $ph = array(
            'company_id' => (int)$company_id,
            //'pay_period_id' => (int)$pay_period_id,
        );

        $query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.pay_period_id in (' . $this->getListSQL($pay_period_id, $ph, 'int') . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    //Used by calcQuickExceptions maintenance job to speed up finding days that need to have exceptions calculated throughout the day.
    public function getMidDayExceptionsByStartDateAndEndDateAndPayPeriodStatus($start_date, $end_date, $pay_period_status_id)
    {
        if ($start_date == '') {
            return false;
        }

        if ($end_date == '') {
            return false;
        }

        if ($pay_period_status_id == '') {
            return false;
        }

        $epf = new ExceptionPolicyFactory();
        $ef = new ExceptionFactory();
        $epcf = new ExceptionPolicyControlFactory();
        $pgf = new PolicyGroupFactory();
        $pguf = new PolicyGroupUserFactory();
        $uf = new UserFactory();
        $cf = new CompanyFactory();
        $udf = new UserDateFactory();
        $sf = new ScheduleFactory();
        $pcf = new PunchControlFactory();
        $pf = new PunchFactory();
        $ppf = new PayPeriodFactory();

        $current_epoch = time();

        $ph = array(
            'current_time1' => $this->db->BindTimeStamp($current_epoch),
            'current_time2' => $this->db->BindTimeStamp($current_epoch),
            'current_epoch1' => $current_epoch,
            'start_date' => $this->db->BindDate($start_date),
            'end_date' => $this->db->BindDate($end_date),
        );

        //Exceptions that need to be calculated in the middle of the day:
        //Definitely: In Late, Out Late, Missed CheckIn
        //Possible: Over Daily Scheduled Time, Over Weekly Scheduled Time, Over Daily Time, Over Weekly Time, Long Lunch (can't run this fast enough), Long Break (can't run this fast enough),
        //Optimize calcQuickExceptions:
        // Loop through exception policies where In Late/Out Late/Missed CheckIn are enabled.
        // Loop through ACTIVE users assigned to these exceptions policies.
        // Only find days that are scheduled AND ( NO punch after schedule start time OR NO punch after schedule end time )
        //		For Missed CheckIn they do not need to be scheduled.
        // Exclude days that already have the exceptions triggered on them (?) (What about split shifts?)
        //	- Just exclude exceptions not assigned to punch/punch_control_id, if there is more than one in the day I don't think it helps much anyways.
        //
        //Currently Over Weekly/Daily time exceptions are only triggered on a Out punch.
        $query = '	select distinct udf.*
					FROM ' . $epf->getTable() . ' as epf
					LEFT JOIN ' . $epcf->getTable() . ' as epcf ON ( epf.exception_policy_control_id = epcf.id )
					LEFT JOIN ' . $pgf->getTable() . ' as pgf ON ( epcf.id = pgf.exception_policy_control_id )
					LEFT JOIN ' . $pguf->getTable() . ' as pguf ON ( pgf.id = pguf.policy_group_id )
					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( pguf.user_id = uf.id )
					LEFT JOIN ' . $cf->getTable() . ' as cf ON ( uf.company_id = cf.id )
					LEFT JOIN ' . $udf->getTable() . ' as udf ON ( uf.id = udf.user_id )
					LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( ppf.id = udf.pay_period_id )
					LEFT JOIN ' . $ef->getTable() . ' as ef ON ( udf.id = ef.user_date_id AND ef.exception_policy_id = epf.id AND ef.type_id != 5 )
					LEFT JOIN ' . $sf->getTable() . ' as sf ON ( udf.id = sf.user_date_id AND ( sf.start_time <= ? OR sf.end_time <= ? ) )
					LEFT JOIN ' . $pcf->getTable() . ' as pcf ON ( udf.id = pcf.user_date_id AND pcf.deleted = 0 )
					LEFT JOIN ' . $pf->getTable() . ' as pf ON	(
																pcf.id = pf.punch_control_id AND pf.deleted = 0
																AND (
																		( epf.type_id = \'S4\' AND ( pf.time_stamp >= sf.start_time OR pf.time_stamp <= sf.end_time ) )
																		OR
																		( epf.type_id = \'S6\' AND ( pf.time_stamp >= sf.end_time ) )
																		OR
																		( epf.type_id = \'C1\' AND ( pf.status_id = 10 AND pf.time_stamp <= ' . $this->getSQLToTimeStampFunction() . '(?-epf.grace) ) )
																	)
																)
					WHERE ( epf.type_id in (\'S4\', \'S6\', \'C1\') AND epf.active = 1 )
						AND ( uf.status_id = 10 AND cf.status_id != 30 )
						AND ( udf.date_stamp >= ? AND udf.date_stamp <= ? )
						AND ppf.status_id in (' . $this->getListSQL($pay_period_status_id, $ph, 'int') . ')
						AND ( ( ( epf.type_id in (\'S4\', \'S6\') AND ( sf.id IS NOT NULL AND sf.deleted = 0 ) AND pf.id IS NULL ) OR epf.type_id = \'C1\' ) AND ef.id IS NULL	)
						AND ( epf.deleted = 0 AND epcf.deleted = 0 AND pgf.deleted = 0 AND uf.deleted = 0 AND cf.deleted = 0 AND udf.deleted = 0 )
				';
        //Don't check deleted = 0 on PCF/PF tables, as we need to check IS NULL on them instead.

        //Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    /*

        Report functions

    */

    public function getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate($time_period, $user_ids, $company_id, $start_date, $end_date)
    {
        if ($time_period == '') {
            return false;
        }

        if ($user_ids == '') {
            return false;
        }

        if ($company_id == '') {
            return false;
        }

        if ($start_date == '') {
            return false;
        }

        if ($end_date == '') {
            return false;
        }

        /*
        if ( $order == NULL ) {
            $order = array( 'date_stamp' => 'asc' );
            $strict = FALSE;
        } else {
            $strict = TRUE;
        }
        */

        $uf = new UserFactory();
        $pcf = new PunchControlFactory();

        $ph = array(
            'company_id' => (int)$company_id,
            'start_date' => $this->db->BindDate($start_date),
            'end_date' => $this->db->BindDate($end_date),
        );

        $query = '
					select	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select	a.user_id,
								(EXTRACT(' . $time_period . ' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) ) as date,
								count(*) as total
						from	' . $this->getTable() . ' as a,
								' . $uf->getTable() . ' as b
						where	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in (' . $this->getListSQL($user_ids, $ph, 'int') . ')
							AND exists(
										select id
										from ' . $pcf->getTable() . ' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id, (EXTRACT(' . $time_period . ' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) )
						) tmp
					GROUP BY user_id
					';

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function deleteByUserIdAndDateAndDeleted($user_id, $date, $deleted)
    {
        if ($user_id == '') {
            return false;
        }

        if ($date == '' or $date <= 0) {
            return false;
        }

        if ($deleted == '') {
            return false;
        }

        $ph = array(
            'user_id' => (int)$user_id,
            'date' => $this->db->BindDate($date),
            'deleted' => (int)$deleted
        );

        $query = '
					delete
					from	' . $this->getTable() . '
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = ?
					';

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getArrayByListFactory($lf)
    {
        if (!is_object($lf)) {
            return false;
        }

        $list = array();
        foreach ($lf as $obj) {
            $list[] = $obj->getID();
        }

        if (empty($list) == false) {
            return $list;
        }

        return false;
    }
}
