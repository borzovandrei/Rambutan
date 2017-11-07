<?php

namespace ShopBundle\Controller;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use ShopBundle\Entity\Likes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $cardId = $request->cookies->get('PHPSESSID');

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

        $cardId = $request->cookies->get('PHPSESSID');

        $dataOld = $redis->get("cart_{$cardId}");
        $dataOld = json_decode($dataOld, true);

        foreach ($dataOld['products'] as $key => $p) {
            if ($p['id'] == $id) {
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

        $cardId = $request->cookies->get('PHPSESSID');

        $dataOld = $redis->get("cart_{$cardId}");
        $dataOld = json_decode($dataOld, true);

        foreach ($dataOld['products'] as $key => $p) {
            if ($p['id'] == $id) {
                unset($dataOld['products'][$key]);
            }
        }

        $redis->set("cart_{$cardId}", json_encode($dataOld));
        return new JsonResponse($dataOld);
    }

    //лайк
    public function likeAction(Request $request)
    {
        $id = $request->get("id");
        $author = $request->get("author");
        $likes = $request->get("likes");

        $em = $this->getDoctrine()->getManager();
        $like = $em->getRepository('ShopBundle:Likes')->findBy(array('author' => $author, 'product'=>$id));

        if (!$like){
            $like1 = new Likes();
            $author = $em->getRepository('ShopBundle:Users')->find($author);
            $id = $em->getRepository('ShopBundle:Products')->find($id);
            $like1->setProduct($id);
            $like1->setAuthor($author);
            $like1->setLikes($likes);
            $em->persist($like1);
            $em->flush();
            if ($likes==0){
                $data = "Дислайк!";
            }else{
                $data = "Лайк!";
            }
        }else{
            $data = "Вы уже оценивали продукт!";
        }
        return new Response($data);
    }


    public function productAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //лайки
        $like = $em->getRepository('ShopBundle:Likes')->findBy(array('product'=>$id,'likes'=>1));
        $dislike = $em->getRepository('ShopBundle:Likes')->findBy(array('product'=>$id,'likes'=>0));
        $like_count=count($like)-count($dislike);


        $product = $em->getRepository('ShopBundle:Products')->find($id);

        //просмотренные товары в редис
        $redis = $this->get('snc_redis.default');
        $historyId = $request->cookies->get('PHPSESSID');

        $OldHistory = $redis->lrange("history_{$historyId}", 0, -1);

        if ($redis->llen("history_{$historyId}") <= 5) {
            $redis->lpush("history_{$historyId}", $product->getId());
        } else {
            $redis->rpop("history_{$historyId}");
            $redis->lpush("history_{$historyId}", $product->getId());
        }


        foreach ($OldHistory as $key => $p) {
            if ($p == $product->getId()) {
                $redis->lrem("history_{$historyId}", 0, $product->getId());
                $redis->lpush("history_{$historyId}", $product->getId());
            }
        }
        if ($OldHistory == null) {
            $data[] = $em->getRepository('ShopBundle:Products')->find($product->getId());
        }

        //просмотренные товары из редиса
        foreach ($OldHistory as $key => $p) {
            $data[] = $em->getRepository('ShopBundle:Products')->find($p);
        }

        //похожее на просмотренное
        $redis->lpush("similar_{$historyId}", $product->getId());//положить в редис открытый товар
        $similar = $redis->lrange("similar_{$historyId}", 0, -1);//просматриваем все просмотренные
        $similar = array_count_values($similar);//собирает товары
        arsort($similar);// сортирует по убыванию
        $similar = array_flip($similar);//меняем ключ-значение местами
        $similar = array_slice($similar, 0, 5);//оставляем топ 5 просматриваемых

        $data_two = $em->getRepository('ShopBundle:Sort')->getSort($similar); //массив категорий
        $data_three = $em->getRepository('ShopBundle:Products')->getTop($data_two, $this->getParameter('view_more')); //массив id продуктов рекомендаций
//        $data_three =  $this->get('shop_bundle.repository.shop')->getTop($data_two); //массив id продуктов рекомендаций

        foreach ($data_three as $key => $p) {
            $recomend[] = $em->getRepository('ShopBundle:Products')->find($p);
        }

        if (!$product) {
            throw $this->createNotFoundException('Не удалось найти товар.');
        }
        $comments = $em->getRepository('ShopBundle:Comment')
            ->getCommentsForBlog($product->getId());

        return $this->render('ShopBundle:Default:product.html.twig', array(
            'like'=>$like_count,
            'product' => $product,
            'comments' => $comments,
            'data' => $data,
            'recomend' => $recomend,
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

    public function invoiceAction(Request $request)
    {
        $id = $request->get("id");
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('ShopBundle:Order')->find($id);
        $orderitem = $em->getRepository('ShopBundle:OrderItem')->findBy(array('orderprod' => $order->getOderitem()));
        foreach ($orderitem as $key => $value) {
            $products[] = $em->getRepository('ShopBundle:Products')->find($orderitem[$key]->getItem());
            $sum[] = $orderitem[$key]->getSum();
        }

        $html = $this->renderView('ShopBundle:Default:invoice.html.twig', array(
            'order' => $order,
            'products' => $products,
            'sum' => $sum,
            'rootDir' => $this->get('kernel')->getRootDir() . '/..'
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'Order.pdf'
        );


    }

}
