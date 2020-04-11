<?php
//src/Controller/RatingController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\User;
use App\Entity\Rating;



class RatingController extends AbstractController
{
    /**
     * @Route("/rate/{id}", name="rating_rate")
     */
    public function rate(int $id,Request $request, EntityManagerInterface $entityManager)
    {
        $userRepo = $entityManager->getRepository(User::class);
        $user = $userRepo->findAll()[0];
        $ratingRepo = $entityManager->getRepository(Rating::class);
        $rating = $ratingRepo->findOneBy([
            'user' => $user,
            'movieId' => $id
        ]);
        if($rating==null){
            $rating = new Rating();
            $rating->setUser($user);
            $rating->setMovieId($id);
            $rating->setValue($request->get("rating"));
            $entityManager->persist($rating);
        }else{
            $rating->setValue($request->get("rating"));
        }
        $entityManager->flush();
        return $this->redirectToRoute('movie_index'); 
    }
}