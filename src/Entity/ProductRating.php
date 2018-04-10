<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_product_rating")
 */
class ProductRating
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Entity\Product
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="ratings")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $rating;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $comment;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $approved = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $spam = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created_on;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updated_on;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param \App\Entity\Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return \App\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     *
     * @return $this
     */
    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     *
     * @return $this
     */
    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSpam(): bool
    {
        return $this->spam;
    }

    /**
     * @param bool $spam
     *
     * @return $this
     */
    public function setSpam(bool $spam): self
    {
        $this->spam = $spam;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn(): \DateTime
    {
        return $this->created_on;
    }

    /**
     * @param \DateTime $createdOn
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $createdOn): self
    {
        $this->created_on = $createdOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn(): \DateTime
    {
        return $this->updated_on;
    }

    /**
     * @param \DateTime $updated_on
     *
     * @return $this
     */
    public function setUpdatedOn(\DateTime $updated_on): self
    {
        $this->updated_on = $updated_on;

        return $this;
    }
}
