<?php
namespace Kumatch\BBSAPI\Spec;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpecResult
{
    /**
     * @var bool
     */
    private $isValid = true;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param bool $isValid
     * @param array $errors
     */
    public function __construct($isValid = true, $errors = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}