<?php
namespace Kumatch\BBSAPI\UseCase;

use Doctrine\ORM\EntityManager;
use Kumatch\BBSAPI\Entity\AccessToken;
use Kumatch\BBSAPI\Entity\User;
use Kumatch\BBSAPI\Entity\EntityConstant;
use Kumatch\BBSAPI\Repository\AccessTokenRepository;

class UserTokenAuthorization
{
    /**
     * @var AccessTokenRepository
     */
    private $accessTokenRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->accessTokenRepository = $entityManager->getRepository(EntityConstant::ACCESS_TOKEN);
    }

    /**
     * @param integer $userId
     * @param string $tokenString
     * @return User|null
     */
    public function invoke($userId, $tokenString)
    {
        $accessToken = $this->findAccessToken($userId, $tokenString);
        if (!$accessToken) {
            return null;
        }

        if ($accessToken->getPeriod() < $this->now()) {
            return null;
        }

        return $accessToken->getUser();
    }

    /**
     * @return int
     */
    protected function now()
    {
        return time();
    }

    /**
     * @param $userId
     * @param $tokenString
     * @return AccessToken|null
     */
    protected function findAccessToken($userId, $tokenString)
    {
        $qb = $this->accessTokenRepository->createQueryBuilder('a');
        $query = $qb->select()
            ->innerJoin('a.user', 'u', 'WITH', 'u.id = :user_id')
            ->where('a.token = :token_string')
            ->setParameter('user_id', $userId)
            ->setParameter('token_string', $tokenString)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}