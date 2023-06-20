<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\ProfilType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();

        $form = $this->createForm(
            LoginType::class,
            [
                'email' => $authenticationUtils->getLastUsername()
            ]
        );

        return $this->render('login/login.html.twig', [
            'formView' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/profil", name="profil")
     * @var User $user
     */
    public function profil(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, SessionInterface $session)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $userFullName = $user->getFullName();

        if (!$user) {
            throw $this->createAccessDeniedException("Veuillez vous connecter pour pouvoir modifier votre profil");
        }

        $form = $this->createForm(ProfilType::class);

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            /**
             * @var User $user
             */
            $form->setData([
                'fullName' => $user->getFullName()
            ]);
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /**
             * @var User $user
             */
            if ($user->getFullName() !== $data['fullName']) {
                /**
                 * @var User $user
                 */
                $user->setFullName($data['fullName']);

                $em->persist($user);

                /**
                 * @var FlashBag
                 */
                $flashBag = $session->getBag('flashes');

                $flashBag->add('success', "Votre nom et prénom ont bien été modifiés");

                $em->flush();
            }


            if (!empty($data['password'])) {

                $writtenPassword = $data['password'];
                $newPassword = $request->request->get('profil')['new_password'];

                if ($passwordHasher->isPasswordValid($user, $writtenPassword)) {

                    /**
                     * @var User $user
                     */
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                    $em->persist($user);

                    $em->flush();
                    /**
                     * @var FlashBag
                     */
                    $flashBag = $session->getBag('flashes');

                    $flashBag->add('success', "Votre mot de passe a bien été modifié");
                }
            }
        }

        return $this->render('login/profil.html.twig', [
            'formView' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
    }
}
