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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_PE extends PayrollDeduction_CA
{
    public $provincial_income_tax_rate_options = array(
        20070701 => array(
            array('income' => 31984, 'rate' => 9.8, 'constant' => 0),
            array('income' => 63969, 'rate' => 13.8, 'constant' => 1279),
            array('income' => 63969, 'rate' => 16.7, 'constant' => 3134),
        ),
        20070101 => array(
            array('income' => 30754, 'rate' => 9.8, 'constant' => 0),
            array('income' => 61509, 'rate' => 13.8, 'constant' => 1230),
            array('income' => 61509, 'rate' => 16.7, 'constant' => 3014),
        ),
    );

    public function getProvincialSurtax()
    {
        /*
            V1 =
            For PEI
                Where T4 <= 12500
                V1 = 0

                Where T4 > 12500
                V1 = 0.10 * ( T4 - 12500 )
        */

        $T4 = $this->getProvincialBasicTax();
        $V1 = 0;

        if ($this->getDate() >= 20080101) {
            if ($T4 <= 12500) {
                $V1 = 0;
            } elseif ($T4 > 12500) {
                $V1 = bcmul(0.10, bcsub($T4, 12500));
            }
        }

        Debug::text('V1: ' . $V1, __FILE__, __LINE__, __METHOD__, 10);

        return $V1;
    }
}
