<?php

namespace App\Twig;

use App\Service\InvitationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InvitationExtension extends AbstractExtension
{
    private $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('has_invitation', [$this, 'hasInvitation']),
            new TwigFunction('get_invitation', [$this, 'getInvitation']),
        ];
    }

    public function hasInvitation()
    {
        return $this->invitationService->isInvitationReceived();
    }

    public function getInvitation()
    {
        return $this->invitationService->getInvitation();
    }
}