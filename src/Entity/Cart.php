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
     * @var Store
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="products")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var User|null
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
     * @return User|null The user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user The user
     *
     * @return $this
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array The products
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products The products
     *
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return array An array of all the properties in an object
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'products' => $this->products,
        ];
    }
}
