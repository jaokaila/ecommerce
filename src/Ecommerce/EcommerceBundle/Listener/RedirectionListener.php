<?php
	
	namespace Ecommerce\EcommerceBundle\Listener;

	use Symfony\Component\DependencyInjection\ContainerInterface;
	use Symfony\Component\HttpFoundation\Session\Session;
	use Symfony\Component\HttpFoundation\RedirectResponse;
	use Symfony\Component\HttpKernel\Event\GetResponseEvent;

	class RedirectionListener
	{
		public function _construct(ContainerInterface $container, Session $session)
		{
			$this->session = $session;
			$this->router = $container->get('router');
			$this->securityContext = $container->get('security.context');
		}

		public function onKernelRequest(GetResponseEvent $event)
		{
			$route= $event->getRequest()->attributes->get('_route');
			if($route=="livraison" || $route=="validation"){
				if($this->session->has('ecommerce_panier')){
					if(count($this->session->get('ecommerce_panier')==0))
						$event->setResponse(new RedirectResponse($this->router->generate('ecommerce_panier')));
				}
			
				if(!is_object($this->get('security.Context')->getToken()->getUser())){
					$this->session->getFlashBag()->add('notification','veuillez vous identifier');
					$event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
				}
			}
		}
	}