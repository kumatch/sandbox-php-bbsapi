<?php
namespace Kumatch\BBSAPI\Repository;

use Doctrine\ORM\EntityRepository;
use Kumatch\BBSAPI\Entity\AccessToken;

class AccessTokenRepository extends EntityRepository
{
    /**
     * @param AccessToken $accessToken
     */
    public function add(AccessToken $accessToken)
    {
        $this->getEntityManager()->persist($accessToken);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function remove(AccessToken $accessToken)
    {
        $this->getEntityManager()->remove($accessToken);
    }
}