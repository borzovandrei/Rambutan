<?php

namespace ShopBundle\Controller;


use PHPExcel_IOFactory;
use PHPExcel_RichText;
use PHPExcel_Settings;
use PHPExcel_Style;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use ShopBundle\Entity\Products;
use ShopBundle\Entity\Users;
use ShopBundle\Form\ProductAddType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine();
        $file = $request->files->get('file');
        $result = $request->get('result');
        $name = mb_strtoupper($request->get('name'));
        $kol = mb_strtoupper($request->get('kol'));
        $resultat = mb_strtoupper($request->get('resultat'));


        define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        $inputFileName = $file->getPathname();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Ошибка загрузки файла: "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //Настройка стиля цвета
        $sharedStyle1 = new PHPExcel_Style();
        $sharedStyle2 = new PHPExcel_Style();
        $sharedStyle1->applyFromArray(
            array('fill' 	=> array(
                'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
                'color'		=> array('argb' => 'FFCCFFCC')
            )
            ));
        $sharedStyle2->applyFromArray(
            array('fill' 	=> array(
                'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
                'color'		=> array('argb' => 'FFF00000')
            )
            ));

        $idProd=null;
        $success=null;
        $error=null;
        foreach ($objPHPExcel->setActiveSheetIndex(0)->getRowIterator() as $row) {
            foreach ($objPHPExcel->setActiveSheetIndex(0)->getColumnIterator() as $column) {
                $cellIterator = $column->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); //устанавливает неопределеннные ячейки
                switch ($column->getColumnIndex()){
                    case $name:
                        $product = $em->getRepository("ShopBundle:Products")->findBy(array('name' => $objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue()));
                        if($product){
                            $idProd[]=$product[0]->getId();
                            $success[0][]=$objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue();
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($resultat.(string)($row->getRowIndex()), 'Товар был добавлен');
                            $objPHPExcel->getActiveSheet()->setSharedStyle($sharedStyle1, $resultat.(string)($row->getRowIndex()));
                        } else{
                            $error[0][]=$objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue();
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($resultat.(string)($row->getRowIndex()), 'Данного товара нет в базе данных');
                            $objPHPExcel->getActiveSheet()->setSharedStyle($sharedStyle2, $resultat.(string)($row->getRowIndex()));
                        }
                        break;

                    case $kol:
                        $data = $em->getRepository("ShopBundle:Products")->findBy(array('name' => $objPHPExcel->setActiveSheetIndex(0)->getCell($name.(string)($row->getRowIndex()))->getValue()));
                        if($data){
                            $success[1][]=$objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue();
                            $sum[] = $objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue();
                        }else{
                            $error[1][]=$objPHPExcel->setActiveSheetIndex(0)->getCell($column->getColumnIndex().(string)($row->getRowIndex()))->getValue();

                        }
                        break;

                    default:
                        break;
                }
            }
        }

        ob_end_clean();

        if($idProd){
        $em = $this->getDoctrine()->getManager();
        foreach ($idProd as $key=>$value){
            $prod = $em->getRepository("ShopBundle:Products")->find($value);
            $oldBalance = $prod->getBalanse();
            $newBalanse =  round($oldBalance +$sum[$key], 2);
            $prod->setBalanse($newBalanse);
            $em->persist($prod);
        }
        $em->flush();
        }

        if ($result==2){
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="supply.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        }

        if ($result==3){
            $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
            $rendererLibraryPath = '/Users/AndreiBorzov/Downloads/mpdf60/mpdf.php';

            if (!PHPExcel_Settings::setPdfRenderer(
                $rendererName,
                $rendererLibraryPath
            )) {
                die(
                    'NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
                    '<br />' .
                    'at the top of this script as appropriate for your directory structure'
                );
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="01simple.pdf"');
            header('Cache-Control: max-age=0');
//            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
//            $objWriter->save('php://output');

        }

        return $this->render('ShopBundle:Admin:supply.html.twig', array(
            'success' =>  $success,
            'error' =>  $error,
            ));

    }



    public function supplyAction(Request $request)
    {
        return $this->render('ShopBundle:Admin:supply.html.twig', array(
        ));
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