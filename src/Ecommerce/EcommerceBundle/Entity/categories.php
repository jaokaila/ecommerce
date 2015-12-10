<?php

namespace Ecommerce\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * categories
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ecommerce\EcommerceBundle\Repository\categoriesRepository")
 */
class categories
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Ecommerce\EcommerceBundle\Entity\Media", mappedBy="categorie", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $images;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;


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
     * Set nom
     *
     * @param string $nom
     *
     * @return categories
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set images
     *
     * @param \Ecommerce\EcommerceBundle\Entity\Media $images
     *
     * @return categories
     */
    public function setImages(\Ecommerce\EcommerceBundle\Entity\Media $images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return \Ecommerce\EcommerceBundle\Entity\Media
     */
    public function getImages()
    {
        return $this->images;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add image
     *
     * @param \Ecommerce\EcommerceBundle\Entity\Media $image
     *
     * @return categories
     */
    public function addImage(\Ecommerce\EcommerceBundle\Entity\Media $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \Ecommerce\EcommerceBundle\Entity\Media $image
     */
    public function removeImage(\Ecommerce\EcommerceBundle\Entity\Media $image)
    {
        $this->images->removeElement($image);
    }
}
