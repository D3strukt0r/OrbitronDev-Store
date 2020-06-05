<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_product_images")
 */
class ProductImages
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Product
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="images")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $image;

    /**
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Product The product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product The product
     *
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return string The image
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image The image
     *
     * @return $this
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
