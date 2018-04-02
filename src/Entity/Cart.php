<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_carts")
 */
class Cart
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Entity\Store
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="products")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var \App\Entity\User|null
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $products;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param \App\Entity\Store $store
     *
     * @return $this
     */
    public function setStore(Store $store): self
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return \App\Entity\User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User|null $user
     *
     * @return $this
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'user'     => $this->user,
            'products' => $this->products,
        ];
    }
}
