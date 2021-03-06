<?php

namespace Gloudemans\Shoppingcart;

use Illuminate\Contracts\Support\Arrayable;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Contracts\Support\Jsonable;
use Money\Money;

class CartItem implements Arrayable, Jsonable
{

    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    public $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int|float
     */
    public $qty;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price without TAX of the cart item.
     *
     * @var Money
     */
    public $price;

    /**
     * The options for this cart item.
     *
     * @var array
     */
    public $options;

    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $associatedModel = null;

    /**
     * The tax rate for the cart item.
     *
     * @var int|float
     */
    private $taxRate = 0;

    /**
     * Is item saved for later.
     *
     * @var boolean
     */
    private $isSaved = false;

    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string $name
     * @param Money $price
     * @param array $options
     */
    public function __construct($id, $name, $price, array $options = [])
    {
        if (empty($id))
        {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($name))
        {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (!($price instanceof Money))
        {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->options = new CartItemOptions($options);
        $this->rowId = $this->generateRowId($id, $options);
    }

    /**
     * Returns price without TAX.
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * Returns price with TAX.
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function priceTax()
    {
        return $this->priceTax;
    }

    /**
     * Returns subtotal.
     * Subtotal is price for whole CartItem without TAX
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function subtotal()
    {
        return $this->subtotal;
    }

    /**
     * Returns total.
     * Total is price for whole CartItem with TAX
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Returns tax.
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function tax()
    {
        return $this->tax;
    }

    /**
     * Returns tax.
     * This method is still here for kinda BC
     *
     * @return Money
     */
    public function taxTotal()
    {
        return $this->taxTotal;
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || !is_numeric($qty))
        {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    /**
     * Update the cart item from a Buyable.
     *
     * @param \Gloudemans\Shoppingcart\Contracts\Buyable $item
     * @return void
     */
    public function updateFromBuyable(Buyable $item)
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
        $this->priceTax = $this->price->add($this->tax);
    }

    /**
     * Update the cart item from an array.
     *
     * @param array $attributes
     * @return void
     */
    public function updateFromArray(array $attributes)
    {
        $this->id = array_get($attributes, 'id', $this->id);
        $this->qty = array_get($attributes, 'qty', $this->qty);
        $this->name = array_get($attributes, 'name', $this->name);
        $this->price = array_get($attributes, 'price', $this->price);
        $this->priceTax = $this->price->add($this->tax);
        $this->options = new CartItemOptions(array_get($attributes, 'options', $this->options));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Associate the cart item with the given model.
     *
     * @param mixed $model
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function associate($model)
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param int|float $taxRate
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Set saved state.
     *
     * @param bool $bool
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function setSaved($bool)
    {
        $this->isSaved = $bool;

        return $this;
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute))
        {
            return $this->{$attribute};
        }

        if ($attribute === 'priceTax')
        {
            return $this->price->add($this->tax);
        }

        if ($attribute === 'subtotal')
        {
            return $this->price->multiply($this->qty);
        }

        if ($attribute === 'total')
        {
            return  $this->priceTax->multiply($this->qty);
        }

        if ($attribute === 'tax')
        {
            return $this->price->multiply($this->taxRate / 100);
        }

        if ($attribute === 'taxTotal')
        {
            return  $this->tax->multiply($this->qty);
        }

        if ($attribute === 'model' && isset($this->associatedModel))
        {
            return with(new $this->associatedModel)->find($this->id);
        }

        return null;
    }

    /**
     * Create a new instance from a Buyable.
     *
     * @param \Gloudemans\Shoppingcart\Contracts\Buyable $item
     * @param array $options
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromBuyable(Buyable $item, array $options = [])
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromArray(array $attributes)
    {
        $options = array_get($attributes, 'options', []);

        return new self($attributes['id'], $attributes['name'], $attributes['price'], $options);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string $name
     * @param Money $price
     * @param array $options
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromAttributes($id, $name, $price, array $options = [])
    {
        return new self($id, $name, $price, $options);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array $options
     * @return string
     */
    protected function generateRowId($id, array $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId'    => $this->rowId,
            'id'       => $this->id,
            'name'     => $this->name,
            'qty'      => $this->qty,
            'price'    => $this->price,
            'options'  => $this->options->toArray(),
            'tax'      => $this->tax,
            'isSaved'  => $this->isSaved,
            'subtotal' => $this->subtotal,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

}
