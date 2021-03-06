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
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_MD_ALL extends PayrollDeduction_US_MD
{
    public $district_options = array(
        //01-Jan-12: No change.
        //01-Jan-11: No change.
        //01-Jan-10: No change.
        //01-Jan-09: No change.
        20080701 => array(
            'standard_deduction_rate' => 15,
            'standard_deduction_minimum' => 1500,
            'standard_deduction_maximum' => 2000,
            'allowance' => 3200
        ),
        20060101 => array(
            'standard_deduction_rate' => 15,
            'standard_deduction_minimum' => 1500,
            'standard_deduction_maximum' => 2000,
            'allowance' => 2400
        )
    );

    public function getDistrictTaxPayable()
    {
        $annual_income = $this->getDistrictAnnualTaxableIncome();

        $rate = bcdiv($this->getUserValue1(), 100);

        $retval = bcmul($annual_income, $rate);

        if ($retval < 0) {
            $retval = 0;
        }

        Debug::text('District Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }

    public function getDistrictAnnualTaxableIncome()
    {
        $annual_income = $this->getAnnualTaxableIncome();
        $standard_deduction = $this->getDistrictStandardDeductionAmount();
        $district_allowance = $this->getDistrictAllowanceAmount();

        $income = bcsub(bcsub($annual_income, $standard_deduction), $district_allowance);

        Debug::text('District Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10);

        return $income;
    }

    public function getDistrictStandardDeductionAmount()
    {
        $retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
        if ($retarr == false) {
            return false;
        }

        $rate = bcdiv($retarr['standard_deduction_rate'], 100);

        $deduction = bcmul($this->getAnnualTaxableIncome(), $rate);

        if ($deduction < $retarr['standard_deduction_minimum']) {
            $retval = $retarr['standard_deduction_minimum'];
        } elseif ($deduction > $retarr['standard_deduction_maximum']) {
            $retval = $retarr['standard_deduction_maximum'];
        } else {
            $retval = $deduction;
        }

        Debug::text('District Standard Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }

    public function getDistrictAllowanceAmount()
    {
        $retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
        if ($retarr == false) {
            return false;
        }

        $allowance_arr = $retarr['allowance'];

        $retval = bcmul($this->getDistrictAllowance(), $allowance_arr);

        Debug::text('District Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }
}
