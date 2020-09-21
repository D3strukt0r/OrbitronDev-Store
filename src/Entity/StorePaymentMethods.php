<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_payment_methods")
 */
class StorePaymentMethods
{
    public const TYPE_BRAINTREE_PRODUCTION = 'braintree_production';
    public const TYPE_BRAINTREE_SANDBOX = 'braintree_sandbox';

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Store
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="paymentMethods")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $payment_type;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $data;

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
        return $this->payment_type;
    }

    /**
     * @param string $type The type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->payment_type = $type;

        return $this;
    }

    /**
     * @return array The data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data The data
     *
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
