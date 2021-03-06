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

/*
 * Adds time to employee accruals based on calendar milestones
 * This file should run once a day.
 *
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php');

//Debug::setVerbosity(11);

$current_epoch = TTDate::getTime();
//$current_epoch = strtotime('28-Dec-07 1:00 AM');

$offset = (86400 - (3600 * 2)); //22hrs of variance. Must be less than 24hrs which is how often this script runs.

$clf = new CompanyListFactory();
$clf->getByStatusID(array(10, 20, 23), null, array('a.id' => 'asc'));
if ($clf->getRecordCount() > 0) {
    foreach ($clf as $c_obj) {
        if ($c_obj->getStatus() != 30) {
            $aplf = new AccrualPolicyListFactory();
            $aplf->getByCompanyIdAndTypeId($c_obj->getId(), array(20, 30)); //Include hour based accruals so rollover adjustments can be calculated.
            if ($aplf->getRecordCount() > 0) {
                foreach ($aplf as $ap_obj) {
                    $ap_obj->addAccrualPolicyTime($current_epoch, $offset);
                }
            }
        }
    }
}
Debug::writeToLog();
Debug::Display();
