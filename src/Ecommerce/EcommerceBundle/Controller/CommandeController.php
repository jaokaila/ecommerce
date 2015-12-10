<?php
namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ecommerce\EcommerceBundle\Entity\categories;
use Ecommerce\EcommerceBundle\Entity\Media;
use Ecommerce\EcommerceBundle\Entity\Produits;
use Ecommerce\EcommerceBundle\Entity\Tva;
use Doctrine\ORM\EntityRepository;
use  Ecommerce\EcommerceBundle\Listener\RedirectionListener;
use Symfony\component\HttpFoundation\RedirectResponse;
use Ecommerce\EcommerceBundle\Form\UtilisateursAdressesType;
use Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses;
use Ecommerce\UtilisateurBundle\Entity\Utilisateur;
use Ecommerce\EcommerceBundle\Entity\Commandes;
use Symfony\component\HttpFoundation\Response;

class CommandeController extends Controller
{

	public function facture()
	{
		$em=$this->getDoctrine()->getManager();
		$generator= $this->container->get('security.secure_random');
		$session= $this->getRequest()->getSession();
		$adresse=$session->get('adresse');
		$panier=$session->get('ecommerce_panier');
		$commande= array();
		$totalHT=0;
		$totalTTC=0;

		$facturation=$em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['facturation']);
		$livraison=$em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['livraison']);
		$produits=$em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($session->get('ecommerce_panier')));
		foreach($produits as $produit)
		{
			
			$prixHT= ($produit->getPrix()*$panier[$produit->getId()]);
			$prixTTC= ($produit->getPrix()*$panier[$produit->getId()]) /$produit->getTva()->getMultiplicate();
			$totalHT += $prixHT;
			$totalTTC += $prixTTC;

			if(!isset($commande['tva']['%'.$produit->getTva()->getValeur()]))
				$commande['tva']['%'.$produit->getTva()->getValeur()] =round($prixTTC - $prixHT,2);
			else
				$commande['tva']['%'.$produit->getTva()->getValeur()] +=round($prixTTC - $prixHT,2);

			$commande['produit'][$produit->getId()]=array(
					'reference'	=> $produit->getName(),
					'quantite'	=> $panier[$produit->getId()],
					'prixHT'	=> round($produit->getPrix(),2),
					'prixTTC'	=> round($produit->getPrix()+$produit->getPrix()/ $produit->getTva()->getMultiplicate(),2)
				);
		}
			$commande['livraison']=array(
					'prenom'	=>$livraison->getPrenom(),
					'nom'		=>$livraison->getNom(),
					'telephone'	=>$livraison->getTelephone(),
					'adresse'	=>$livraison->getAdresse(),
					'cp'		=>$livraison->getCp(),
					'ville'		=>$livraison->getVille(),
					'pays'		=>$livraison->getPays(),
					'complement'=>$livraison->getComplement(),
				);
			$commande['facturation']=array(
					'prenom'	=>$facturation->getPrenom(),
					'nom'		=>$facturation->getNom(),
					'telephone'	=>$facturation->getTelephone(),
					'adresse'	=>$facturation->getAdresse(),
					'cp'		=>$facturation->getCp(),
					'ville'		=>$facturation->getVille(),
					'pays'		=>$facturation->getPays(),
					'complement'=>$facturation->getComplement(),
				);
			$commande['prixHT']=round($totalHT,2);
			$commande['prixTTC']=round($totalTTC,2);
			$commande['token']=bin2hex($generator->nextBytes(20));

			return $commande;
		

	}

	public function preparecommandeAction()
	{
		$session=$this->getRequest()->getSession();
		$em=$this->getDoctrine()->getManager();
		if(!$session->has('commande')){
			$commande= new Commandes();
			$commande->setDate(new \DateTime());
			$commande->setUtilisateur($this->container->get('security.Context')->getToken()->getUser());
			$commande->setValider(0);
			$commande->setReference(0);
			$commande->setCommande($this->facture());
			$em->persist($commande);
			$session->set('commande', $commande);
		}
		else
			$commande=$em->getRepository('EcommerceBundle:Commandes')->find($session->get('commande'));
		//$commande= new Commandes();
		

		/*if(!$session->has('commande')){
			$em->persist($commande);
			$session->set('commande', $commande);
		}
*/
		$em->flush();
		//return $this->render("EcommerceBundle:Public:commande.html.twig");
		return new Response($commande->getId());
	}

	public function validationcommandeAction($id)
	{
		$em=$this->getDoctrine()->getManager();
		$session=$this->getRequest()->getSession();
		$commande=$em->getRepository('EcommerceBundle:Commandes')->find($id);

		//if(!$commande || $commande->getValider()==1)
		//	throw $this->createNotFoundException('commande inexistant');

		$commande->setValider(1);
		//$commande->setReference($this->container->get('setNewReference')->reference());
		$commande->setReference(1);
		$em->flush();

		
		$session->remove('adresse');
		$session->remove('ecommerce_panier');
		$session->remove('commande');

		$this->get('session')->getFlashBag()->add('success','votre commande est validé avec succées');

		return $this->redirect($this->generateUrl('ecommerce_homepage'));

	}
}