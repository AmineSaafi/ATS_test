<?php

namespace AtsBundle\Controller;

use AtsBundle\Entity\Product;
use AtsBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $url = file_get_contents("http://internal.ats-digital.com:3066/api/products");
        $data = json_decode($url);
        $product = new Product();
        $em = $this->getDoctrine()->getManager();

        foreach ($data as $d) {
            $product = new Product();
            $product->setProductName($d->productName);
            $product->setBasePrice($d->basePrice);
            $product->setCategory($d->category);
            $product->setBrand($d->brand);
            $product->setProductMaterial($d->productMaterial);
            $product->setImageUrl($d->imageUrl);
            $product->setDelivery($d->delivery);
            $product->setDetails($d->details);

            foreach ($d->reviews as $rev){
                $review = new Review();
                $review->setRating($rev->rating);
                $review->setContent($rev->content);
                $review->setProduct($product);
                //$em->persist($review);
            }

            $em->persist($product);

        }
        $em->flush();
        return new Response('Saved new product with id '.$product->getId());

    }

    /**
     * @Route("/products")
     */
    public function listAction(Request $request)
    {
        $products = $this->getDoctrine()
            ->getRepository('AtsBundle:Product')
            ->findAll();

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AtsBundle:Product a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

        return $this->render('@Ats/Default/index.html.twig', array(
            'recipes' => $products,
            'pagination' => $pagination
        ));
    }
}
