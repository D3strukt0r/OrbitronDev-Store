<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_orders")
 */
class Order
{
    const STATUS_NOT_PROCESSED = 0;
    const STATUS_IN_PRODUCTION = 1;
    const STATUS_SENT = 2;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Store
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $street;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $zip_code;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $city;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $country;

    /**
     * @var DeliveryType
     * @ORM\ManyToOne(targetEntity="DeliveryType")
     * @ORM\JoinColumn(name="delivery_type_id", referencedColumnName="id", nullable=false)
     */
    protected $delivery_type;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $product_list;

    /**
     * @var int
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $status = 0;

    /**
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Store The store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param Store $store The store
     *
     * @return $this
     */
    public function setStore(Store $store): self
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name The name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string The email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email The email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string The phone
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone The phone
     *
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string The street
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street The street
     *
     * @return $this
     */
    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string The zip code
     */
    public function getZipCode(): string
    {
        return $this->zip_code;
    }

    /**
     * @param string $zip_code The zip code
     *
     * @return $this
     */
    public function setZipCode(string $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    /**
     * @return string The city
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city The city
     *
     * @return $this
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string The country
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country The country
     *
     * @return $this
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return DeliveryType The delivery type
     */
    public function getDeliveryType(): DeliveryType
    {
        return $this->delivery_type;
    }

    /**
     * @param DeliveryType $delivery_type The delivery type
     *
     * @return $this
     */
    public function setDeliveryType(DeliveryType $delivery_type): self
    {
        $this->delivery_type = $delivery_type;

        return $this;
    }

    /**
     * @return array The product list
     */
    public function getProductList(): array
    {
        return $this->product_list;
    }

    /**
     * @param array $product_list The product list
     *
     * @return $this
     */
    public function setProductList(array $product_list): self
    {
        $this->product_list = $product_list;

        return $this;
    }

    /**
     * @return int The status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status The status
     *
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
