<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function show($user1, $user2, MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        $messagesArray = [];
        $user1 = $userRepository->find($user1);
        $user2 = $userRepository->find($user2);
        $messagesArray[] = $messageRepository->findMessageOfSenderAndRecipient($user1, $user2);

        // dd($messages);

        return $this->render("message/show.html.twig", [
            'messagesArray' => $messagesArray
            ]);
    }
}
