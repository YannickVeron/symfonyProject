<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    // /**
    //  * @return Friend[] Returns an array of Friend objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Friend
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function hasFriend($user, $friend){//also include non accepted friend request.
        $friend = $this->findOneBy([
            'user' =>$user,
            'friend' =>$friend
        ]);
        if($friend == null){//if not find check inverse
            return $this->findOneBy([
                'user' =>$friend,
                'friend' =>$user
            ]);
        }
        return $friend;
    }

    public function getFriends($user,$status=""){
        $statusFilter="";
        if($status!=""){
            $statusFilter=" AND f.status='".$status."'";
        }
        $query = $this->getEntityManager()
        ->createQuery('SELECT f FROM App\Entity\Friend f WHERE ( f.user = :userId OR f.friend = :userId )'.$statusFilter)
        ->setParameter('userId', $user->getId());
        
        return $query->getResult();
    }
}
