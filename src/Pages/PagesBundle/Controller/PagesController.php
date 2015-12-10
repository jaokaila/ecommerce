<?php

namespace Pages\PagesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagesController extends Controller
{
    public function pagesAction()
    {
        return $this->render('PagesBundle:Pages:pages.html.twig');
    }
}
