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
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


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

    // /**
    //  * @Security("has_role('ROLE_AUTEUR')")
    //  */
    public function addAction(Request $request){

        // if(!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR')){
        //     throw new AccessDeniedException('Accès limité aux auteurs.');
        // }
        
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();
        
                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
        
                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
            }
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
            'form'=>$form->createView()
        ));
    }

    public function editAction($id, Request $request){

        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if($advert == null){
            throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');
        }

        $form = $this->createForm(AdvertEditType::class,$advert);


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->flush();
        
                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
        
                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
            }
        }


        return $this->render('OCPlatformBundle:Advert:edit.html.twig',array(
            'advert' => $advert,
            'form'   => $form->createView()
        ));
    }

    public function deleteAction(Request $request, $id){
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

        $form = $this->get('form.factory')->create();

        if ($request-> isMethod('POST') && $form->handleRequest($request)->isValid()){
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimmée.');
            
            return $this->redirectToRoute('oc_platform_home');
        }
        
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
        ));
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

    public function testAction()
    {
      $advert = new Advert;
          
      $advert->setDate(new \Datetime());  // Champ « date » OK
      $advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
      //$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
      $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères
          
      // On récupère le service validator
      $validator = $this->get('validator');
          
      // On déclenche la validation sur notre object
      $listErrors = $validator->validate($advert);
  
      // Si $listErrors n'est pas vide, on affiche les erreurs
      if(count($listErrors) > 0) {
        // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
        return new Response((string) $listErrors);
      } else {
        return new Response("L'annonce est valide !");
      }
    }
}