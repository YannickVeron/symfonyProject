<?php
//src/Controller/MovieController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Rating;
use App\Entity\Comment;
use App\Service\APIManager;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\formulaire\FormComment;
use \Datetime;


class MovieController extends AbstractController
{
    /**
     * @Route("/", name="movie_index")
     */
    public function index(APIManager $apiManager)
    {
        $movies = $apiManager->getDiscover();
        $user = $this->getUser();
        $listCommentFriend = array();
        if(isset($user)){
            $userFriend = $user->getFriends();
            foreach($userFriend as $use){
                $listCommentFriend = array_merge($listCommentFriend,$use->getFriend()->getComments()->toArray());
            }
        }
        usort($listCommentFriend, function($a, $b) {
            $ad = $a->getCreatedAt();
            $bd = $b->getCreatedAt();
            if($ad == $bd) {
                return 0;
            }
            return $ad > $bd ? -1 : 1;
        });
        //dd($listCommentFriend);
        return $this->render("movie/index.html.twig",["movies"=>$movies , "listCommentFriend"=>$listCommentFriend ]);
    }

    /**
     * @Route("/show/{id}-{name}", name="movie_show")
     */
    public function show(int $id, EntityManagerInterface $entityManager, Request $request, APIManager $apiManager)
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

        //Lors d'un envoie du formulaire on récupère les données
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

        $content = $apiManager->getMovie($id);
        $trailer = $apiManager->getTrailer($id);

        $listMovieCategorie =  $apiManager->getSimilar($id);

        return $this->render("movie/show.html.twig",["movie"=>$content,"rating"=>$avgScore[array_key_first($avgScore)] , "trailer"=>$trailer , "formComment"=>  $formComment->createView(),"comments"=>$comments, "listMovieCategorie"=>$listMovieCategorie['results']]);
    }
}
