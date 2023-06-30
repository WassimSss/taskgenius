<?php

namespace App\Controller;

use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    protected $invitationRepository;
    protected $em;

    public function __construct(InvitationRepository $invitationRepository, EntityManagerInterface $em)
    {
        $this->invitationRepository = $invitationRepository;
        $this->em = $em;
    }

    /**
     * @Route("invitation/{id}/accepted", name="invitation_accepted")
     */
    public function accepted($id): Response
    {
        $user = $this->getUser();

        $invitation = $this->invitationRepository->find($id);

        if (!$invitation) {
            throw $this->createNotFoundException("L'invitation n'existe pas");
        }

        $project = $invitation->getProject();

        if ($user == $invitation->getRecipient()) {

            $project->addUser($user);

            $invitation->setStatus("Accepté");

            $this->em->persist($invitation);
            $this->em->flush();

            return $this->redirectToRoute('message', []);

        } else {
            throw $this->createAccessDeniedException("Vous n'êtes pas le receveur de l'invitation");
        }
    }

    /**
     * @Route("invitation/{id}/denied", name="invitation_denied")
     */
    public function denied($id): Response
    {
        $user = $this->getUser();

        $invitation = $this->invitationRepository->find($id);

        if (!$invitation) {
            throw $this->createNotFoundException("L'invitation n'existe pas");
        }

        if ($user == $invitation->getRecipient()) {

            $invitation->setStatus("Refusé");

            $this->em->persist($invitation);
            $this->em->flush();

            return $this->redirectToRoute('message', []);
        } else {
            throw $this->createAccessDeniedException("Vous n'êtes pas le receveur de l'invitation");
        }
    }

}
