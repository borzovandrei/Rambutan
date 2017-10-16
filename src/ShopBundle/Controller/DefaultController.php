<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShopBundle:Default:index.html.twig');
    }


    public function productAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('ShopBundle:Products')->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Не удалось найти товар.');
        }

        $comments = $em->getRepository('ShopBundle:Comment')
            ->getCommentsForBlog($product->getId());

//        $redis = $this->container->get('snc_redis.default');
//        var_dump($redis);

        return $this->render('ShopBundle:Default:product.html.twig', array(
            'product' => $product,
            'comments'  => $comments
        ));
    }


    public function navigationAction($id = NULL)
    {
        $em = $this->getDoctrine()->getManager();
        $sort = $em->getRepository('ShopBundle:Sort')->findAll();
        if (!$id) {
            $sortname = "Продукты";
            $product = $em->getRepository('ShopBundle:Products')->findAll();
        } else {
            $product = $em->getRepository('ShopBundle:Products')->findBy(array('id_class' => $id));
            $sortname = $em->getRepository('ShopBundle:Sort')->find($id);
        }
        return $this->render('ShopBundle:Default:navigation.html.twig', array(
            'sort' => $sort,
            'product' => $product,
            'sortname' => $sortname,
        ));
    }

}
