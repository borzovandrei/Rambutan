<?php

namespace ShopBundle\Controller;


use ShopBundle\Entity\Products;
use ShopBundle\Entity\Users;
use ShopBundle\Form\ProductAddType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function indexAction()
    {

        return $this->render('ShopBundle:Admin:index.html.twig',array(

    ));

    }


    public function addProductAction(Request $request)
    {
        $product = new Products();
        $form = $this->createForm(ProductAddType::class, $product );
        $form -> handleRequest($request);

        if ($form->isSubmitted() &&  $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $product->upload();
            $em -> persist($product);
            $em->flush();
            return $this->redirectToRoute("shop_homepage");
        }


        return $this->render("ShopBundle:Admin:add.html.twig", [
            'form_add_product'=> $form -> createView(),
        ]);
    }





    public function productEditAction($id, Request $request){
        $em = $this->getDoctrine();
        $product = $em->getRepository("ShopBundle:Products")->find($id);
        if(!$product){
            throw $this->createAccessDeniedException("Данного товара нет в магазине");
        }

        $form = $this->createForm(ProductAddType::class, $product );
        $form -> handleRequest($request);
        if ($form->isSubmitted() &&  $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $product->upload();
            $em -> persist($product);
            $em->flush();
            return $this->redirectToRoute("shop_product", array('id' => $id));
        }

        return $this->render("ShopBundle:Admin:edit.html.twig", [
            'form_edit_blog'=> $form -> createView()
        ]);

    }
    public function user_roleAction(Request $request)
    {
        $user = new Users();
        $search = $request->query->get('search');
        $em = $this->getDoctrine();
        $user = $em->getRepository("ShopBundle:Users")->findBy(array('username' => $search ));

        $newrole = $request->query->get('role');

        $role = $em->getRepository('ShopBundle:Role')->findBy(array('name' => $newrole ));
        $user[0]->getUserRoles()->add($role[0]);

        $em = $this->getDoctrine()->getManager();
        $em -> persist($user[0]);
        $em->flush();
        dump($user,$newrole, $role);die();


        return $this->render('ShopBundle:Admin:index.html.twig');

    }

    public function order_statusAction(Request $request)
    {

        $order = $request->query->get('order');
        $status = $request->query->get('status');

        $em = $this->getDoctrine();
        $order = $em->getRepository("ShopBundle:Order")->find($order);
        $status = $em->getRepository("ShopBundle:StatusOrder")->find($status);

        $order->setStatus($status);

        $em = $this->getDoctrine()->getManager();
        $em -> persist($order);
        $em->flush();
        dump($order, $status);die();


        return $this->render('ShopBundle:Admin:index.html.twig');

    }

}