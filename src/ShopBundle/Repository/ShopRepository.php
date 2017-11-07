<?php


namespace ShopBundle\Repository;

;
use Doctrine\ORM\Query\Expr\Join;
use ShopBundle\Entity\Products;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;


class ShopRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @var ContainerAwareInterface
     */
    private $container;

    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getRecomend($sort, $sum)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->where('s.id_class = :sort')
            ->addOrderBy('s.rating', 'DESC')
            ->setParameter('sort', $sort)
            ->setMaxResults($sum);
        return $qb->getQuery()->getResult();
    }


    public function getSort(array $idProd)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id')
            ->innerJoin(Products::class, 'p', Join::WITH, "p.id_class = s")
            ->where('p.id in (:idProd)')
            ->setParameter('idProd', $idProd);
        return array_column($qb->getQuery()->getScalarResult(), 'id');
    }




    public function getTop(array $idSort, array $lim)
    {
        foreach ($idSort as $key=>$value){
            $sort[]=(int)$value;
        }

        $limit = $lim["count" . count($sort)];

        $sql=$this->getSQL("p",$sort[0],$limit[0]);
        for ($i=1; $i<count($sort); $i++){
            $sql.=$this->getUnion("p".$i,$sort[$i],$limit[$i]);
        }

        $r = $this->getEntityManager()->getConnection()->prepare($sql);
        $r->execute();
        $r = array_column($r->fetchAll(), 'id');
        foreach ($r as $key=>$value){
            $data[]=(int)$value;
        };
        return $data;
    }



    private function getSQL($x1,$x2,$x3){
        $sql = "
                SELECT ".$x1.".id FROM (SELECT p.* FROM products p
                WHERE p.sort_id = ".$x2."
                ORDER BY RAND()
                  LIMIT ".$x3."
                ) ".$x1."
              ";
        return $sql;
    }

    private function getUnion($x1,$x2,$x3){
        $sql = "
                 UNION
                 SELECT ".$x1.".id
        
                 FROM (
                     SELECT p.* FROM products p
                     WHERE p.sort_id = ".$x2."
                     ORDER BY RAND()
                     LIMIT ".$x3."
                 ) ".$x1."
              ";
        return $sql;
    }

}