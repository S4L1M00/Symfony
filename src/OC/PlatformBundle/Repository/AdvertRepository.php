<?php

namespace OC\PlatformBundle\Repository;


use Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends \Doctrine\ORM\EntityRepository
{

    public function getAdvertWithCategories(array $categoryNames){
        
        $qb = $this->createQueryBuilder('a')
                   ->innerJoin('a.categories','c')
                   ->addSelect('c');

        $qb->expr()->in('c.name',$categoryNames);

        return $qb->getQuery()
                  ->getResult();
    }

    public function getAdverts($page,$nbPerPage){
        $qb = $this->createQueryBuilder('a')
                   ->leftJoin('a.categories', 'c')
                     ->addSelect('c')
                   ->leftJoin('a.image','img')
                     ->addSelect('img')
                   ->orderBy('a.date','DESC');

        $qb->setFirstResult(($page-1) * $nbPerPage)
           ->setMaxResults($nbPerPage);
             

        return new Paginator($qb, true);
    }


    public function getAllAdvertsDesc(){
        $qb = $this->createQueryBuilder('a')
                   ->orderBy('a.date', 'DESC');

        return $qb->getQuery()
                  ->getResult();
    }
}
