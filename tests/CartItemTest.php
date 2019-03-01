<?php

namespace Gloudemans\Tests\Shoppingcart;

use Money\Currency;
use Money\Money;
use Orchestra\Testbench\TestCase;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;

class CartItemTest extends TestCase
{

    /**
     * Set the package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    /** @test */
    public function it_can_be_cast_to_an_array()
    {
        $cartItem = new CartItem(1, 'Some item', new Money(1000, new Currency('USD')), ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertEquals([
                                'id'       => 1,
                                'name'     => 'Some item',
                                'price'    => new Money(1000, new Currency('USD')),
                                'rowId'    => '07d5da5550494c62daf9993cf954303f',
                                'qty'      => 2,
                                'options'  => [
                                    'size'  => 'XL',
                                    'color' => 'red',
                                ],
                                'tax'      => new Money(0, new Currency('USD')),
                                'subtotal' => new Money(2000, new Currency('USD')),
                                'isSaved'  => false,
                            ], $cartItem->toArray());
    }

    /** @test */
    public function it_can_be_cast_to_json()
    {
        $cartItem = new CartItem(1, 'Some item', new Money(1000, new Currency('USD')), ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertJson($cartItem->toJson());
        $json = '{"rowId":"07d5da5550494c62daf9993cf954303f","id":1,"name":"Some item","qty":2,"price":{"amount":"1000","currency":"USD"},"options":{"size":"XL","color":"red"},"tax":{"amount":"0","currency":"USD"},"isSaved":false,"subtotal":{"amount":"2000","currency":"USD"}}';

        $this->assertEquals($json, $cartItem->toJson());
    }
}
