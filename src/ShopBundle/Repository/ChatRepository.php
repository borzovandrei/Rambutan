<?php

namespace ShopBundle\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * ChatRepository
 */
class ChatRepository extends EntityRepository
{

    //сообщения данной комнаты
    public function getChat($chat){
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.chatroom = :id_room')
            ->addOrderBy('c.date')
            ->setParameter('id_room', $chat);
        return $qb->getQuery()->getResult();

    }


    public function findByNot($field, $value)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->not($qb->expr()->eq('a.'.$field, '?1')));
        $qb->setParameter(1, $value);

        return $qb->getQuery()->getResult();
    }

}