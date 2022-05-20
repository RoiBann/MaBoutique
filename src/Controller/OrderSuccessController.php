<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripSessionId($stripSessionId);

        if (!$order || $order->getUser() != $this->getUser()){
            return  $this->redirectToRoute('home');
        }

        if ($order->getState() == 0) {
            //Vider la session "cart"
            $cart->remove();
            //Modifier le status isPaid en mettant 1
            $order->setState(1);
            $this->entityManager->flush();

            //Envoyer un email au client pour lui confirmer sa commande
            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Merci pour votre commande sur <strong>Ma Boutique</strong>.<br><br>Vous serez à coup sûr satisfait(e) de votre passage sur votre boutique en ligne préférée!";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande à bien été validée!", $content);
        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
