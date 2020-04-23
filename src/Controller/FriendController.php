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
     * @Route("/friend/add/{userid}", name="friend_add")
     */
    public function add(int $userid,EntityManagerInterface $entityManager):Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $friendRepo = $entityManager->getRepository(Friend::class);
        $userFriend = $userRepo->find($userid);
        if($friendRepo->hasFriend($this->getUser(),$userFriend) == null){
            $friend = new Friend();
            $friend->setFriend($userFriend);
            $friend->setUser($this->getUser());
            $friend->setStatus("Pending");
            $entityManager->persist($friend);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_show',['id'=>$userid]); 
    }

    /**
     * @Route("/friend/remove/{friend_id}", name="friend_remove")
     */
    public function remove(int $friend_id,EntityManagerInterface $entityManager):Response
    {
        $friendRepo = $entityManager->getRepository(Friend::class);
        $friend = $friendRepo->find($friend_id);
        if($friend!=null){
            $entityManager->remove($friend);
            $entityManager->flush();
        }

        return $this->redirectToRoute('movie_index'); 
    }

    /**
     * @Route("/friend/accept/{request_id}", name="friend_accept")
     */
    public function accept($request_id,EntityManagerInterface $entityManager):Response
    {
        $friendRepo = $entityManager->getRepository(Friend::class);
        $friendRequest = $friendRepo->find($request_id);
        $friendRequest->setStatus("Accepted");
        $entityManager->flush();

        return $this->redirectToRoute('user_edit'); 
    }
}