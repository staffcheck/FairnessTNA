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
 * Checks for any version updates...
 *
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php');

//
//Check system requirements.
//
if (PRODUCTION == true) {
    Debug::Text('Checking system requirements... ' . TTDate::getDate('DATE+TIME', time()), __FILE__, __LINE__, __METHOD__, 10);
    $install_obj = new Install();
    $failed_requirment_requirements = $install_obj->getFailedRequirements(false, array('base_url', 'clean_cache'));

    if (is_array($failed_requirment_requirements) and count($failed_requirment_requirements) > 1) {
        SystemSettingFactory::setSystemSetting('valid_install_requirements', 0);
        Debug::Text('Failed system requirements: ' . implode($failed_requirment_requirements), __FILE__, __LINE__, __METHOD__, 10);
        TTLog::addEntry(0, 510, 'Failed system requirements: ' . implode($failed_requirment_requirements), 0, 'company');
    } else {
        SystemSettingFactory::setSystemSetting('valid_install_requirements', 1);
    }

    unset($install_obj, $check_all_requirements);
    Debug::Text('Checking system requirements complete... ' . TTDate::getDate('DATE+TIME', time()), __FILE__, __LINE__, __METHOD__, 10);
}

//
// Purge database tables
//
if (!isset($config_vars['other']['disable_database_purging'])
    or isset($config_vars['other']['disable_database_purging']) and $config_vars['other']['disable_database_purging'] != true
) {
    PurgeDatabase::Execute();
}

//
// Clean cache directories
// - Make sure cache directory is set, and log/storage directories are not contained within it.
//
if (!isset($config_vars['other']['disable_cache_purging'])
    or isset($config_vars['other']['disable_cache_purging']) and $config_vars['other']['disable_cache_purging'] != true
) {
    if (isset($config_vars['cache']['dir'])
        and $config_vars['cache']['dir'] != ''
        and strpos($config_vars['path']['log'], $config_vars['cache']['dir']) === false
        and strpos($config_vars['path']['storage'], $config_vars['cache']['dir']) === false
    ) {
        Debug::Text('Purging Cache directory: ' . $config_vars['cache']['dir'] . ' - ' . TTDate::getDate('DATE+TIME', time()), __FILE__, __LINE__, __METHOD__, 10);
        $install_obj = new Install();
        $install_obj->cleanCacheDirectory(''); //Don't exclude .ZIP files, so if there is a corrupt one it will be redownloaded within a week.
        Debug::Text('Purging Cache directory complete: ' . TTDate::getDate('DATE+TIME', time()), __FILE__, __LINE__, __METHOD__, 10);
    } else {
        Debug::Text('Cache directory is invalid: ' . TTDate::getDate('DATE+TIME', time()), __FILE__, __LINE__, __METHOD__, 10);
    }
}

//
//Check for severely out of date versions and take out of production mode if necessary.
//
if (PRODUCTION == true and ((time() - (int)APPLICATION_VERSION_DATE) > (86400 * 455))) {
    Debug::Text('ERROR: Application version is severely out of date, changing production mode... ', __FILE__, __LINE__, __METHOD__, 10);
    $install_obj = new Install();
    $tmp_config_vars['debug']['production'] = 'FALSE';
    $write_config_result = $install_obj->writeConfigFile($tmp_config_vars);
    unset($install_obj, $tmp_config_vars, $write_config_result);
}

Debug::writeToLog();
Debug::Display();
