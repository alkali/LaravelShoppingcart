<?php

namespace Gloudemans\Tests\Shoppingcart\Fixtures;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Money\Currency;
use Money\Money;

class BuyableProduct implements Buyable
{

    /**
     * @var int|string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Money
     */
    private $price;

    /**
     * BuyableProduct constructor.
     *
     * @param int|string $id
     * @param string $name
     * @param Money $price
     */
    public function __construct($id = 1, $name = 'Item name', $price = null)
    {
        if (is_null($price))
        {
            $price = new Money(1000, new Currency('USD'));
        }
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null)
    {
        return $this->id;
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription($options = null)
    {
        return $this->name;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @return Money
     */
    public function getBuyablePrice($options = null)
    {
        return $this->price;
    }
}