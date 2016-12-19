<?php

namespace Admin\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Admin\APIBundle\Entity\User as User;
use Admin\APIBundle\Entity\Category as Category;
use Admin\APIBundle\Entity\ClassifiedAdvertisementImage as ClassifiedAdvertisementImage;


use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClassifiedAdvertisement
 *
 * @ORM\Table(name="classified_advertisement")
 * @ORM\Entity(repositoryClass="Admin\APIBundle\Repository\ClassifiedAdvertisementRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * 
 */
class ClassifiedAdvertisement
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $price;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     */
    private $lastUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="classifiedAdvertisements")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="classifiedAdvertisements")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $seller;

    /**
     * 
     * @ORM\OneToOne(targetEntity="ClassifiedAdvertisementImage", cascade={"remove"})
     */
    private $image;

    public function __construct()
    {
        $this->createdAt   = new \DateTime();
        $this->lastUpdate  = new \DateTime();
        $this->isActive    = true;
        $this->description = '';
        $this->slug        = null;
        $this->price       = 0;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ClassifiedAdvertisement
     */
    public function setTitle($title)
    {
        if ($title !== null) {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ClassifiedAdvertisement
     */
    public function setDescription($description)
    {
        if ($description !== null) {
            $this->description = $description;
        }

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return ClassifiedAdvertisement
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return ClassifiedAdvertisement
     */
    public function setPrice($price)
    {
        $this->price = ($this->price !== null ? $price : 0);
        $this->price = ($this->price > 0 ? $price : 0);

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ClassifiedAdvertisement
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return ClassifiedAdvertisement
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return ClassifiedAdvertisement
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set seller
     *
     * @param \Admin\APIBundle\Entity\User $seller
     * @return ClassifiedAdvertisement
     */
    public function setSeller(\Admin\APIBundle\Entity\User $seller)
    {
        if ($seller !== null) {
            $this->seller = $seller;
        }

        return $this;
    }

    /**
     * Get seller
     *
     * @return \Admin\APIBundle\Entity\User 
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        $this->setLastUpdate(new \DateTime());
    }

    /**
     * Return all serializables datas to avoid circular ref
     * @param  Array $currentUser current user's serializables datas
     * @return Array              
     */
    public function getSerializableDatas($isMine = false) {
        $lastUpdate = null;
        if ($this->getLastUpdate()) {
          $lastUpdate = $this->getLastUpdate()->format('Y-m-d H:i:s');
        }

        $category = null;
        if ($this->getCategory()) {
            $category = $this->getCategory()->getSerializableDatas();
        }

        return array(
            'id'          => $this->getId(),
            'title'       => $this->getTitle(),
            'description' => $this->getDescription(),
            'price'       => (int)$this->getPrice(),
            'created_at'  => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'last_update' => $lastUpdate,
            'is_active'   => $this->getIsActive(),
            'category'    => $category,
            'is_mine'     => $isMine,
            'seller'      => $this->getSeller()->getSerializableDatas(),
        );
    }

    /**
     * Set category
     *
     * @param \Admin\APIBundle\Entity\Category $category
     * @return ClassifiedAdvertisement
     */
    public function setCategory(\Admin\APIBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Admin\APIBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set image
     *
     * @param \Admin\APIBundle\Entity\ClassifiedAdvertisementImage $image
     * @return ClassifiedAdvertisement
     */
    public function setImage(\Admin\APIBundle\Entity\ClassifiedAdvertisementImage $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Admin\APIBundle\Entity\ClassifiedAdvertisementImage 
     */
    public function getImage()
    {
        return $this->image;
    }
}
