<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_delivery_types")
 */
class DeliveryType
{
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
    protected $type;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var float
     * @ORM\Column(type="decimal")
     */
    protected $price;

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
     * @return string The type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type The type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null The description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description The description
     *
     * @return $this
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float The price
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price The price
     *
     * @return $this
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
