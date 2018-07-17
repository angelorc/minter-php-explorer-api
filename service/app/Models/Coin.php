<?php
namespace App\Models;

use BI\BigInteger;


/**
 * Class Coin
 * @package App\Models
 */
class Coin
{
    /**
     * PIP coefficient
     */
    public const PIP = 10 ** -18;
    public const PIP_STR = '0.000000000000000001';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $pipAmount;

    /**
     * Coin constructor.
     * @param $name
     * @param $pipAmount
     */
    public function __construct(string $name, string $pipAmount)
    {
        $this->name = mb_strtolower($name);
        $this->pipAmount = $pipAmount;
    }

    /**
     * @return String
     */
    public function getName(): String
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return bcmul($this->pipAmount, $this::PIP_STR, 18);
    }

    /**
     * @return float
     */
    public function getUsdAmount(): float
    {
        //TODO: перенести конвертацию в сервис, как будет понятно откуда брать курс
        return $this->getAmount() * 0.000075;
    }
}