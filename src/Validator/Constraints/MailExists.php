<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MailExists extends Constraint
{
    public $message = '{{ message }}';

    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
