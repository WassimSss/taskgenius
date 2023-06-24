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
        ];
    }

    public function hasInvitation()
    {
        return $this->invitationService->isInvitationReceived();
    }
}