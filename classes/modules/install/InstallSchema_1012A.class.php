<?php
/*********************************************************************************
 * This file is part of "Fairness", a Payroll and Time Management program.
 * Fairness is Copyright 2013 Aydan Coskun (aydan.ayfer.coskun@gmail.com)
 * Portions of this software are Copyright (C) 2003 - 2013 TimeTrex Software Inc.
 * because Fairness is a fork of "TimeTrex Workforce Management" Software.
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
/*
 * $Revision: 8371 $
 * $Id: InstallSchema_1012A.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1012A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}

	function postInstall() {
		global $cache;
		
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);
		
		//Get all pay period schedules.
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getAll();
		if ( $ppslf->getRecordCount() > 0 ) {
			foreach( $ppslf as $pps_obj ) {
				$user_ids = $pps_obj->getUser();
				if ( is_array($user_ids) ) {
					$time_zone_arr = array();
					foreach( $user_ids as $user_id ) {
						$uplf = TTnew( 'UserPreferenceListFactory' );
						$uplf->getByUserId( $user_id );
						if ( $uplf->getRecordCount() > 0 ) {
							if ( isset($time_zone_arr[$uplf->getCurrent()->getTimeZone()]) ) {
								$time_zone_arr[$uplf->getCurrent()->getTimeZone()]++;
							} else {
								$time_zone_arr[$uplf->getCurrent()->getTimeZone()] = 1;
							}
						}
					}
					
					arsort($time_zone_arr);
					
					//Grab the first time zone, as it is most common
					foreach( $time_zone_arr as $time_zone => $count ) {				
						break;
					}
					
					if ( $time_zone != '' ) {
						//Set pay period timezone to the timezone of the majority of the users are in.
						$pps_obj->setTimeZone( $time_zone );
						if ( $pps_obj->isValid() ) {
							$pps_obj->Save();
						}
					}
				}
			}
		}
		
		Debug::text('l: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);
				
		return TRUE;
	}
}
?>
