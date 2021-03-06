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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'FairnessClientAPI.class.php');

//Example: php export_report.php -server "http://192.168.1.1/fairness/api/soap/api.php" -username myusername -password mypass -report UserSummaryReport -template "by_employee+contact" /tmp/employee_list.csv csv
if ($argc < 3 or in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
    $help_output = "Usage: export_report.php [OPTIONS] [output file] [file format]\n";
    $help_output .= "\n";
    $help_output .= "  Options:\n";
    $help_output .= "    -server <URL>				URL to API server\n";
    $help_output .= "    -username <username>		API username\n";
    $help_output .= "    -password <password>		API password\n";
    $help_output .= "    -report <report>			Report to export (ie: TimesheetDetailReport,TimesheetSummaryReport,ScheduleSummaryReport,UserSummaryReport,PayStubSummaryReport)\n";
    $help_output .= "    -saved_report <name>		Name of saved report\n";
    $help_output .= "    -template <template>		Name of template\n";
    $help_output .= "    -time_period <name>		Time Period for report\n";

    echo $help_output;
} else {
    //Handle command line arguments
    $last_arg = count($argv) - 1;

    if (in_array('-n', $argv)) {
        $dry_run = true;
    } else {
        $dry_run = false;
    }

    if (in_array('-server', $argv)) {
        $api_url = trim($argv[array_search('-server', $argv) + 1]);
    } else {
        $api_url = false;
    }

    if (in_array('-username', $argv)) {
        $username = trim($argv[array_search('-username', $argv) + 1]);
    } else {
        $username = false;
    }

    if (in_array('-password', $argv)) {
        $password = trim($argv[array_search('-password', $argv) + 1]);
    } else {
        $password = false;
    }

    if (in_array('-report', $argv)) {
        $report = trim($argv[array_search('-report', $argv) + 1]);
    } else {
        $report = false;
    }

    if (in_array('-template', $argv)) {
        $template = trim($argv[array_search('-template', $argv) + 1]);
    } else {
        $template = false;
    }

    if (in_array('-saved_report', $argv)) {
        $saved_report = trim($argv[array_search('-saved_report', $argv) + 1]);
    } else {
        $saved_report = false;
    }

    if (in_array('-time_period', $argv)) {
        $time_period = trim($argv[array_search('-time_period', $argv) + 1]);
    } else {
        $time_period = false;
    }

    $output_file = null;
    if (isset($argv[$last_arg - 1]) and $argv[$last_arg - 1] != '') {
        $output_file = $argv[$last_arg - 1];
    }

    $file_format = 'csv';
    if (isset($argv[$last_arg]) and $argv[$last_arg] != '') {
        $file_format = $argv[$last_arg];
    }

    if (!isset($output_file)) {
        echo "Output File not set!\n";
        exit;
    }

    $FAIRNESS_URL = $api_url;

    $api_session = new FairnessClientAPI();
    $api_session->Login($username, $password);
    if ($FAIRNESS_SESSION_ID == false) {
        echo "API Username/Password is incorrect!\n";
        exit(1);
    }
    //echo "Session ID: $FAIRNESS_SESSION_ID\n";

    if ($report != '') {
        $report_obj = new FairnessClientAPI($report);

        $config = array();
        if ($saved_report != '') {
            $saved_report_obj = new FairnessClientAPI('UserReportData');
            $saved_report_result = $saved_report_obj->getUserReportData(array('filter_data' => array('name' => trim($saved_report))));
            $saved_report_data = $saved_report_result->getResult();
            if (is_array($saved_report_data) and isset($saved_report_data[0]) and isset($saved_report_data[0]['data'])) {
                $config = $saved_report_data[0]['data']['config'];
            } else {
                echo "ERROR: Saved report not found...\n";
                exit(1);
            }
        } elseif ($template != '') {
            $config_result = $report_obj->getTemplate($template);
            $config = $config_result->getResult();
        }

        if ($time_period != '' and isset($config['-1010-time_period'])) {
            $config['-1010-time_period']['time_period'] = $time_period;
        }
        //var_dump($config);

        $result = $report_obj->getReport($config, strtolower($file_format));
        $retval = $result->getResult();
        if (is_array($retval)) {
            if (isset($retval['file_name']) and $output_file == '') {
                $output_file = $retval['file_name'];
            }
            file_put_contents($output_file, base64_decode($retval['data']));
        } else {
            var_dump($retval);
            echo "ERROR: No report data...\n";
            exit(1);
        }
    } else {
        echo "ERROR: No report specified...\n";
        exit(1);
    }
}
