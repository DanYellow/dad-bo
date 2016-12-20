<?php

namespace Admin\APIBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;

/**
 * User
 *
 * @ORM\Table(name="seller")
 * @ORM\Entity(repositoryClass="Admin\APIBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 *
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="pseudo", type="string", length=15, unique=true)
     */
    private $pseudo;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=50, nullable=true)
     */
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="ClassifiedAdvertisement", mappedBy="seller", cascade={"remove"})
     * 
     */
    private $classifiedAdvertisements;


    public function __construct()
    {
        parent::__construct();
        
        $this->classifiedAdvertisements = new ArrayCollection();
        $this->roles                    = ['ROLE_USER'];
        $this->enabled                  = true;
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

    /**
     * Add classifiedAdvertisements
     *
     * @param \Admin\APIBundle\Entity\ClassifiedAdvertisement $classifiedAdvertisements
     * @return User
     */
    public function addClassifiedAdvertisement(\Admin\APIBundle\Entity\ClassifiedAdvertisement $classifiedAdvertisements)
    {
        $this->classifiedAdvertisements[] = $classifiedAdvertisements;

        return $this;
    }

    /**
     * Remove classifiedAdvertisements
     *
     * @param \Admin\APIBundle\Entity\ClassifiedAdvertisement $classifiedAdvertisements
     */
    public function removeClassifiedAdvertisement(\Admin\APIBundle\Entity\ClassifiedAdvertisement $classifiedAdvertisements)
    {
        $this->classifiedAdvertisements->removeElement($classifiedAdvertisements);
    }

    /**
     * Get classifiedAdvertisements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClassifiedAdvertisements()
    {
        return $this->classifiedAdvertisements;
    }


    function getSerializableDatas() {
        return array(
          'id' => $this->getId(),
          'pseudo' => $this->getPseudo(),
          'location' => $this->getLocation(),
        );
    }

    /**
    * @ORM\PreRemove
    */
    public function deleteAllClassifiedAdvertisements()
    {
        $classifiedAdvertisements = $this->getClassifiedAdvertisements();

        foreach ($classifiedAdvertisements as $classifiedAdvertisement) {
            $this->classifiedAdvertisements->removeElement($classifiedAdvertisement);
            $classifiedAdvertisement->classifiedAdvertisement = null; //$lot->setTerrain(null); will be better, try to add getters and setters
        }
    }
}
