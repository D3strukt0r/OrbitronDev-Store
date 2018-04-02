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
     * @var \App\Entity\Product
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
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
