<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_vouchers")
 */
class Voucher
{
    public const TYPE_PERCENTAGE = 0;
    public const TYPE_EXACT = 1;

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
     * @ORM\Column(type="text")
     */
    protected $code;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $type;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $amount;

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
     * @return string The code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code The code
     *
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int The type
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type The type
     *
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int The amount
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount The amount
     *
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
