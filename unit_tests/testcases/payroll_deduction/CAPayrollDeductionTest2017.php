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
 * @group CAPayrollDeductionTest2017
 */
class CAPayrollDeductionTest2017 extends PHPUnit_Framework_TestCase
{
    public $company_id = null;

    public function setUp()
    {
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

        require_once(Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php');

        $this->tax_table_file = dirname(__FILE__) . '/CAPayrollDeductionTest2017.csv';

        $this->company_id = PRIMARY_COMPANY_ID;

        TTDate::setTimeZone('Etc/GMT+8'); //Force to non-DST timezone. 'PST' isnt actually valid.

        return true;
    }

    public function tearDown()
    {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);
        return true;
    }

    public function testCSVFile()
    {
        $this->assertEquals(file_exists($this->tax_table_file), true);

        $test_rows = Misc::parseCSV($this->tax_table_file, true);

        $total_rows = (count($test_rows) + 1);
        $i = 2;
        foreach ($test_rows as $row) {
            //Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
            if (isset($row['gross_income']) and isset($row['low_income']) and isset($row['high_income'])
                and $row['gross_income'] == '' and $row['low_income'] != '' and $row['high_income'] != ''
            ) {
                $row['gross_income'] = ($row['low_income'] + (($row['high_income'] - $row['low_income']) / 2));
            }
            if ($row['country'] != '' and $row['gross_income'] != '') {
                //echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

                $pd_obj = new PayrollDeduction($row['country'], $row['province']);
                $pd_obj->setDate(strtotime($row['date']));
                $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
                $pd_obj->setAnnualPayPeriods($row['pay_periods']);

                $pd_obj->setFederalTotalClaimAmount($row['federal_claim']); //Amount from 2005, Should use amount from 2007 automatically.
                $pd_obj->setProvincialTotalClaimAmount($row['provincial_claim']);
                //$pd_obj->setWCBRate( 0.18 );

                $pd_obj->setEIExempt(false);
                $pd_obj->setCPPExempt(false);

                $pd_obj->setFederalTaxExempt(false);
                $pd_obj->setProvincialTaxExempt(false);

                $pd_obj->setYearToDateCPPContribution(0);
                $pd_obj->setYearToDateEIContribution(0);

                $pd_obj->setGrossPayPeriodIncome($this->mf($row['gross_income']));

                //var_dump($pd_obj->getArray());

                $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), $this->mf($row['gross_income']));
                if ($row['federal_deduction'] != '') {
                    $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), $this->mf($row['federal_deduction']));
                }
                if ($row['provincial_deduction'] != '') {
                    $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), $this->mf($row['provincial_deduction']));
                }
            }

            $i++;
        }

        //Make sure all rows are tested.
        $this->assertEquals($total_rows, ($i - 1));
    }

    //
    // January 2017
    //

    public function mf($amount)
    {
        return Misc::MoneyFormat($amount, false);
    }

    //Test that the tax changes from one year to the next are without a specified threshold.

    public function testCompareWithLastYearCSVFile()
    {
        $this->assertEquals(file_exists($this->tax_table_file), true);

        $test_rows = Misc::parseCSV($this->tax_table_file, true);

        $total_rows = (count($test_rows) + 1);
        $i = 2;
        foreach ($test_rows as $row) {
            //Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
            if (isset($row['gross_income']) and isset($row['low_income']) and isset($row['high_income'])
                and $row['gross_income'] == '' and $row['low_income'] != '' and $row['high_income'] != ''
            ) {
                $row['gross_income'] = ($row['low_income'] + (($row['high_income'] - $row['low_income']) / 2));
            }
            if ($row['country'] != '' and $row['gross_income'] != '') {
                //echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

                $pd_obj = new PayrollDeduction($row['country'], $row['province']);
                $pd_obj->setDate(strtotime('-1 year', strtotime($row['date']))); //Get the same date only last year.
                $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
                $pd_obj->setAnnualPayPeriods($row['pay_periods']);

                $pd_obj->setFederalTotalClaimAmount($row['federal_claim']); //Amount from 2005, Should use amount from 2007 automatically.
                $pd_obj->setProvincialTotalClaimAmount($row['provincial_claim']);
                //$pd_obj->setWCBRate( 0.18 );

                $pd_obj->setEIExempt(false);
                $pd_obj->setCPPExempt(false);

                $pd_obj->setFederalTaxExempt(false);
                $pd_obj->setProvincialTaxExempt(false);

                $pd_obj->setYearToDateCPPContribution(0);
                $pd_obj->setYearToDateEIContribution(0);

                $pd_obj->setGrossPayPeriodIncome($this->mf($row['gross_income']));

                //var_dump($pd_obj->getArray());

                $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), $this->mf($row['gross_income']));
                if ($row['federal_deduction'] != '') {
                    //$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->mf( $row['federal_deduction'] ) );
                    $amount_diff = 0;
                    $amount_diff_percent = 0;
                    if ($row['federal_deduction'] > 0) {
                        $amount_diff = abs(($pd_obj->getFederalPayPeriodDeductions() - $row['federal_deduction']));
                        $amount_diff_percent = (($amount_diff / $row['federal_deduction']) * 100);
                    }

                    //Debug::text($i.'. Amount: This Year: '. $row['federal_deduction'] .' Last Year: '. $pd_obj->getFederalPayPeriodDeductions() .' Diff Amount: '. $amount_diff .' Percent: '. $amount_diff_percent .'%', __FILE__, __LINE__, __METHOD__, 10);
                    if ($amount_diff > 1.5) {
                        $this->assertLessThan(3, $amount_diff_percent); //Should be slightly higher than inflation.
                        $this->assertGreaterThan(0, $amount_diff_percent);
                    }
                }
                if ($row['provincial_deduction'] != '') {
                    //$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
                    $amount_diff = 0;
                    $amount_diff_percent = 0;
                    if ($row['provincial_deduction'] > 0 and $pd_obj->getProvincialPayPeriodDeductions() > 0) {
                        $amount_diff = abs(($pd_obj->getProvincialPayPeriodDeductions() - $row['provincial_deduction']));
                        $amount_diff_percent = (($amount_diff / $row['provincial_deduction']) * 100);
                    }

                    Debug::text($i . '. Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getProvincialPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10);
                    if ($amount_diff > 3) {
                        $this->assertLessThan(20, $amount_diff_percent); //Reasonable margin of error.
                        $this->assertGreaterThan(0, $amount_diff_percent);
                    }
                }
            }

            $i++;
        }

        //Make sure all rows are tested.
        $this->assertEquals($total_rows, ($i - 1));
    }

    public function testCA_2017a_Example()
    {
        Debug::text('CA - Example Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'AB');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(29721.00);
        $pd_obj->setProvincialTotalClaimAmount(17593);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1100);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1100');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '78.00');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '67.52');
    }

    public function testCA_2017a_Example1()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'AB');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(12);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(18214);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1800);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1800');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '94.24');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '13.85');
    }

    public function testCA_2017a_Example2()
    {
        Debug::text('CA - Example2 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'AB');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(18214);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(2300);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '2300');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '280.85');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '145.04');
    }

    public function testCA_2017a_Example3()
    {
        Debug::text('CA - Example3 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'AB');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(18214);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(2500);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '2500');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '457.54');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '208.81');
    }

    public function testCA_2017a_Example4()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(9938);
        $pd_obj->setWCBRate(0);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1560);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1560');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '145.68');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '55.90');
    }

    public function testCA_2017a_GovExample1()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'AB');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(18214);
        $pd_obj->setWCBRate(0);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1030); //Take Gross income minus RPP and Union Dues.

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1030');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '116.07');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '60.68');
    }

    public function testCA_2017a_GovExample2()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(9938);
        $pd_obj->setWCBRate(0);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1030); //Take Gross income minus RPP and Union Dues.

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1030');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '116.07');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '46.40');
    }

    public function testCA_2017a_GovExample3()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'ON');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(9863);
        $pd_obj->setWCBRate(0);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1030); //Take Gross income minus RPP and Union Dues.

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1030');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '116.07');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '59.42');
    }

    public function testCA_2017a_GovExample4()
    {
        Debug::text('CA - Example1 - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'PE');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(52);

        $pd_obj->setFederalTotalClaimAmount(11327);
        $pd_obj->setProvincialTotalClaimAmount(7708);
        $pd_obj->setWCBRate(0);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1030); //Take Gross income minus RPP and Union Dues.

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1030');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '116.07');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '96.22');
    }

    //
    // CPP/ EI
    //
    public function testCA_2017a_BiWeekly_CPP_LowIncome()
    {
        Debug::text('CA - BiWeekly - CPP - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(0);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(585.32);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '585.32');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '22.31');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '22.31');
    }

    public function testCA_2017a_SemiMonthly_CPP_LowIncome()
    {
        Debug::text('CA - BiWeekly - CPP - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(24);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(0);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(585.23);

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '585.23');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '21.75');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '21.75');
    }

    public function testCA_2017a_SemiMonthly_MAXCPP_LowIncome()
    {
        Debug::text('CA - BiWeekly - MAXCPP - Beginning of 2017 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(24);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(0);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(($pd_obj->getCPPEmployeeMaximumContribution() - 1)); //2544.30 - 1.00
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(587.00);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '587.00');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '1.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '1.00');
    }

    public function testCA_2017a_EI_LowIncome()
    {
        Debug::text('CA - EI - Beginning of 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(0);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(587.76);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '587.76');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '9.58');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '13.41');
    }

    public function testCA_2017a_MAXEI_LowIncome()
    {
        Debug::text('CA - MAXEI - Beginning of 2006 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('01-Jan-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11635);
        $pd_obj->setProvincialTotalClaimAmount(0);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(($pd_obj->getEIEmployeeMaximumContribution() - 1));

        $pd_obj->setGrossPayPeriodIncome(587.00);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '587.00');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '1.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '1.40');
    }


    public function testCA_2017a_MAXEI_MAXCPPa()
    {
        Debug::text('CA - MAXEI/MAXCPP - Beginning of 2006 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('10-Nov-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11474);
        $pd_obj->setProvincialTotalClaimAmount(10027);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(2569.21);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '2569.21');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '120.51');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '120.51');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '41.88');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '58.63');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '336.03');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '131.85');
    }

    public function testCA_2017a_MAXEI_MAXCPPb()
    {
        Debug::text('CA - MAXEI/MAXCPP - Beginning of 2006 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('10-Nov-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11474);
        $pd_obj->setProvincialTotalClaimAmount(10027);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(($pd_obj->getCPPEmployeeMaximumContribution() - 20)); //2544.30 - 20.00
        $pd_obj->setYearToDateEIContribution(($pd_obj->getEIEmployeeMaximumContribution() - 20)); //955.04 - 20.00

        $pd_obj->setGrossPayPeriodIncome(2569.21);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '2569.21');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '20.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '20.00');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '20.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '28.00');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '336.03');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '131.85');
    }

    public function testCA_2017a_MAXEI_MAXCPPc()
    {
        Debug::text('CA - MAXEI/MAXCPP - Beginning of 2006 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('10-Nov-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11474);
        $pd_obj->setProvincialTotalClaimAmount(10027);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(($pd_obj->getCPPEmployeeMaximumContribution() - 1)); //2544.30 - 1.00
        $pd_obj->setYearToDateEIContribution(($pd_obj->getEIEmployeeMaximumContribution() - 1)); //955.04 - 1.00

        $pd_obj->setGrossPayPeriodIncome(2569.21);

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '2569.21');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '1.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '1.00');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '1.00');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '1.40');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '336.03');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '131.85');
    }

    public function testCA_2017a_MAXEI_MAXCPPd()
    {
        Debug::text('CA - MAXEI/MAXCPP - Beginning of 2006 01-Jan-2017: ', __FILE__, __LINE__, __METHOD__, 10);

        $pd_obj = new PayrollDeduction('CA', 'BC');
        $pd_obj->setDate(strtotime('10-Nov-2017'));
        $pd_obj->setEnableCPPAndEIDeduction(true); //Deduct CPP/EI.
        $pd_obj->setAnnualPayPeriods(26);

        $pd_obj->setFederalTotalClaimAmount(11474);
        $pd_obj->setProvincialTotalClaimAmount(10027);
        $pd_obj->setWCBRate(0.18);

        $pd_obj->setEIExempt(false);
        $pd_obj->setCPPExempt(false);

        $pd_obj->setFederalTaxExempt(false);
        $pd_obj->setProvincialTaxExempt(false);

        $pd_obj->setYearToDateCPPContribution(0);
        $pd_obj->setYearToDateEIContribution(0);

        $pd_obj->setGrossPayPeriodIncome(1900.00); //Less than EI/CPP maximum earnings for the year.

        //var_dump($pd_obj->getArray());

        $this->assertEquals($this->mf($pd_obj->getGrossPayPeriodIncome()), '1900.00');
        $this->assertEquals($this->mf($pd_obj->getEmployeeCPP()), '87.39');
        $this->assertEquals($this->mf($pd_obj->getEmployerCPP()), '87.39');
        $this->assertEquals($this->mf($pd_obj->getEmployeeEI()), '30.97');
        $this->assertEquals($this->mf($pd_obj->getEmployerEI()), '43.36');
        $this->assertEquals($this->mf($pd_obj->getFederalPayPeriodDeductions()), '200.71');
        $this->assertEquals($this->mf($pd_obj->getProvincialPayPeriodDeductions()), '80.94');
    }
}
