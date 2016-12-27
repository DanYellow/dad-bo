<?php

namespace Admin\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="Admin\APIBundle\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug_name", type="string", length=50, unique=true)
     */
    private $slugName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ClassifiedAdvertisement", mappedBy="category", cascade={"persist", "merge"})
     */
    private $classifiedAdvertisements;


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
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slugName
     *
     * @param string $slugName
     * @return Category
     */
    public function setSlugName($slugName)
    {
        $this->slugName = $slugName;

        return $this;
    }

    /**
     * Get slugName
     *
     * @return string 
     */
    public function getSlugName()
    {
        return $this->slugName;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classifiedAdvertisements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add classifiedAdvertisements
     *
     * @param \Admin\APIBundle\Entity\ClassifiedAdvertisement $classifiedAdvertisements
     * @return Category
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
    public function getClassifiedAdvertisements($areActive = false)
    {
        if ($areActive === 0) {
            return $this->classifiedAdvertisements;
        } else {
            $filteredCAs = array_filter($this->classifiedAdvertisements->toArray(), 
                                function ($classifiedAdvertisement) {
                                    return $classifiedAdvertisement->getIsActive();
            });

            return $filteredCAs;
        }
    }


    /**
     * Return all serializables datas to avoid circular references
     * @return Array              
     */
    public function getSerializableDatas() {
        return array(
            'id'        => $this->getId(),
            'name'      => $this->getName(),
            'slug_name' => $this->getSlugName(),
            'nb_items'  => count($this->getClassifiedAdvertisements(true)),
        );
    }
}
