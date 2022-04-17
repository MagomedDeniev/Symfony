<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute] class UniqueTitleSlug extends Constraint
{
    public $message = 'This {{ field }} already exists';

    public function __construct($options = null, array $groups = null, $payload = null, $message = null)
    {
        parent::__construct($options, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
