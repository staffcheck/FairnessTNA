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
 * @package Core
 */
class CompanySettingListFactory extends CompanySettingFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => (int)$id, 
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => (int)$id, 
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndName($company_id, $name) {		
		if ( $company_id == '' ) {
			return FALSE;
		}
		
		if ( $name == '') {
			return FALSE;
		}

		$cache_id = $company_id.$name;

		$ph = array(
					'company_id' => (int)$company_id, 
					'name' => $name, 
					);

		$this->rs = $this->getCache($cache_id);
		if ( $this->rs === FALSE ) {
			$query = '
						select	*
						from	'. $this->getTable() .'
						where	company_id = ?
							AND	name = ?
							AND deleted = 0';

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $cache_id);
		}
		return $this;
	}

}
?>