<?php
namespace Kumatch\BBSAPI\Spec;

use Kumatch\BBSAPI\Entity\User;

class UserSpec extends SpecAbstract
{
    /**
     * @param User $user
     * @return SpecResult
     */
    public function validate(User $user)
    {
        $errors = $this->getErrors($user);

        if (!$errors) {
            return new SpecResult();
        } else {
            return new SpecResult(false, $errors);
        }
    }
}