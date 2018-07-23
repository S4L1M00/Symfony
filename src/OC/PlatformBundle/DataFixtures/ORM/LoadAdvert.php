<?php
namespace OC\PlatformBundle\DataFixtures\ORM;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Category;
use OC\PlatformBundle\Entity\Application;
class LoadAdvert implements FixtureInterface
{
  public function load(ObjectManager $manager)
  {
    for ($i=0; $i <15 ; $i++) { 
      
      $advert = new Advert();
      $advert->setTitle('Titre '.$i);
      $advert->setAuthor('Auteur'.$i.'@symfony.com');
      $advert->setContent('Contenu numero '.$i);
      
      $manager->persist($advert);
      
      for ($j=0; $j < 2 ; $j++) { 
        $application = new Application();
        $application->setAuthor('Application '.$j);
        $application->setContent('Contenu '.$j.' de l\'annonce '.$i);
  
        $advert->addApplication($application);
        $manager->persist($application);
      
      }
    }
    // On déclenche l'enregistrement de toutes les compétences
    $manager->flush();
  }
}