<?php
declare(strict_types=1);

namespace PavelShev\LoanCalculator;

class DifferentiatedCalc extends LoanCalculator
{

    /**
     * Calculate total payment for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function monthlyPayment(int $monthNumber): float
    {
        return $this->interestDebtPayment($monthNumber) + $this->principalDebtPayment($monthNumber);
    }

    /**
     * Calculate payment on principal debt for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function principalDebtPayment(int $monthNumber): float
    {
        return $this->getLoanAmount() / $this->getLoanTermInMonths();
    }

}
