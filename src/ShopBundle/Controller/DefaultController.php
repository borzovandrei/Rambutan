<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShopBundle:Default:index.html.twig');
    }


    public function cartAction(Request $request)
    {
        $request = Request::createFromGlobals();
        $data = $request->get("cart");
        $redis = $this->get('snc_redis.default');

        $cardId =$request->cookies->get('PHPSESSID');

         if ($dataOld = $redis->get("cart_{$cardId}")) {
             $dataOld = json_decode($dataOld, true);
             $data['products'] = array_merge($data['products'], $dataOld['products']);
         }


        $redis->set("cart_{$cardId}", json_encode($data));
        return new JsonResponse($data);
    }


    public function minusCartAction(Request $request)
    {
        $request = Request::createFromGlobals();
        $id = $request->get("id");
        $redis = $this->get('snc_redis.default');

        $cardId =$request->cookies->get('PHPSESSID');

        $dataOld = $redis->get("cart_{$cardId}");
        $dataOld = json_decode($dataOld, true);

        foreach ($dataOld['products'] as $key => $p) {
             if($p['id'] == $id){
                 unset($dataOld['products'][$key]);
                 break;
             }
         }

        $redis->set("cart_{$cardId}", json_encode($dataOld));
        return new JsonResponse($dataOld);
    }



    public function delCartAction(Request $request)
    {
        $request = Request::createFromGlobals();
        $id = $request->get("id");
        $redis = $this->get('snc_redis.default');

        $cardId =$request->cookies->get('PHPSESSID');

        $dataOld = $redis->get("cart_{$cardId}");
        $dataOld = json_decode($dataOld, true);

        foreach ($dataOld['products'] as $key => $p) {
            if($p['id'] == $id){
                unset($dataOld['products'][$key]);
            }
        }

        $redis->set("cart_{$cardId}", json_encode($dataOld));
        return new JsonResponse($dataOld);
    }





    public function productAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('ShopBundle:Products')->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Не удалось найти товар.');
        }
        $comments = $em->getRepository('ShopBundle:Comment')
            ->getCommentsForBlog($product->getId());

        return $this->render('ShopBundle:Default:product.html.twig', array(
            'product' => $product,
            'comments' => $comments
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
