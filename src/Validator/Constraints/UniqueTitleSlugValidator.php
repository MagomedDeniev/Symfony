<?php


namespace App\Validator\Constraints;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTitleSlugValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $manager,){}

    public function validate($entity, Constraint $constraint)
    {
        $repo = $this->manager->getRepository(\get_class($entity));

        $items      = $repo->findBy(['title' => $entity->getTitle()]);
        $field      = 'alias';
        $duplicate  = false;

        /** Verify if any entity items have same slug */
        $slugifier  = new Slugify();
        $slug       = $slugifier->slugify($entity->getTitle());
        $exist      = $repo->findOneBy(['slug' => $slug]);

        if ($exist) {
            $duplicate = true;
        }

        /** Verify if any entity items have same title */
        foreach ($items as $item) {
            if ($entity->getTitle() == $item->getTitle()) {
                $field      = 'title';
                $duplicate  = true;
            }
        }

        /** Verify if this is an existing entity item, for allow update */
        if ($repo->findOneBy(['id' => $entity->getId()])){
            if ($exist && $exist->getId() == $entity->getId()) {
                $duplicate = false;
            }
        }

        if ($duplicate) {
            $this->context  ->buildViolation($constraint->message)
                            ->atPath('title')
                            ->setParameter('{{ field }}', $field)
                            ->addViolation();
        }
    }
}
