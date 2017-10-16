<?php

namespace ShopBundle\Controller;


use ShopBundle\Entity\Products;
use ShopBundle\Form\ProductAddType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShopBundle:Admin:index.html.twig');
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
            'form_add_product'=> $form -> createView()
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


}