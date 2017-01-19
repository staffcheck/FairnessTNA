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
class PermissionUserFactory extends Factory {
	protected $table = 'permission_user';
	protected $pk_sequence_name = 'permission_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $permission_control_obj = NULL;

	function getPermissionControlObject() {
		return $this->getGenericObject( 'PermissionControlListFactory',
		$this->getPermissionControl(), 'permission_control_obj' );
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getPermissionControl() {
		return (int)$this->data['permission_control_id'];
	}

	function setPermissionControl($id) {
		$id = trim($id);

		$pclf = TTnew( 'PermissionControlListFactory' );

		if ( $id != 0
				OR $this->Validator->isResultSetWithRows( 'permission_control',
															$pclf->getByID($id),
															TTi18n::gettext('Permission Group is
															invalid') ) ) {
			$this->data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueUser($id) {
		$pclf = TTnew( 'PermissionControlListFactory' );

		$ph = array(
					'id' => $id
					);

		$query = 'select a.id from '. $this->getTable() .' as a, '. $pclf->getTable() .' as b where a.permission_control_id = b.id AND a.user_id = ? AND b.deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id, 'Unique User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_id === FALSE ) {
			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		return (int)$this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id != 0
				AND $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Selected Employee is invalid')
															)
				AND	$this->Validator->isTrue(		'user',
													$this->isUniqueUser($id),
													TTi18n::gettext('Selected Employee is already assigned to another Permission Group')
													)
			) {

			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getPermissionControl(), $log_action, TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ), NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>