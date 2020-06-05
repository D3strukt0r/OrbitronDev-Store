<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_products")
 */
class Product
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
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    protected $owner;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $description;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $short_description;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $price;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $price_sale;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $small_icon;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="ProductImages",
     *     mappedBy="product",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $images;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $downloadable = false;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="ProductFile", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected $stock = 0;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $last_edited;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $closed = false;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected $rating_count = 0;

    /**
     * @var float
     * @ORM\Column(type="decimal", options={"default": 0})
     */
    protected $rating_average = 0;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="ProductRating",
     *     mappedBy="product",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $ratings;

    public function __construct()
    {
        $this->name = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->short_description = new ArrayCollection();
        $this->price = new ArrayCollection();
        $this->price_sale = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->last_edited = new DateTime();
    }

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
     * @return User The owner
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner The owner
     *
     * @return $this
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return string|null The name
     */
    public function getName(string $language): ?string
    {
        $array = ($this->name instanceof ArrayCollection ? $this->name : new ArrayCollection($this->name));
        if ($array->containsKey($language)) {
            return $array->get($language);
        }
        if ($array->containsKey($this->getDefaultLanguage())) {
            return $array->get($this->getDefaultLanguage());
        }

        return null;
    }

    /**
     * @return array The names
     */
    public function getNames(): array
    {
        return $this->name;
    }

    /**
     * @param string $name     The name
     * @param string $language The language
     *
     * @return $this
     */
    public function setName(string $name, string $language): self
    {
        $array = ($this->name instanceof ArrayCollection ? $this->name : new ArrayCollection($this->name));
        $array->set($language, $name);
        $this->name = $array->toArray();

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return $this
     */
    public function removeName(string $language): self
    {
        $array = ($this->name instanceof ArrayCollection ? $this->name : new ArrayCollection($this->name));
        if ($array->containsKey($language)) {
            $array->remove($language);
            $this->name = $array->toArray();
        }

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return string|null The description
     */
    public function getDescription(string $language): ?string
    {
        $array = ($this->description instanceof ArrayCollection ? $this->description : new ArrayCollection(
            $this->description
        ));
        if ($array->containsKey($language)) {
            return $array->get($language);
        }
        if ($array->containsKey($this->getDefaultLanguage())) {
            return $array->get($this->getDefaultLanguage());
        }

        return null;
    }

    /**
     * @return array The descriptions
     */
    public function getDescriptions(): array
    {
        return $this->description;
    }

    /**
     * @param string $description The description
     * @param string $language    The language
     *
     * @return $this
     */
    public function setDescription(string $description, string $language): self
    {
        $array = ($this->description instanceof ArrayCollection ? $this->description : new ArrayCollection(
            $this->description
        ));
        $array->set($language, $description);
        $this->description = $array->toArray();

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return $this
     */
    public function removeDescription(string $language): self
    {
        $array = ($this->description instanceof ArrayCollection ? $this->description : new ArrayCollection(
            $this->description
        ));
        if ($array->containsKey($language)) {
            $array->remove($language);
            $this->description = $array->toArray();
        }

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return string|null The short description
     */
    public function getShortDescription(string $language): ?string
    {
        $array = ($this->short_description instanceof ArrayCollection ? $this->short_description : new ArrayCollection(
            $this->short_description
        ));
        if ($array->containsKey($language)) {
            return $array->get($language);
        }
        if ($array->containsKey($this->getDefaultLanguage())) {
            return $array->get($this->getDefaultLanguage());
        }

        return null;
    }

    /**
     * @return array An array of the short descriptions
     */
    public function getShortDescriptions(): array
    {
        return $this->short_description;
    }

    /**
     * @param string $description The description
     * @param string $language    The language
     *
     * @return $this
     */
    public function setShortDescription(string $description, string $language): self
    {
        $array = ($this->short_description instanceof ArrayCollection ? $this->short_description : new ArrayCollection(
            $this->short_description
        ));
        $array->set($language, $description);
        $this->short_description = $array->toArray();

        return $this;
    }

    /**
     * @param string $language The language
     *
     * @return $this
     */
    public function removeShortDescription(string $language): self
    {
        $array = ($this->short_description instanceof ArrayCollection ? $this->short_description : new ArrayCollection(
            $this->short_description
        ));
        if ($array->containsKey($language)) {
            $array->remove($language);
            $this->short_description = $array->toArray();
        }

        return $this;
    }

    /**
     * @param string $currency The currency
     *
     * @return string|null The price
     */
    public function getPrice(string $currency): ?string
    {
        $array = ($this->price instanceof ArrayCollection ? $this->price : new ArrayCollection($this->price));
        if ($array->containsKey($currency)) {
            return $array->get($currency);
        }
        if ($array->containsKey($this->getDefaultCurrency())) {
            return $array->get($this->getDefaultCurrency());
        }

        return null;
    }

    /**
     * @return array The prices
     */
    public function getPrices(): array
    {
        return $this->price;
    }

    /**
     * @param float  $price    The price
     * @param string $currency The currency
     *
     * @return $this
     */
    public function setPrice(float $price, string $currency): self
    {
        $array = ($this->price instanceof ArrayCollection ? $this->price : new ArrayCollection($this->price));
        $array->set($currency, $price);
        $this->price = $array->toArray();

        return $this;
    }

    /**
     * @param string $currency The currency
     *
     * @return $this
     */
    public function removePrice(string $currency): self
    {
        $array = ($this->price instanceof ArrayCollection ? $this->price : new ArrayCollection($this->price));
        if ($array->containsKey($currency)) {
            $array->remove($currency);
            $this->price = $array->toArray();
        }

        return $this;
    }

    /**
     * @param string $currency The currency
     *
     * @return string|null The sale price
     */
    public function getSalePrice(string $currency): ?string
    {
        $array = ($this->price_sale instanceof ArrayCollection ? $this->price_sale : new ArrayCollection(
            $this->price_sale
        ));
        if ($array->containsKey($currency)) {
            return $array->get($currency);
        }
        if ($array->containsKey($this->getDefaultCurrency())) {
            return $array->get($this->getDefaultCurrency());
        }

        return null;
    }

    /**
     * @return array The sale prices
     */
    public function getSalePrices(): array
    {
        return is_array($this->price_sale) ? $this->price_sale : [];
    }

    /**
     * @param string $currency The currency
     *
     * @return bool Whether the product is in sale
     */
    public function isInSale(string $currency): bool
    {
        if (null !== $this->price_sale) {
            $array = ($this->price_sale instanceof ArrayCollection ? $this->price_sale : new ArrayCollection(
                $this->price_sale
            ));
            if ($array->containsKey($currency)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param float  $price    The price
     * @param string $currency The currency
     *
     * @return $this
     */
    public function setSalePrice(float $price, string $currency): self
    {
        $array = ($this->price_sale instanceof ArrayCollection ? $this->price_sale : new ArrayCollection(
            $this->price_sale
        ));
        $array->set($currency, $price);
        $this->price_sale = $array->toArray();

        return $this;
    }

    /**
     * @param string $currency The currency
     *
     * @return $this
     */
    public function removeSalePrice(string $currency): self
    {
        $array = ($this->price_sale instanceof ArrayCollection ? $this->price_sale : new ArrayCollection(
            $this->price_sale
        ));
        if ($array->containsKey($currency)) {
            $array->remove($currency);
            $this->price_sale = $array->toArray();
        }

        return $this;
    }

    /**
     * @return string|null The small icon url
     */
    public function getSmallIcon(): ?string
    {
        return $this->small_icon;
    }

    /**
     * @param string|null $small_icon The small icon url
     *
     * @return $this
     */
    public function setSmallIcon(string $small_icon = null): self
    {
        $this->small_icon = $small_icon;

        return $this;
    }

    /**
     * @return ProductImages[] The product images
     */
    public function getImages(): array
    {
        return $this->images->toArray();
    }

    /**
     * @param ProductImages $image The product image
     *
     * @return $this
     */
    public function addImage(ProductImages $image): self
    {
        $this->images->add($image);
        $image->setProduct($this);

        return $this;
    }

    /**
     * @param ProductImages $image The product image
     *
     * @return $this
     */
    public function removeImage(ProductImages $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    /**
     * @return bool Whether it's a downloadable file
     */
    public function getDownloadable(): bool
    {
        return $this->downloadable;
    }

    /**
     * @param bool $downloadable Whether it's a downloadable file
     *
     * @return $this
     */
    public function setDownloadable(bool $downloadable): self
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * @return ProductFile[] The files
     */
    public function getFiles(): array
    {
        return $this->files->toArray();
    }

    /**
     * @param ProductFile $file The files
     *
     * @return $this
     */
    public function addFile(ProductFile $file): self
    {
        $this->images->add($file);
        $file->setProduct($this);

        return $this;
    }

    /**
     * @param ProductFile $file The files
     *
     * @return $this
     */
    public function removeFile(ProductFile $file): self
    {
        if ($this->images->contains($file)) {
            $this->images->removeElement($file);
        }

        return $this;
    }

    /**
     * @return int The available stock
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock The available stock
     *
     * @return $this
     */
    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return DateTime The last time the product was edited
     */
    public function getLastEdited(): DateTime
    {
        return $this->last_edited;
    }

    /**
     * @param DateTime $lastEdited The last time the product was edited
     *
     * @return $this
     */
    public function setLastEdited(DateTime $lastEdited): self
    {
        $this->last_edited = $lastEdited;

        return $this;
    }

    /**
     * @return bool Whether the product was closed
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @param bool $closed Whether the product was closed
     *
     * @return $this
     */
    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * @return int The rating
     */
    public function getRatingCount(): int
    {
        return $this->rating_count;
    }

    /**
     * @param int $ratingCount The rating
     *
     * @return $this
     */
    public function setRatingCount(int $ratingCount): self
    {
        $this->rating_count = $ratingCount;

        return $this;
    }

    /**
     * @return float The rating average
     */
    public function getRatingAverage(): float
    {
        return $this->rating_average;
    }

    /**
     * @param float $ratingAverage The rating average
     *
     * @return $this
     */
    public function setRatingAverage(float $ratingAverage): self
    {
        $this->rating_average = $ratingAverage;

        return $this;
    }

    /**
     * @return ProductRating[] The ratings
     */
    public function getRatings(): array
    {
        return $this->ratings->toArray();
    }

    /**
     * @param ProductRating $rating The rating
     *
     * @return $this
     */
    public function addRating(ProductRating $rating): self
    {
        $this->images->add($rating);
        $rating->setProduct($this);

        return $this;
    }

    /**
     * @param ProductRating $rating The rating
     *
     * @return $this
     */
    public function removeRating(ProductRating $rating): self
    {
        if ($this->images->contains($rating)) {
            $this->images->removeElement($rating);
        }

        return $this;
    }

    /**
     * @return string The default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->store->getDefaultLanguage();
    }

    /**
     * @return string The default currency
     */
    public function getDefaultCurrency(): string
    {
        return $this->store->getDefaultCurrency();
    }

    /**
     * @return array An array of all the properties in an object
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store' => $this->store,
            'owner' => $this->owner,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'price_sale' => $this->price_sale,
            'small_icon' => $this->small_icon,
            'images' => $this->images,
            'downloadable' => $this->downloadable,
            'files' => $this->files,
            'stock' => $this->stock,
            'last_edited' => $this->last_edited,
            'closed' => $this->closed,
            'rating_count' => $this->rating_count,
            'rating_average' => $this->rating_average,
            'ratings' => $this->ratings,
        ];
    }
}
