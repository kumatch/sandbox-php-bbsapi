<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\UserRepository;
use Kumatch\BBSAPI\Utility\PasswordEncoder;

class UserRegistration
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param EntityManager $entityManager
     * @param PasswordEncoder $passwordEncoder
     */
    public function __construct(EntityManager $entityManager, PasswordEncoder $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $entityManager->getRepository(EntityConstant::USER);
    }

    /**
     * @param User $user
     * @return User
     */
    public function invoke(User $user)
    {
        $user->setEncodedPassword($this->passwordEncoder->encode($user->getPassword()));

        $this->userRepository->add($user);
        $this->entityManager->flush();

        $user->setPassword(null);

        return $user;
    }
}