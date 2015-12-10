<?php
namespace Utilisateurs\UtilisateurBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
/**
 * @ORM\Entity
 * @ORM\Table(name="utilisateur")
 */
class Utilisateur extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

   

     /**
     *
     *@ORM\OneToMany(targetEntity="Ecommerce\EcommerceBundle\Entity\Commandes",mappedBy="utilisateur", cascade={"persist", "remove"})
     *@ORM\JoinColumn(nullable=true)
     */
    private $commandes;

    /**
     *
     *@ORM\OneToMany(targetEntity="Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses",mappedBy="utilisateur", cascade={"persist", "remove"})
     *@ORM\JoinColumn(nullable=true)
     */
    private $adresses;

     public function __construct()
    {
       parent::__construct();
       //$this->commandes= new \Doctrine\Common\Collections\ArrayCollections();
       //$this->adresses= new \Doctrine\Common\Collections\ArrayCollections();
    }

    /**
     * Add commande
     *
     * @param \Ecommerce\EcommerceBundle\Entity\Commandes $commande
     *
     * @return Utilisateur
     */
    public function addCommande(\Ecommerce\EcommerceBundle\Entity\Commandes $commande)
    {
        $this->commandes[] = $commande;

        return $this;
    }

    /**
     * Remove commande
     *
     * @param \Ecommerce\EcommerceBundle\Entity\Commandes $commande
     */
    public function removeCommande(\Ecommerce\EcommerceBundle\Entity\Commandes $commande)
    {
        $this->commandes->removeElement($commande);
    }

    /**
     * Get commandes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommandes()
    {
        return $this->commandes;
    }

    /**
     * Add adress
     *
     * @param \Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses $adress
     *
     * @return Utilisateur
     */
    public function addAdress(\Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses $adress)
    {
        $this->adresses[] = $adress;

        return $this;
    }

    /**
     * Remove adress
     *
     * @param \Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses $adress
     */
    public function removeAdress(\Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses $adress)
    {
        $this->adresses->removeElement($adress);
    }

    /**
     * Get adresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdresses()
    {
        return $this->adresses;
    }
}
