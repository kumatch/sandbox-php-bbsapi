<?php
namespace Kumatch\BBSAPI\Spec;

use Symfony\Component\Validator\Validation;

abstract class SpecAbstract
{
    /**
     * @var Validation;
     */
    private $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * @param $value
     * @return array|null
     */
    protected function getErrors($value)
    {
        $violations = $this->validator->validate($value);
        if (!$violations->count()) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
            $property = $violation->getPropertyPath();
            if (!isset($errors[$property])) {
                $errors[$property] = [];
            }

            array_push($errors[$property], $violation->getMessage());
        }

        return $errors;
    }
}