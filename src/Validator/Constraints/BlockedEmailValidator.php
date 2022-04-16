<?php


namespace App\Validator\Constraints;

use App\Entity\EmailAddress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlockedEmailValidator extends ConstraintValidator
{
    private $manager;
    private $translator;

    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    public function validate($user, Constraint $constraint)
    {
        $email = $this->manager->getRepository(EmailAddress::class)->findOneBy(['address' => $user->getEmail()]);
        $message = $this->translator->trans('email.in.blacklist',[],'validators');

        if ($email && $email->getStatus() === false) {
            $this->context ->buildViolation($constraint->message)
                ->atPath('email')
                ->setParameter('{{ message }}', $message)
                ->addViolation();
        }
    }
}
