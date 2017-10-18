<?php
namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ShopBundle\Entity\Comment;
use ShopBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\User;

class CommentController extends Controller
{
    public function newAction($product_id)
    {
        $product = $this->getProduct($product_id);

        $comment = new Comment();
        $comment->setProduct($product);
        $form   = $this->createForm(CommentType::class, $comment);

        return $this->render('ShopBundle:Comment:comment.html.twig', array(
            'comment' => $comment,
            'form' => $form->createView()
        ));
    }


    public function createAction(Request $request, $product_id)
    {
        $product = $this->getProduct($product_id);

        $user = $this->getUser();
        $username = $user->getUsername();

        $comment  = new Comment();
        $comment->setUser($username);
        $comment->setProduct($product);
        $form    = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('shop_product', array(
                'id' => $comment->getProduct()->getId())) .
                '#comment-' . $comment->getId()
            );
        }

        return $this->render('ShopBundle:Comment:create.html.twig', array(
            'comment' => $comment,
            'form'    => $form->createView()
        ));
    }

    protected function getProduct($product_id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('ShopBundle:Products')->find($product_id);

        if (!$product) {
            throw $this->createNotFoundException('Данный продукт не найден');
        }

        return $product;
    }


}