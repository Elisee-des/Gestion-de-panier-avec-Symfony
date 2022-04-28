<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart", name="cart_")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session, ProductsRepository $productsRepository)
    {
        $panier = $session->get("panier", []);

        //on fabirque les donnees
        $dataPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $product = $productsRepository->find($id);
            $dataPanier[] = [
                "produit" => $product,
                "quantite" => $quantite
            ];

            $total += $product->getPrice() * $quantite;
        }

        return $this->render('cart/index.html.twig', compact("dataPanier", "total"));
    }

    /**
     * @Route("/add/{id}", name="add")
     */
    public function add(Products $product, $id, SessionInterface $session)
    {
        //on recupere le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        //on sauvegarde dans la session
        $session->set("panier", $panier);

        $this->addFlash(
           'message',
           'Un element ajouter dans le panier'
        );

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/remove/{id}", name="remove")
     */
    public function remove(Products $product, $id, SessionInterface $session)
    {
        //on recupere le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        //on sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute('cart_index');
    }


    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Products $product, SessionInterface $session)
    {
        //on recupere le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        //on sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/delete", name="delete_all")
     */
    public function deleteAll(SessionInterface $session)
    {
        //on recupere le panier actuel
        $session->remove("panier");

        return $this->redirectToRoute('cart_index');
    }
}
