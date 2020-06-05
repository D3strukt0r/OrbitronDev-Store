<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_product_files")
 */
class ProductFile
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="files")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $file;

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
     * @return string The file
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file The file
     *
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
