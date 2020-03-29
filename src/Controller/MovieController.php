<?php
//src/Controller/MovieController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Movie;

class MovieController extends AbstractController
{
    /**
     * @Route("/", name="movie_index")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $movieRepo = $entityManager->getRepository(Movie::class);
        $movies = $movieRepo->findAll();
        return $this->render("movie/index.html.twig",["movies"=>$movies]);
    }
}