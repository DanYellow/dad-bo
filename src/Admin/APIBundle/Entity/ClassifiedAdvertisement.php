<?php

namespace Admin\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Admin\APIBundle\Entity\User as User;
use Admin\APIBundle\Entity\Category as Category;


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
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     * 
     */
    private $path;

    /**
     * 
     * @Assert\Image(
     *          mimeTypes = {"image/jpeg", "image/jpg", "image/png", "image/gif"},
     *          mimeTypesMessage = "Ce format n'est pas autorisé. Seul les images au format .jp(e)g, .png et .gif sont autorisés",
     *          maxSize = "6M", 
     *          maxSizeMessage = "Ce fichier est trop lourd ({{ size }}). La taille maximum autorisée est de : {{ limit }}"
     * )
     */
    private $file;

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
     * @var string
     *
     * @ORM\Column(name="image_id", type="string", length=255, nullable=true)
     */
    private $imageId;

    public function __construct()
    {
        $this->createdAt   = new \DateTime();
        $this->lastUpdate  = new \DateTime();
        $this->isActive    = true;
        $this->description = "";
        $this->slug        = null;
        $this->price       = 0;
        $this->imageId       = uniqid();
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
        // if (null !== $this->getFile()) {
        //     // do whatever you want to generate a unique name
        //     $filename = $this->getFile()->getClientOriginalName() . '-' . sha1(uniqid(mt_rand(), true));
        //     $this->path = $filename.'.'.$this->getFile()->guessExtension();
        // }
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



    // Upload management
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads';
    }


    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        $finalFileName = null;
        $filename = null;

        // $filename = pathinfo($this->getFile()->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $this->getImageId();

        $finalFileName = $filename . '-' . rand(0, 10000) . '.' . $this->getFile()->guessExtension();

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $finalFileName
        );

        // set the path property to the filename where you've saved the file
        $this->path = $finalFileName;
  
        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return ClassifiedAdvertisement
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets file
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }  

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set imageId
     *
     * @param string $imageId
     * @return ClassifiedAdvertisement
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return string 
     */
    public function getImageId()
    {
        return $this->imageId;
    }
}
