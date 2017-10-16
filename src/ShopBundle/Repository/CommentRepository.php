<?php

namespace ShopBundle\Repository;

use Doctrine\ORM\EntityRepository;


class CommentRepository extends EntityRepository
{
    public function getCommentsForBlog($productId, $approved = true)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.product = :products_id')
            ->addOrderBy('c.created')
            ->setParameter('products_id', $productId);

        if (false === is_null($approved))
            $qb->andWhere('c.approved = :approved')
                ->setParameter('approved', $approved);

        return $qb->getQuery()
            ->getResult();
    }

}