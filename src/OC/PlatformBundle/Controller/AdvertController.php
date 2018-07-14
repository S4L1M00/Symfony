<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\Exception;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;

class AdvertController extends Controller
{
    public function indexAction($page){

        $page = ($page == "") ? 1 : $page;
        if($page < 1){
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }


        $nbPerPage = 3;

        $listAdverts = $this->getDoctrine()->getRepository('OCPlatformBundle:Advert')->getAdverts($page,$nbPerPage);

        $nbPages =  ceil(count($listAdverts)/$nbPerPage);


        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'page' => $page,
            'nbPages' => $nbPages
        ));
    }

    public function viewAction($id){

        $em = $this->getDoctrine()->getManager();
        

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if($advert == null){
            throw new Exception('L\'annnonce d\'id :'.$id.' n\'existe pas');
        }

        $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));

        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));

        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert'=>$advert,
            'listApplications'=>$listApplications,
            'listAdvertSkills'=>$listAdvertSkills
        ));
    }

    public function addAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')){

            $request->getSession()->getFlashBag()->add('notice','Annonce bien enregistrée');

            return $this->redirectToRoute('oc_platform_view',array('id'=>$advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }

    public function editAction($id, Request $request){

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod('POST')){

            $request->getSession()->getFlashBag()->add('notice','Annonce bien modifiée');
            return $this->redirectToRoute('oc_platform_view',array('id'=>$îd));
        }

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if($advert == null){
            throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');
        }

        $em->persist($advert);
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig',array('advert' => $advert));
    }

    public function deleteAction($id){
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        foreach ($advert->getCategories() as $category){
            $advert->removeCategory($category);
        }

        foreach($advert->getApplications() as $application){
            // $advert->removeApplication($application);
            $em->remove($application);
        }

        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));
        foreach ($listAdvertSkills as $advertSkill){
            $em->remove($advertSkill);
        }

        $em->remove($advert);

        $em->flush();

        $listAdverts = $this->getDoctrine()->getRepository('OCPlatformBundle:Advert')->findAll();
        
        return $this->render('OCPlatformBundle:Advert:index.html.twig',array('listAdverts'=>$listAdverts));
    }

    public function menuAction($limit){

        $listAdverts = $this->getDoctrine()->getRepository('OCPlatformBundle:Advert')->findBy(
            array(),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
        );
      
        

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }
}