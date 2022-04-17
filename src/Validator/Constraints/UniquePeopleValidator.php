<?php


namespace App\Validator\Constraints;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniquePeopleValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $manager,){}

    public function validate($entity, Constraint $constraint)
    {
        $repo = $this->manager->getRepository(\get_class($entity));

        $field     = 'alias';
        $duplicate = false;

        /** Verify if any entity items have same slug */
        $slugifier = new Slugify();
        $slug      = $slugifier->slugify($entity->getFullName());
        $exist     = $repo->findOneBy(['slug' => $slug]);

        if ($exist) {
            $duplicate = true;
        }

        $items = $repo->findBy(['slug' => $slug]);

        /** Verify if any entity items have same first and last name */
        foreach ($items as $item) {
            if ($entity->getFullName() == $item->getFullName()) {

                /** Optional correct field message */
                if ($entity->getFirstName() && $item->getFirstName()) {
                    $sameFirstName = $entity->getFirstName() == $item->getFirstName();
                    $sameLastName  = $entity->getLastName() == $item->getLastName();

                    if ($sameFirstName && $sameLastName) {
                        $field = 'first and last name';
                    }
                }

                $duplicate  = true;
            }
        }

        /** Verify if this is an existing entity item, for allow update */
        if ($repo->findOneBy(['id' => $entity->getId()])){
            if ($exist && $exist->getId() == $entity->getId()){
                $duplicate = false;
            }
        }

        if ($duplicate) {
            $this->context ->buildViolation($constraint->message)
                           ->atPath('firstName')
                           ->setParameter('{{ field }}', $field)
                           ->addViolation();
        }
    }
}
