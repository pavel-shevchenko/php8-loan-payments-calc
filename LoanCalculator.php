<?php
declare(strict_types=1);

namespace PavelShev\LoanCalculator;

abstract class LoanCalculator
{

    /**
     * The period in months when the debtor must pay off the debt
     *
     * @var integer
     */
    protected int $loanTermInMonths;

    /**
     * Date defining the month of the first loan payment
     *
     * @var \DateTime
     */
    protected \DateTime $dateForFirstMonth;

    /**
     * Customer loan amount
     *
     * @var integer
     */
    protected int $loanAmount;

    /**
     * Interest rate - provided by creditor/lender
     *
     * @var float
     */
    protected float $interestRate;

    /**
     * Internal cache for intermediate calculated values
     *
     * @var float
     */
    private array $cache;

    /**
     * Set the data for calculating the loan payments and get calculator instance
     *
     * @param integer $loanTermInMonths
     * @param \DateTime $dateForFirstMonth
     * @param integer $loanAmount
     * @param integer|float $interestRate
     * @return LoanCalculator
     */
    function getCalculator(
        int $loanTermInMonths,
        \DateTime $dateForFirstMonth,
        int $loanAmount,
        int|float $interestRate): static
    {
        return (new static())->setLoanTermInMonths($loanTermInMonths)
            ->setDateForFirstMonth($dateForFirstMonth)
            ->setLoanAmount($loanAmount)
            ->setInterestRate($interestRate);
    }

    /**
     * Set loan term
     *
     * @param integer $loanTermInMonths
     * @return LoanCalculator
     */
    public function setLoanTermInMonths(int $loanTermInMonths): static
    {
        $this->loanTermInMonths = $loanTermInMonths;
        $this->clearCache();

        return $this;
    }

    /**
     * Retrieve loan term
     *
     * @return integer
     */
    public function getLoanTermInMonths(): int
    {
        return $this->loanTermInMonths;
    }

    /**
     * Set date defining the month of the first loan payment
     *
     * @param \DateTime $dateForFirstMonth
     * @return LoanCalculator
     */
    public function setDateForFirstMonth(\DateTime $dateForFirstMonth): static
    {
        $this->dateForFirstMonth = $dateForFirstMonth;
        $this->clearCache();

        return $this;
    }

    /**
     * Retrieve date defining the month of the first loan payment
     *
     * @return \DateTime
     */
    public function getDateForFirstMonth(): \DateTime
    {
        return $this->dateForFirstMonth;
    }

    /**
     * Set loan amount
     *
     * @param integer $loanAmount
     * @return LoanCalculator
     */
    public function setLoanAmount(int $loanAmount): static
    {
        $this->loanAmount = $loanAmount;
        $this->clearCache();

        return $this;
    }

    /**
     * Retrieve loan amount
     *
     * @return integer
     */
    public function getLoanAmount(): int
    {
        return $this->loanAmount;
    }

    /**
     * Set interest rate
     *
     * @param integer|float $interestRate
     * @return LoanCalculator
     */
    public function setInterestRate(int|float $interestRate): static
    {
        $this->interestRate = (float)$interestRate;
        $this->clearCache();

        return $this;
    }

    /**
     * Retrieve interest rate
     *
     * @return float
     */
    public function getInterestRate(): float
    {
        return $this->interestRate;
    }

    /**
     * Set internal cache value
     *
     * @param string $key - Origin of value
     * @param int|string $monthNumber - Month for which intermediate values were calculated
     * @param float $value
     * @return LoanCalculator
     */
    protected function setCached(string $key, int|string $monthNumber, float $value): static
    {
        $this->cache[$key][$monthNumber] = $value;

        return $this;
    }

    /**
     * Get internal cache value
     *
     * @param string $key - Origin of value
     * @param int|string $monthNumber - Month for which intermediate values were calculated
     *
     * @return float|bool
     */
    protected function getCached(string $key, int|string $monthNumber): float|bool
    {
        return $this->cache[$key][$monthNumber] ?? false;
    }

    /**
     * Check internal cache value
     *
     * @param string $key - Origin of value
     * @param int|string $monthNumber - Month for which intermediate values were calculated
     *
     * @return bool
     */
    protected function hasCached(string $key, int|string $monthNumber): bool
    {
        return isset($this->cache[$key][$monthNumber]);
    }

    /**
     * Clear internal cache
     */
    protected function clearCache(): static
    {
        $this->cache = [];

        return $this;
    }

    /**
     * Calculate the interest rate for one month.
     *
     * @param int $monthNumber - month number in period of loan repayment,
     * the first month is equivalent to the current month in the Gregorian calendar.
     * @return float
     */
    public function monthlyRate(int $monthNumber): float
    {
        if ($this->hasCached('monthlyRate', $monthNumber)) {
            return $this->getCached('monthlyRate', $monthNumber);
        }
        $monthTimestamp = strtotime(
            sprintf('+%s month', --$monthNumber),
            $this->getDateForFirstMonth()->getTimestamp()
        );
        $monthNumInYear = (int)date('n', $monthTimestamp);
        $yearForMonthNum = (int)date('Y', $monthTimestamp);

        $value = $this->getInterestRate()
            * cal_days_in_month(CAL_GREGORIAN, $monthNumInYear, $yearForMonthNum)
            / (365 + date('L', $monthTimestamp));
        $this->setCached('monthlyRate', $monthNumber, $value);

        return $value;
    }

    /**
     * Calculate the average monthly interest rate
     *
     * @return float
     */
    public function monthlyRateAverage(): float
    {
        return $this->getInterestRate() / 12;
    }

    /**
     * Calculate interest payment for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    public function interestDebtPayment(int $monthNumber): float
    {
        if ($this->hasCached('interestDebtPayment', $monthNumber)) {
            return $this->getCached('interestDebtPayment', $monthNumber);
        }
        $value = $this->monthlyLoanAmount($monthNumber - 1) * $this->monthlyRate($monthNumber) / 100;
        $this->setCached('interestDebtPayment', $monthNumber, $value);

        return $value;
    }

    /**
     * Calculate unpaid loan amount for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    abstract public function monthlyLoanAmount(int $monthNumber): float;

    /**
     * Calculate total payment for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    abstract protected function monthlyPayment(int $monthNumber): float;

    /**
     * Calculate payment on principal debt for the month by its number in repayment period
     *
     * @param int $monthNumber - month number in period of loan repayment
     * @return float
     */
    abstract protected function principalDebtPayment(int $monthNumber): float;

}
