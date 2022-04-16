<?php

namespace App\CustomAbstracts;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomAbstractController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private UserRepository $userRepo
    ){}

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function user(): User
    {
        return $this->userRepo->findOneBy(['username' => $this->getUser()->getUsername()]);
    }

    public function updateLastActivity()
    {
        if ($this->getUser()) {
            $this->user()->setLastActivityAt(new \DateTime('now'));
            $this->userRepo->flush();
        }
    }
}
