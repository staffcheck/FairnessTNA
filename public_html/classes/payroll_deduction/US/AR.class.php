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
class PayrollDeduction_US_AR extends PayrollDeduction_US
{
    public $state_income_tax_rate_options = array(
        20150101 => array(
            0 => array(
                array('income' => 4300, 'rate' => 0.9, 'constant' => 0),
                array('income' => 8400, 'rate' => 2.4, 'constant' => 38.70),
                array('income' => 12600, 'rate' => 3.4, 'constant' => 137.10),
                array('income' => 21000, 'rate' => 4.4, 'constant' => 279.90),
                array('income' => 35100, 'rate' => 5.90, 'constant' => 649.50),
                array('income' => 35100, 'rate' => 6.90, 'constant' => 1481.40),
            ),
        ),
        20060101 => array(
            0 => array(
                array('income' => 3000, 'rate' => 1.0, 'constant' => 0),
                array('income' => 6000, 'rate' => 2.5, 'constant' => 30),
                array('income' => 9000, 'rate' => 3.5, 'constant' => 105),
                array('income' => 15000, 'rate' => 4.5, 'constant' => 210),
                array('income' => 25000, 'rate' => 6.0, 'constant' => 480),
                array('income' => 25000, 'rate' => 7.0, 'constant' => 1080),
            ),
        ),
    );

    public $state_options = array(
        20150101 => array( //01-Jan-2015
            'standard_deduction' => 2200,
            'allowance' => 26
        ),
        20060101 => array( //01-Jan-2006
            'standard_deduction' => 2000,
            'allowance' => 20
        )
    );

    public function getStateTaxPayable()
    {
        $annual_income = $this->getStateAnnualTaxableIncome();

        $retval = 0;

        if ($annual_income > 0) {
            $rate = $this->getData()->getStateRate($annual_income);
            $state_constant = $this->getData()->getStateConstant($annual_income);
            $prev_income = $this->getData()->getStateRatePreviousIncome($annual_income);

            $retval = bcadd(bcmul(bcsub($annual_income, $prev_income), $rate), $state_constant);
        }

        $retval = bcsub($retval, $this->getStateAllowanceAmount());

        if ($retval < 0) {
            $retval = 0;
        }

        Debug::text('State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }

    public function getStateAnnualTaxableIncome()
    {
        $annual_income = $this->getAnnualTaxableIncome();
        $standard_deduction = $this->getStateStandardDeduction();

        $income = bcsub($annual_income, $standard_deduction);

        Debug::text('State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10);

        return $income;
    }

    public function getStateStandardDeduction()
    {
        $retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
        if ($retarr == false) {
            return false;
        }

        $retval = $retarr['standard_deduction'];

        Debug::text('State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }

    public function getStateAllowanceAmount()
    {
        $retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
        if ($retarr == false) {
            return false;
        }

        $allowance_arr = $retarr['allowance'];

        $retval = bcmul($this->getStateAllowance(), $allowance_arr);

        Debug::text('State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10);

        return $retval;
    }
}
