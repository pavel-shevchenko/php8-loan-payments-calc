<?php
declare(strict_types=1);

namespace PavelShev\LoanCalculator;

class AnnuityCalculator extends LoanCalculator
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

        $value = $this->monthlyLoanAmount($monthNumber - 1) - $this->principalDebtPayment($monthNumber);
        $this->setCached('monthlyLoanAmount', $monthNumber, $value);

        return $value;
    }

    /**
     * Calculate total payment for the month by its number in repayment period
     *
     * @param int|bool $monthNumber - not used in annuity repayment
     * @return float
     */
    public function monthlyPayment(int|bool $monthNumber = false): float
    {
        if ($this->hasCached('monthlyPayment', 'ALL')) {
            return $this->getCached('monthlyPayment', 'ALL');
        }
        $monthlyRate = $this->monthlyRateAverage() / 100;
        $monthlyIncreaseRatio = (1 + $monthlyRate);
        $incrRatioTermExponent = $monthlyIncreaseRatio ** $this->getLoanTermInMonths();
        $annuityCoefNumerator = $monthlyRate * $incrRatioTermExponent;
        $annuityCoefDenominator = $incrRatioTermExponent - 1;
        $annuityCoefficient = $annuityCoefNumerator / $annuityCoefDenominator;

        $value = $annuityCoefficient * $this->getLoanAmount();
        $this->setCached('monthlyPayment', 'ALL', $value);

        return $value;
    }

    /**
     * Calculate payment on principal debt for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function principalDebtPayment(int $monthNumber): float
    {
        if ($this->hasCached('principalDebtPayment', $monthNumber)) {
            return $this->getCached('principalDebtPayment', $monthNumber);
        }
        $value = $this->monthlyPayment($monthNumber) - $this->interestDebtPayment($monthNumber);
        $this->setCached('principalDebtPayment', $monthNumber, $value);

        return $value;
    }

}
