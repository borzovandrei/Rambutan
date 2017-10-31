<?php


namespace ShopBundle\Repository;

class ShopRepository extends \Doctrine\ORM\EntityRepository
{

    public function getRecomend($chat){
        $qb = $this->createQueryBuilder('c')
//            ->select('c')
//            ->where('c.chatroom = :id_room')
//            ->addOrderBy('c.date')
//            ->setParameter('id_room', $chat)
        ;
        return $qb->getQuery()->getResult();

    }

}