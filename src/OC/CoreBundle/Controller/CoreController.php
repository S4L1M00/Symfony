<?php 

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CoreController extends Controller
{
    public function indexAction(Request $request){

        return $this->render("OCCoreBundle:Core:index.html.twig");
    }

    public function contactAction(Request $request){

        $request->getSession()->getFlashBag()->add('info','La page de contact n’est pas encore disponible');
        
        return $this->RedirectToRoute("oc_core_home");
    }
}