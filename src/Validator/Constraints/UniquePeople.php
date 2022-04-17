<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute] class UniquePeople extends Constraint
{
    public $message = 'Person with this {{ field }} already exists';

    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
