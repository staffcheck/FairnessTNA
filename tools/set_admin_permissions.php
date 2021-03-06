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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php');

if ($argc < 2 or in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
    $help_output = "Usage: set_admin_permissions.php [user_name]\n";
    echo $help_output;
} else {
    //Handle command line arguments
    $last_arg = count($argv) - 1;

    if (isset($argv[$last_arg]) and $argv[$last_arg] != '') {
        $user_name = $argv[$last_arg];
        //Get user_id from user_name
        $ulf = new UserListFactory();
        $ulf->getByUserName($user_name);
        if ($ulf->getRecordCount() == 1) {
            echo "Found user, apply administrator permissions...\n";
            ob_flush();

            $u_obj = $ulf->getCurrent();
            //Create new Permission Group just for this purpose.
            $pf = new PermissionFactory();
            $pf->StartTransaction();

            //Apply all preset flags, including 0 => "system"
            $preset_flags = array_merge(array(0), array_keys($pf->getOptions('preset_flags')));

            $pcf = new PermissionControlFactory();
            $pcf->setCompany($u_obj->getCompany());
            $pcf->setLevel(25);
            $pcf->setName('Administrator Fix (' . rand(1, 1000) . ')');
            $pcf->setDescription('Created By set_admin_permissions.php');
            if ($pcf->isValid()) {
                $pcf_id = $pcf->Save(false);

                $pcf->setUser(array($u_obj->getId()));

                $pcf->Save();

                if ($pf->applyPreset($pcf_id, 40, $preset_flags) == true) {
                    echo "Success!\n";
                }
            }
            //$pf->FailTransaction();
            $pf->CommitTransaction();
        } elseif ($ulf->getRecordCount() > 2) {
            echo "Found more then one user with the same user name, not updating permissions!\n";
        } else {
            echo "User name not found!\n";
        }
    }
}

echo "WARNING: Clear FairnessTNA cache after running this.\n";
//Debug::Display();
Debug::writeToLog();
