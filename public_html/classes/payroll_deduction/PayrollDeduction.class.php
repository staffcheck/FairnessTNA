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
 * @package PayrollDeduction
 */
class PayrollDeduction {
	var $obj = NULL;
	var $data = NULL;

	protected $version = '1.0.37';
	protected $data_version = '20170101';

	function __construct( $country, $province, $district = NULL) {
		$this->setCountry( $country );
		$this->setProvince( $province );
		$this->setDistrict( $district );

		$base_file_name = Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction_Base.class.php';
		$province_file_name = Environment::getBasePath().'/classes/payroll_deduction/'.$this->getCountry().'/'.$this->getProvince().'.class.php';
		$district_file_name = Environment::getBasePath().'/classes/payroll_deduction/'.$this->getCountry().'/'.$this->getProvince().'_'.$this->getDistrict().'.class.php';
		$country_file_name = Environment::getBasePath().'/classes/payroll_deduction/'.$this->getCountry().'.class.php';
		$data_file_name = Environment::getBasePath().'/classes/payroll_deduction/'.$this->getCountry().'/Data.class.php';

		if ( $this->getDistrict() != '' AND $this->getDistrict() != '00' ) {
			$class_name = 'PayrollDeduction_'.$this->getCountry().'_'.$this->getProvince().'_'.$this->getDistrict();
		} elseif ( $this->getProvince() != '' ) {
			$class_name = 'PayrollDeduction_'.$this->getCountry().'_'.$this->getProvince();
		} else {
			$class_name = 'PayrollDeduction_'.$this->getCountry();
		}

		//Debug::text('Country: '. $country_file_name .' Province: '. $province_file_name .' District: '. $district_file_name .' Class: '. $class_name, __FILE__, __LINE__, __METHOD__, 10);
		if ( ( file_exists($country_file_name) OR ( $this->getProvince() != '' AND file_exists($province_file_name) ) OR ( $this->getDistrict() != '' AND file_exists($district_file_name) ) ) AND file_exists($data_file_name) ) {
			//Debug::text('Country File Exists: '. $country_file_name .' Province File Name: '. $province_file_name .' Data File: '. $data_file_name, __FILE__, __LINE__, __METHOD__, 10);

			include_once( $base_file_name );
			include_once( $data_file_name );

			if ( file_exists($country_file_name) ) {
				include_once( $country_file_name );
			}
			if ( $this->getProvince() != '' AND file_exists($province_file_name) ) {
				include_once( $province_file_name );
			}
			if ( $this->getDistrict() != '' AND file_exists($district_file_name) ) {
				include_once( $district_file_name );
			}

			if ( class_exists( $class_name ) ) {
				$this->obj = new $class_name;
				$this->obj->setCountry( $this->getCountry() );
				$this->obj->setProvince( $this->getProvince() );
				$this->obj->setDistrict( $this->getDistrict() );

				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			Debug::text('File DOES NOT Exists Country File Name: '. $country_file_name .' Province File: '. $province_file_name, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function getVersion() {
		return $this->version;
	}
	function getDataVersion() {
		return $this->data_version;
	}

	private function getObject() {
		if ( is_object($this->obj) ) {
			return $this->obj;
		}

		return FALSE;
	}

	private function setCountry($country) {
		$this->data['country'] = strtoupper(trim($country));

		return TRUE;
	}
	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}

	private function setProvince($province) {
		$this->data['province'] = strtoupper(trim($province));

		return TRUE;
	}
	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}

	private function setDistrict($district) {
		$this->data['district'] = strtoupper(trim($district));

		return TRUE;
	}
	function getDistrict() {
		if ( isset($this->data['district']) ) {
			return $this->data['district'];
		}

		return FALSE;
	}

	function __call($function_name, $args = array() ) {
		if ( $this->getObject() !== FALSE ) {
			//Debug::text('Calling Sub-Class Function: '. $function_name, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_callable( array($this->getObject(), $function_name) ) ) {
				$return = call_user_func_array(array($this->getObject(), $function_name), $args);

				return $return;
			}
		}

		Debug::text('Sub-Class Function Call FAILED!:'. $function_name, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}
?>