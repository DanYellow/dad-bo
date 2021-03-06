<?php

namespace Admin\APIBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;

/**
 * User
 *
 * @ORM\Table(name="seller")
 * @ORM\Entity(repositoryClass="Admin\APIBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="pseudo", type="string", length=15, unique=true)
     * @Expose
     */
    private $pseudo;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=50, nullable=true)
     * @Expose
     */
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="ClassifiedAdvertisement", mappedBy="seller")
     * @Expose
     * @Groups({"Profile"})
     */
    private $classifiedAdvertisements;


    public function __construct()
    {
        parent::__construct();
        
        $this->classifiedAdvertisements = new ArrayCollection();
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
     * Set pseudo
     *
     * @param string $pseudo
     * @return User
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @return string 
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return User
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        $this->email = sha1($this->email);
        $this->pseudo = $this->username;
    }
}
