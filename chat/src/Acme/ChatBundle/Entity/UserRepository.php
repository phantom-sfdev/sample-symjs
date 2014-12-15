<?php

namespace Acme\ChatBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    public function findAllUsersOrderById(){
        return $this->getEntityManager()
            ->createQuery(
                'SELECT cu.id, cu.user_name FROM AcmeChatBundle:User cu ORDER BY cu.id ASC'
            )->getResult();
    }

    public function findUserByName($username){

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("cu.user_name")
            ->from("AcmeChatBundle:User", "cu")
            ->where("cu.user_name = :username")
            ->setParameter(":username", $username, Type::STRING)
            ->getQuery();

        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }
}
