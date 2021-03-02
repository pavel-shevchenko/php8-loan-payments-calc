<?php
declare(strict_types=1);

namespace PavelShev\LoanCalculator;

class AnnuityCalculator extends LoanCalculator
{

    /**
     * Calculate total payment for the month by its number in repayment period
     *
     * @param int|bool $monthNumber - not used in annuity
     * @return float
     */
    public function monthlyPayment(int|bool $monthNumber = false): float
    {
        $monthlyRate = $this->monthlyRateAverage() / 100;
        $exponent = (1 + $monthlyRate) ** $this->getLoanTermInMonths();
        $annuityNumerator = $monthlyRate * $exponent;
        $annuityDenominator = $exponent - 1;
        $annuityCoefficient = $annuityNumerator / $annuityDenominator;

        return $annuityCoefficient * $this->getLoanAmount();
    }

    /**
     * Calculate payment on principal debt for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function principalDebtPayment(int $monthNumber): float
    {
        return $this->monthlyPayment($monthNumber) - $this->interestDebtPayment($monthNumber);
    }

}
