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
use App\Entity\Comment;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use App\formulaire\FormComment;




class MovieController extends AbstractController
{
    /**
     * @Route("/", name="movie_index")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        // request HTTP / API MovieDB
        $client = HttpClient::create();
        $secret= "key";
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
    public function show(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $ratingRepo = $entityManager->getRepository(Rating::class);
        $commentRepo = $entityManager->getRepository(Comment::class);

        $queryAvgRating = $ratingRepo->createQueryBuilder('g')
            ->select("avg(g.value)")
            ->where('g.movieId = :idMovie')
            ->setParameter('idMovie', $id)
            ->getQuery();
        $avgScore = $queryAvgRating->getSingleResult();

        // récupère user connecter
        $user = $this->getUser();
        // Formulaire commentaire
        $comment = new Comment();
        $comment->setText('Commentaire');
        $formComment = $this->createFormBuilder($comment)
            ->add('text', TextType::class)
            ->add('save', SubmitType::class)
            ->add('replyToId',HiddenType::class,["mapped" => false])
            ->getForm();

        // Lors d'un envoie du formulaire on récupère les données
        $formComment->handleRequest($request);
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setUser($user);
            $comment->setMovieId($id);
            if($request->request->get('form')['replyToId']!=""){
                $comment->setReplyTo($commentRepo->find($request->request->get('form')['replyToId']));
            }
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('movie_index');
        }
        
        
        $comments = $commentRepo->findBy(['movieId' => $id,'replyTo'=>null]);
        //dd($comments)

        // request HTTP / API MovieDB
        $client = HttpClient::create();
        $secret= "key";//to move elsewhere, .env maybe ?
        $link = "https://api.themoviedb.org/3/movie/".$id."?api_key=".$secret."&language=fr-FR";
        $response = $client->request('GET', $link);
        $content = $response->toArray();

        $links = "https://api.themoviedb.org/3/movie/".$id."/videos?api_key=".$secret."&language=fr-FR";
        $responses = $client->request('GET', $links);
        $trailer=  $responses->toArray();
        $c = $trailer['results'][0];

        return $this->render("movie/show.html.twig",["movie"=>$content,"rating"=>$avgScore[array_key_first($avgScore)] , "trailer"=>$c , "formComment"=>  $formComment->createView(),"comments"=>$comments]);
    }
}