<?php

namespace Admin\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Admin\APIBundle\Entity\User as User;

use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups({"list", "details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({"list", "details"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"list", "details"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="isActive", type="boolean")
     * @Groups({"list", "details"})
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=2, scale=2, nullable=true)
     * @Groups({"list", "details"})
     */
    private $price;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     * @Groups({"list", "details"})
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * 
     */
    private $image;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdate", type="datetime", nullable=true)
     * @Groups({"list", "details"})
     */
    private $lastUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     * @Groups({"list", "details"})
     */
    private $slug;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="classifiedAdvertisements")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Groups({"list", "details"})
     */
    private $seller;

    public function __construct()
    {
        $this->createdAt   = new \DateTime();
        $this->isActive    = true;
        $this->description = "";
        $this->slug        = null;
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
        $this->title = $title;

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
        $this->description = $description;

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
        $this->price = $price;

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
     * Set image
     *
     * @param string $image
     * @return ClassifiedAdvertisement
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
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
        $this->seller = $seller;

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
}
