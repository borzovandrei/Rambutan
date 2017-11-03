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

        return $this->render('ShopBundle:Admin:index.html.twig', array());

    }


    public function addProductAction(Request $request)
    {
        $product = new Products();
        $product->setRating(0);
        $form = $this->createForm(ProductAddType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $product->upload();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("shop_homepage");
        }


        return $this->render("ShopBundle:Admin:add.html.twig", [
            'form_add_product' => $form->createView(),
        ]);
    }


    public function productEditAction($id, Request $request)
    {
        $em = $this->getDoctrine();
        $product = $em->getRepository("ShopBundle:Products")->find($id);
        if (!$product) {
            throw $this->createAccessDeniedException("Данного товара нет в магазине");
        }

        $form = $this->createForm(ProductAddType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $product->upload();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("shop_product", array('id' => $id));
        }

        return $this->render("ShopBundle:Admin:edit.html.twig", [
            'form_edit_blog' => $form->createView()
        ]);

    }

    public function user_roleAction(Request $request)
    {
        $user = new Users();
        $search = $request->query->get('search');
        $em = $this->getDoctrine();
        $user = $em->getRepository("ShopBundle:Users")->findBy(array('username' => $search));
        if (!$user) {
            $this->get('session')->getFlashBag()->add('user_role', 'Данный пользователь не найден.');
            return $this->redirect($this->generateUrl('shop_add'));
        }

        $newrole = $request->query->get('role');

        $role = $em->getRepository('ShopBundle:Role')->findBy(array('name' => $newrole));
        $user[0]->getUserRoles()->add($role[0]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user[0]);
        $em->flush();

        $this->get('session')->getFlashBag()->add('user_role', 'Пользователю получил права.');
        return $this->redirect($this->generateUrl('shop_add'));

    }

    public function order_statusAction(Request $request)
    {

        $order = $request->query->get('order');
        $status = $request->query->get('status');

        $em = $this->getDoctrine();
        $order = $em->getRepository("ShopBundle:Order")->find($order);
        $status = $em->getRepository("ShopBundle:StatusOrder")->find($status);
        if (!$order) {
            $this->get('session')->getFlashBag()->add('order_status', 'Данный заказ не найден.');
            return $this->redirect($this->generateUrl('shop_add'));
        }

        $order->setStatus($status);

        $message = \Swift_Message::newInstance()
            ->setSubject('Rambutan orders')
            ->setFrom('order@rambutan.com')
            ->setTo($order->getEmail())
            ->setBody($this->renderView('ShopBundle:Email:statusEmail.html.twig', array('enquiry' => $order)));
        $this->get('mailer')->send($message);

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $this->get('session')->getFlashBag()->add('order_status', 'Статус заказа изменен.');
        return $this->redirect($this->generateUrl('shop_add'));

    }

}