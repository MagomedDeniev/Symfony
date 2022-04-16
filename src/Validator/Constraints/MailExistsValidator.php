<?php


namespace App\Validator\Constraints;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailExistsValidator extends ConstraintValidator
{
    private $manager;
    private $translator;

    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        $repo = $this->manager->getRepository(User::class);
        $exist = $repo->findOneBy(['email' => $value]);

        $message = $this->translator->trans('no.account.with.such.email',[],'validators');

        if (!$exist) {
            $this->context ->buildViolation($constraint->message)
                ->setParameter('{{ message }}', $message)
                ->addViolation();
        }
    }
}
