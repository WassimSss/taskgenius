<?php

namespace App\Service;

use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;

class InvitationService
{
    protected $invitation;
    protected $invitationReceived;
    protected $invitationRepository;
    protected $userRepository;
    protected $user;

    public function __construct(InvitationRepository $invitationRepository, UserRepository $userRepository, Security $security)
    {
        $this->invitationRepository = $invitationRepository;
        $this->userRepository = $userRepository;
        $this->user = $security->getUser();
    }

    public function isInvitationReceived(): bool
    {
        // Si une invitation pour l'user connecté est trouver
        if($this->invitationRepository->findBy(['recipient' => $this->user,'status' => 'En attente']))
        {
            $this->invitation = $this->invitationRepository->findBy([
                'recipient' => $this->user,
                'status' => 'En attente'
            ]);
            $this->setInvitationReceived(true);
        } else {
            $this->setInvitationReceived(false);
        }
        return $this->invitationReceived;
    }

    public function getInvitation()
    {
        if($this->invitationRepository->findOneBy(['recipient' => $this->user]))
        {
            return $this->invitation = $this->invitationRepository->findBy([
                'recipient' => $this->user,
                'status' => 'En attente'
            ]);
        } else {
        return false;
        }
    }

    public function setInvitationReceived(bool $isInvitation)
    {
        $this->invitationReceived = $isInvitation;
    }

}