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
use Symfony\component\HttpFoundation\Response;

class PublicController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$session= $this->getRequest()->getSession();
    	$produits=$em->getRepository('EcommerceBundle:Produits')->findAll();
		
		if($session->has('ecommerce_panier'))
    		$panier=$session->get('ecommerce_panier');
    	else
    		$panier=false;

        return $this->render('EcommerceBundle:Public:index.html.twig', array(
        		'produits' => $produits,
        		'panier' => $panier,
        	));
    }

    public function produitAction()
    {
    	$session= $this->getRequest()->getSession();
    	$id= $this->getRequest()->query->get('id');
    	$categorie= $this->getRequest()->query->get('cat');
    	$em = $this->getDoctrine()->getManager();
    	$detail=$em->getRepository('EcommerceBundle:Produits')->findById($id);
    	$categories= $em->getRepository('EcommerceBundle:Produits')->myfind($categorie);
    	if($session->has('ecommerce_panier'))
    		$panier=$session->get('ecommerce_panier');
    	else
    		$panier=false;

    	return $this->render('EcommerceBundle:Public:produit.html.twig', array(
    			'detail' => $detail,
    			'categories' => $categories,
    			'panier' => $panier,
    		));
    }

    public function panierAction()
    {
    	$session= $this->getRequest()->getSession();
    	//$session->remove('ecommerce_panier');
    	//die();
    	if(!$session->has('ecommerce_panier')) $session->set('ecommerce_panier', array());
    	$em = $this->getDoctrine()->getManager();
    	$produits=$em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($session->get('ecommerce_panier')));

    	
    	return $this->render('EcommerceBundle:Public:panier.html.twig', array(
    			'produits' => $produits,
    			'panier' => $session->get('ecommerce_panier'),
    		));
    }

   

    public function categorieAction()
    {
    	return $this->render('EcommerceBundle:Public:categorie.html.twig');
    }

    public function ajouterAction($id)
    {
		$session= $this->getRequest()->getSession();
		if(!$session->has('ecommerce_panier')) $session->set('ecommerce_panier', array());
		$panier= $session ->get('ecommerce_panier');

		if(array_key_exists($id, $panier)){
		 if($this->getRequest()->query->get('qte') != null)
		 	$panier[$id]= $this->getRequest()->query->get('qte');
		 }else {
		 	if($this->getRequest()->query->get('qte') != null)
		 		$panier[$id]= $this->getRequest()->query->get('qte');
	
		 else
		 	$panier[$id]=1;
		}

		$session-> set('ecommerce_panier', $panier);

  		return $this->redirect($this->generateUrl('ecommerce_panier'));
    }

    public function supprimerAction($id)
    {
    	$session= $this->getRequest()->getSession();
    	$panier= $session ->get('ecommerce_panier');
    	if(array_key_exists($id, $panier)){
    		unset($panier[$id]);
    		$session-> set('ecommerce_panier', $panier);
    	}
    	return $this->redirect($this->generateUrl('ecommerce_panier'));
    }

     public function contactAction()
    {
    	return $this->render('EcommerceBundle:Public:contact.html.twig');
    }

    public function par_categorieAction(){
    	$session= $this->getRequest()->getSession();
    	$categorie= $this->getRequest()->query->get('cat');
    	$em= $this->getDoctrine()->getManager();
    	$produits= $em->getRepository('EcommerceBundle:Produits')->myfind($categorie);
    	if($session->has('ecommerce_panier'))
    		$panier=$session->get('ecommerce_panier');
    	else
    		$panier=false;
    	return $this->render('EcommerceBundle:Public:par_categorie.html.twig', array(
    			'produits' => $produits,
    			'panier' =>$panier,
    		));
    }

    public function menuAction(){

    	$session= $this->getRequest()->getSession();
        if(!is_object($this->get('security.Context')->getToken()->getUser()))
            $etat= false;
        else
            $etat=true;
    	if(!$session-> has('ecommerce_panier'))
    		$article=0;
    	else
    		$article=count($session->get('ecommerce_panier'));
    	$em= $this->getDoctrine()->getManager();
    	$menu= $em->getRepository('EcommerceBundle:categories')->findAll();
    	return $this->render('EcommerceBundle::layout.html.twig', array(
    			'menus'=>$menu,
    			'article' => $article,
                'etat'  =>$etat,
    		));
    }

    public function livraisonAction(){
    	if(!is_object($this->get('security.Context')->getToken()->getUser()))
    		return  $this->redirect($this->generateUrl('fos_user_security_login'));
    	else{

    		$utilisateur= $this->get('security.Context')->getToken()->getUser();
    		$entity=new UtilisateursAdresses();
    		$form= $this->createForm(new UtilisateursAdressesType(), $entity);
    		$request=$this->getRequest();
    		if($request->getMethod()=='POST')
    		{
    				
    				$em=$this->getDoctrine()->getManager();
    				$form->submit($request);
    				$entity=$form->getData();
    				$entity->setUtilisateur($utilisateur);
    				$em->persist($entity);
    				$em->flush();
    				return $this->redirect($this->generateUrl('ecommerce_livraison'));
    			
    		}

    		return $this->render('EcommerceBundle:Public:livraison.html.twig', array(
    				'form' =>$form->createView(),
    				'utilisateur' => $utilisateur
    			));
    	}
    }

    public function suppressionAction($id){

    	$em=$this->getDoctrine()->getManager();
    	$entity=$em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($id);
    	if($this->get('security.Context')->getToken()->getUser()!=$entity->getUtilisateur() || !$entity)
    		return $this->redirect($this->generateUrl('ecommerce_livraison'));
    	$em->remove($entity);
    	$em->flush();
    	return $this->redirect($this->generateUrl('ecommerce_livraison'));
    }

    public function setLivraison()
    {
    	$session= $this->getRequest()->getSession();
    	if (!$session->has('adresse'))
    		$session->set('adresse', array());
    	$adresse=$session->get('adresse');
    	if($this->getRequest()->request->get('livraison') != null && $this->getRequest()->request->get('facturation')!=null)
    	{
    		$adresse['livraison']=$this->getRequest()->request->get('livraison');
    		$adresse['facturation']=$this->getRequest()->request->get('facturation');
    	}else{
    		return $this->redirect($this->generateUrl('ecommerce_validation'));
    	}
    	$session->set('adresse', $adresse);
    	return $this->redirect($this->generateUrl('ecommerce_validation'));
    }

    public function validationAction()
    {
    	
    	if($this->get('request')->getMethod()=='POST')
    		$this->setLivraison();
    	$em=$this->getDoctrine()->getManager();
    	$preparecommande= $this->forward('EcommerceBundle:Commande:preparecommande');
    	$commande=$em->getRepository('EcommerceBundle:Commandes')->find($preparecommande->getContent());

    	/*$em=$this->getDoctrine()->getManager();
    	$session= $this->getRequest()->getSession();
    	$adresse= $session->get('adresse');
    	$produits=$em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($session->get('ecommerce_panier')));
    	$livraison=$em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['livraison']);
    	$facturation=$em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['facturation']);

    	return $this->render('EcommerceBundle:Public:validation.html.twig', array(
    			'produits'	=> $produits,
    			'livraison'	=> $livraison,
    			'facturation'=> $facturation,
    			'panier'	=> $session->get('ecommerce_panier'),
    		));*/
		//var_dump($commande);
		//die();
		return $this->render('EcommerceBundle:Public:validation.html.twig', array('commande'=>$commande));

    }

    //insertion dans la base pour faire des simulations
    public function testAction(){
    	$categorie1 = new categories();
    	$categorie1->setNom('habillement');

    	$categorie2 = new categories();
    	$categorie2->setNom('chaussure');

    	$categorie3 = new categories();
    	$categorie3->setNom('divers');

    	$image1 = new media();
    	$image1-> setPath('http://media.laredoute.com/products2/250by250/9/4/5/324519650_0_PR_1_11867833_324519650-c1f4d0b2-3896-4055-8e19-8c0ea327e3d8_1200.jpg');
    	$image1-> setAlt('doudoune addidas');
    	$image1-> setCategorie($categorie1);

    	$image2 = new media();
    	$image2-> setPath('http://media.laredoute.com/products2/250by250/f/c/6/324519882_0_PR_1_11917923_324519882-272bf954-1b58-4922-98fd-144436194c6b_1200.jpg');
    	$image2->setAlt('polo homme addidas');
    	$image2-> setCategorie($categorie1);

    	$image3 = new media();
    	$image3-> setPath('http://media.laredoute.com/products2/250by250/d/2/9/324520993_0_PR_1_11840045_324520993-40a9b2a2-85ca-4ee7-854c-6cac1ec56e7c_1200.jpg');
    	$image3-> setAlt('chaussettes nike');
    	$image3-> setCategorie($categorie3);

    	$image4 = new media();
    	$image4-> setPath('http://i1.wp.com/lemag.laredoute.fr/wp-content/uploads/2015/11/71.jpg?resize=315%2C463');
    	$image4-> setAlt('tenis');
    	$image4-> setCategorie($categorie2);

    	$image5 = new media();
    	$image5-> setPath('http://i2.wp.com/lemag.laredoute.fr/wp-content/uploads/2015/11/11.jpg?resize=315%2C463');
    	$image5-> setAlt('chaussure');
    	$image5-> setCategorie($categorie2);

    	$image6 = new media();
    	$image6-> setPath('http://media.laredoute.com/products2/250by250/0/9/8/324496856_0_PR_1_10162159_324496856-e57f7d54-4e7a-4491-879b-15a89429a8d0_1200.jpg');
    	$image6-> setAlt('chaussure homme');
    	$image6-> setCategorie($categorie2);

    	$image7= new media();
    	$image7-> setPath('http://media.laredoute.com/products2/250by250/c/3/2/350010253_0_PR_1_11968291_350010253-9931fe97-b184-45a2-8e62-2af2837b83d7_1200.jpg');
    	$image7-> setAlt('robe');
    	$image7-> setCategorie($categorie1);

    	$image8= new media();
    	$image8-> setPath('http://media.laredoute.com/products2/250by250/2/1/e/324519305_0_PR_1_10791886_324519305-7c75a344-6ec4-4e84-a001-abeb968f7bc0_1200.jpg');
    	$image8-> setAlt('coiffure');
    	$image8-> setCategorie($categorie3);

    	$image9 = new media();
    	$image9-> setPath('http://media.laredoute.com/products2/250by250/c/1/9/324525289_0_PR_1_11961226_324525289-000ee2db-1932-405f-a64c-bbfa8fe43e52_1200.jpg');
    	$image9-> setAlt('pochette');
    	$image9-> setCategorie($categorie3);

    	$tva = new tva();
    	$tva->setMultiplicate(0.2);
    	$tva->setNom('tva');
    	$tva->setValeur(0.2);

    	$produit1 = new produits();
    	$produit1-> setImages($image1);
    	$produit1-> setCategorie($categorie1);
    	$produit1-> setName('doudoune');
    	$produit1-> setDescription('Doudoune matelassée de sport bicolore');
    	$produit1-> setPrix(199.20);
    	$produit1-> setDisponible(true);
    	$produit1-> setTva($tva);
    	$produit2 = new produits();
    	$produit2-> setImages($image2);
    	$produit2-> setCategorie($categorie1);
    	$produit2-> setName('polo homme');
    	$produit2-> setDescription('Addidas polo homme');
    	$produit2-> setPrix(29.20);
    	$produit2-> setDisponible(true);
    	$produit2-> setTva($tva);

    	$produit3 = new produits();
    	$produit3-> setImages($image3);
    	$produit3-> setCategorie($categorie3);
    	$produit3-> setName('chaussettes');
    	$produit3-> setDescription('Chaussettes nike (lot de 3 paires)');
    	$produit3-> setPrix('12.00');
    	$produit3-> setDisponible(true);
    	$produit3-> setTva($tva);

    	$produit4 = new produits();
    	$produit4-> setImages($image4);
    	$produit4-> setCategorie($categorie2);
    	$produit4-> setName('tenis');
    	$produit4-> setDescription('Baskets à paillette dorés');
    	$produit4-> setPrix('40.00');
    	$produit4-> setDisponible(true);
    	$produit4-> setTva($tva);

    	$produit5 = new produits();
    	$produit5-> setImages($image5);
    	$produit5-> setCategorie($categorie2);
    	$produit5-> setName('chaussure');
    	$produit5-> setDescription('escarpin');
    	$produit5-> setPrix(40.00);
    	$produit5-> setDisponible(true);
    	$produit5-> setTva($tva);

    	$produit6 = new produits();
    	$produit6-> setImages($image6);
    	$produit6-> setCategorie($categorie2);
    	$produit6-> setName('chaussure');
    	$produit6-> setDescription('Bottines montantes cuir vachette');
    	$produit6-> setPrix(100.00);
    	$produit6-> setDisponible(true);
    	$produit6-> setTva($tva);

    	$produit7 = new produits();
    	$produit7-> setImages($image7);
    	$produit7-> setCategorie($categorie1);
    	$produit7-> setName('Robe');
    	$produit7-> setDescription('Robe dentelle forme trapèze');
    	$produit7-> setPrix(39.99);
    	$produit7-> setDisponible(true);
    	$produit7-> setTva($tva);

    	$produit8 = new produits();
    	$produit8-> setImages($image8);
    	$produit8-> setCategorie($categorie3);
    	$produit8-> setName('coiffure');
    	$produit8-> setDescription('Materiel de coiffure');
    	$produit8-> setPrix(39.99);
    	$produit8-> setDisponible(true);
    	$produit8-> setTva($tva);

    	$produit9 = new produits();
    	$produit9-> setImages($image9);
    	$produit9-> setCategorie($categorie3);
    	$produit9-> setName('pochette');
    	$produit9-> setDescription('pochette');
    	$produit9-> setPrix(39.99);
    	$produit9-> setDisponible(true);
    	$produit9-> setTva($tva);

    	$em = $this->getDoctrine()->getManager();
    	$em->persist($categorie1);
    	$em->persist($categorie2);
    	$em->persist($categorie3);

    	$em->persist($image1);
    	$em->persist($image2);
    	$em->persist($image3);
    	$em->persist($image4);
    	$em->persist($image5);
    	$em->persist($image6);
    	$em->persist($image7);
    	$em->persist($image8);
    	$em->persist($image9);

    	$em->persist($tva);

    	$em->persist($produit1);
    	$em->persist($produit2);
    	$em->persist($produit3);
    	$em->persist($produit4);
    	$em->persist($produit5);
    	$em->persist($produit6);
    	$em->persist($produit7);
    	$em->persist($produit9);
    	$em->persist($produit8);

    	$em->flush();

    	return $this->render("EcommerceBundle:Public:test.html.twig");
    }
}