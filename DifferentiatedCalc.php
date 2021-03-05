<?php
declare(strict_types=1);

namespace PavelShev\LoanCalculator;

class DifferentiatedCalc extends LoanCalculator
{

    /**
     * Calculate unpaid loan amount for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function monthlyLoanAmount(int $monthNumber): float
    {
        if ($this->hasCached('monthlyLoanAmount', $monthNumber)) {
            return $this->getCached('monthlyLoanAmount', $monthNumber);
        }
        if ($monthNumber === 0) {
            return $this->getLoanAmount();
        }
        $repaidLoanPartByMonth = $this->principalDebtPayment($monthNumber) * $monthNumber;

        $value = $this->getLoanAmount() - $repaidLoanPartByMonth;
        $this->setCached('monthlyLoanAmount', $monthNumber, $value);

        return $value;
    }

    /**
     * Calculate total payment for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function monthlyPayment(int $monthNumber): float
    {
        if ($this->hasCached('monthlyPayment', $monthNumber)) {
            return $this->getCached('monthlyPayment', $monthNumber);
        }
        $value = $this->interestDebtPayment($monthNumber) + $this->principalDebtPayment($monthNumber);
        $this->setCached('monthlyPayment', $monthNumber, $value);

        return $value;
    }

    /**
     * Calculate payment on principal debt for the month by its number in repayment period
     *
     * @param int|bool $monthNumber - not used in differentiated repayment
     * @return float
     */
    public function principalDebtPayment(int|bool $monthNumber = false): float
    {
        if ($this->hasCached('principalDebtPayment', 'ALL')) {
            return $this->getCached('principalDebtPayment', 'ALL');
        }
        $value = $this->getLoanAmount() / $this->getLoanTermInMonths();
        $this->setCached('principalDebtPayment', 'ALL', $value);

        return $value;
    }

}
