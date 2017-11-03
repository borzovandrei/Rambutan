<?php


namespace ShopBundle\Repository;

;
use Doctrine\ORM\Query\Expr\Join;
use ShopBundle\Entity\Products;


class ShopRepository extends \Doctrine\ORM\EntityRepository
{

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




    public function getTop(array $idSort)
    {
        foreach ($idSort as $key=>$value){
            $sort[]=(int)$value;
        }

        switch (count($sort)) {
            case 1:
                $limit=array(5);
                break;
            case 2:
                $limit=array(3,2);
                break;
            case 3:
                $limit=array(3,1,1);
                break;
            case 4:
                $limit=array(2,1,1,1);
                break;
            case 5:
                $limit=array(1,1,1,1,1);
                break;
        }

//        $limit = $this->container->getParameter("view_more.count" . count($sort));

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