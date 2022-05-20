<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPassworldController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/compte/modifier-mot-de-passe", name="account_passworld")
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification = null;

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);//Demande au formulaire s'il est prêt à écouter, manipuler la demande entrante

        if($form->isSubmitted() && $form->isValid()){
            $old_password = $form->get('old_password')->getData();

            if ($hasher->isPasswordValid($user, $old_password)){
                $new_password = $form->get('new_password')->getData();
                $password = $hasher->hashPassword($user, $new_password);

                $user->setPassword($password);
                $this->entityManager->flush();

                $notification = "Votre mot de passe à bien été mis à jour.";
             }else{
                $notification = "Votre mot de passe actuel n'est pas le bon !";
            }
        }

        return $this->render('account/passworld.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
