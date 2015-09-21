<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\AccessToken;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\UserRepository;
use Kumatch\BBSAPI\Repository\AccessTokenRepository;
use Kumatch\BBSAPI\Utility\PasswordEncoder;
use Kumatch\BBSAPI\Utility\TokenGenerator;

class UserAuthentication
{
    const PERIOD_HOURS = 24;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var AccessTokenRepository
     */
    private $accessTokenRepository;

    /**
     * @param EntityManager $entityManager
     * @param PasswordEncoder $passwordEncoder
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(EntityManager $entityManager, PasswordEncoder $passwordEncoder, TokenGenerator $tokenGenerator)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;

        $this->userRepository = $entityManager->getRepository(EntityConstant::USER);
        $this->accessTokenRepository = $entityManager->getRepository(EntityConstant::ACCESS_TOKEN);
    }

    /**
     * @param string $username
     * @param string $password
     * @return AccessToken|null
     */
    public function invoke($username, $password)
    {
        $user = $this->findUser($username);
        if (!$user) {
            return false;
        }

        if ($user->getEncodedPassword() !== $this->passwordEncoder->encode($password)) {
            return false;
        }

        $token = $this->tokenGenerator->generate();
        $period = $this->now() + ( self::PERIOD_HOURS * 3600 );

        $accessToken = new AccessToken();
        $accessToken
            ->setUser($user)
            ->setToken($token)
            ->setPeriod($period);
        $this->accessTokenRepository->add($accessToken);

        $this->entityManager->flush();

        return $accessToken;
    }

    /**
     * @return int
     */
    protected function now()
    {
        return time();
    }

    /**
     * @param $username
     * @return User|null
     */
    private function findUser($username)
    {
        $criteria = array(
            "username" => $username
        );

        return $this->userRepository->findOneBy($criteria);
    }
}