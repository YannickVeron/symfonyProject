<?php
//src/Controller/MovieController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Rating;

class MovieController extends AbstractController
{
    /**
     * @Route("/", name="movie_index")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $client = HttpClient::create();
        $secret= "c029119e5cf73439700add1d1e54af11";//to move elsewhere, .env maybe ?
        $link = "https://api.themoviedb.org/3/discover/movie?api_key=".$secret;
        $response = $client->request('GET', $link);

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->toArray();


        return $this->render("movie/index.html.twig",["movies"=>$content["results"]]);
    }

    /**
     * @Route("/show/{id}-{name}", name="movie_show")
     */
    public function show(int $id, EntityManagerInterface $entityManager)
    {
        $ratingRepo = $entityManager->getRepository(Rating::class);
        $queryAvgRating = $ratingRepo->createQueryBuilder('g')
            ->select("avg(g.value)")
            ->where('g.movieId = :idMovie')
            ->setParameter('idMovie', $id)
            ->getQuery();

        $avgScore = $queryAvgRating->getSingleResult();
        $client = HttpClient::create();
        $secret= "c029119e5cf73439700add1d1e54af11";//to move elsewhere, .env maybe ?
        $link = "https://api.themoviedb.org/3/movie/".$id."?api_key=".$secret."&language=fr-FR";
        $response = $client->request('GET', $link);
        $content = $response->toArray();
        return $this->render("movie/show.html.twig",["movie"=>$content,"rating"=>$avgScore[array_key_first($avgScore)]]);
    }
}