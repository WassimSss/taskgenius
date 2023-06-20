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

        $form = $this->createForm(LoginType::class, [
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
     */
    public function profil(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, UserRepository $userRepository){

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Veuillez vous connecter pour pouvoir modifier votre profil") ;
        }

        $form = $this->createForm(ProfilType::class);

        $form->handleRequest($request);
        if(!$form->isSubmitted()){
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
            $user->setFullName($data['fullName']);

            if (!empty($data['password'])) {
                
                $writtenPassword = $data['password'];
                $newPassword = $request->request->get('profil')['new_password'];
                
                if ($passwordHasher->isPasswordValid($user, $writtenPassword)) {
                    
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                    $em->persist($user);

                    $em->flush();

                    // A long terme, envoyer une notification pour dire que le profil a bien été modifié
                    return $this->render('index/index.html.twig', [
                        
                    ]);


                } else {
                    return $this->render('login/profil.html.twig', [
                        'formView' => $form->createView()
                    ]);
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
