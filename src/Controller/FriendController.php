<?php
//src/Controller/FriendController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Friend;
use App\Entity\User;


class FriendController extends AbstractController
{
    /**
     * @Route("/friend/add/{id}", name="friend_add")
     */
    public function add(int $id,EntityManagerInterface $entityManager):Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $friendRepo = $entityManager->getRepository(Friend::class);
        $userFriend = $userRepo->find($id);
        if($friendRepo->hasFriend($this->getUser(),$userFriend) == null){
            $friend = new Friend();
            $friend->setFriend($userFriend);
            $friend->setUser($this->getUser());
            $friend->setStatus("Pending");
            $entityManager->persist($friend);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_show',['id'=>$id]); 
    }

    /**
     * @Route("/friend/remove/{id}", name="friend_remove")
     */
    public function remove(int $id,EntityManagerInterface $entityManager):Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $friendRepo = $entityManager->getRepository(Friend::class);
        $friend = $friendRepo->hasFriend($this->getUser(),$userRepo->find($id));
        if($friend!=null){
            $entityManager->remove($friend);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_show',['id'=>$id]); 
    }
}