<?php
//src/Controller/MovieController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;


class MovieController extends AbstractController
{
    /**
     * @Route("/", name="movie_index")
     */
    public function index(EntityManagerInterface $entityManager)
    {

        $client = HttpClient::create();
        $secret= "4ab62c41adff3e08feda913337efb774";//to move elsewhere, .env maybe ?
        $link = "https://api.themoviedb.org/3/discover/movie?api_key=".$secret;
        $response = $client->request('GET', $link);

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        //dd($content["results"]);
        return $this->render("movie/index.html.twig",["movies"=>$content["results"]]);
    }

    /**
     * @Route("/show/{id}-{name}", name="movie_show")
     */
    public function show(int $movieId)
    {
        return $this->render("movie/show.html.twig",['movieId'=>$movieId]);
    }
}