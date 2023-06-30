<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\SendMessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\sendMessageService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Scalar\MagicConst\Dir;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("message", name="message")
     */
    public function index(MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        /**
         * @var User $user
         */
        $userMessagesSent = $user->getMessages()->toArray();
        $userMessagesReceived = $messageRepository->findBy(['recipient' => $user]);
        $allMessagesSentAndReceived = [...$userMessagesSent, ...$userMessagesReceived];
        /**
         * @var Message $message
         */
        $discussionsBetweenUsers = [];
        $messages = [];

        // $numberOfTheMessage = 0;
        foreach ($allMessagesSentAndReceived as $message) {
            $sender = $message->getSender();
            $recipient = $message->getRecipient();
            if (!in_array([$sender->getId(), $recipient->getId()], $discussionsBetweenUsers) && !in_array([$recipient->getId(), $sender->getId()], $discussionsBetweenUsers)) {
                $arrayUsers = [$sender->getId(), $recipient->getId()];
                $discussionsBetweenUsers[] = $arrayUsers;
            }
        }
        // dd($discussionsBetweenUsers);
        // foreach ($allDiscussions as $discussion) {
            
        // }
        // dd($messages);
        return $this->render('message/index.html.twig', [
            'discussionsBetweenUsers' => $discussionsBetweenUsers,
            'userRepository' => $userRepository
        ]);
    }

    /**
     * @Route("message/{user1}/{user2}/show", name="message_show")
     */
    public function show($user1, $user2, MessageRepository $messageRepository, UserRepository $userRepository, HttpFoundationRequest $request): Response
    {
        $messagesArray = [];
        $user1 = $userRepository->find($user1);
        $user2 = $userRepository->find($user2);
        $messagesArray[] = $messageRepository->findMessageOfSenderAndRecipient($user1, $user2);
        // dd($messagesArray, $messageRepository->findMessageOfSenderAndRecipient($user1, $user2));
        $form = $this->createForm(SendMessageType::class);

        $form->handleRequest($request);

        // dd($form);
        return $this->render("message/show.html.twig", [
            'messagesArray' => $messagesArray,
            'user1' => $user1->getId(),
            'user2' => $user2->getId(),
            'form' => $form
            
            ]);
    }

    /**
     * @Route("/message/send/{user1}/{user2}", name="message_send", methods="POST")
     */
    public function send($user1, $user2, HttpFoundationRequest $request, EntityManagerInterface $em, UserRepository $userRepository)
    {
        // Le sender est celui qui est connecté
        /**
         * @var User $sender
         */
        $sender = $this->getUser();
        if ($user1 == $sender->getId()) {
            $recipient = $userRepository->find($user2);
        } elseif ($user2 == $sender->getId()) {
            $recipient = $recipient = $userRepository->find($user1);
    };
                
        // Le recipient est l'autre

        // Récupère la data du formulaire d'envoie de message
        $data = $request->request->all();

        // Créer un nouvelle objet message
        $message = new Message;

        $message->setContent($data['send_message']['content'])
        ->setSender($sender)
        ->setRecipient($recipient)
        ->setCreationDate(new DateTime());
        
        $em->persist($message);
        $em->flush();


        return $this->redirectToRoute("message_show", [
            "user1" => $user1,
            "user2" => $user2
        ]);
    }
}
