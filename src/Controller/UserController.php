<?php
//src/Controller/MovieController.php
namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Friend;
use App\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;


class UserController extends AbstractController
{
    /**
     * @Route("/inscription", name="inscriptions")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $user->setEmail("@gmail.com");
        $user->setPassword("");
        $form = $this->createForm(UserType::class, $user);


        // Lors d'un envoie du formulaire on récupère les données
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $passwordEncoder->encodePassword($user, $form->get('password')->getData())
            );

            $user->setRoles(array('ROLE_ADMIN'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login'  );

        }

        return $this->render("security/inscription.html.twig", [
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * @Route("/user/show/{id}", name="user_show")
     */
    public function show(Int $id, EntityManagerInterface $entityManager): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $friendRepo = $entityManager->getRepository(Friend::class);
        $user = $userRepo->find($id);
        $isFriend = $friendRepo->hasFriend($this->getUser(),$user);
        $ratings = $user->getRatings();
        $comments = $user->getComments();
        $client = HttpClient::create();
        $secret= "key";//to move elsewhere, .env maybe ?

        $movies = [];
        foreach($ratings as $key=>$rating){
            $link = "https://api.themoviedb.org/3/movie/".$rating->getMovieId()."?api_key=".$secret."&language=fr-FR";
            $response = $client->request('GET', $link);
            $content = $response->toArray();
            $movies[]=["rating"=>$rating,"movie"=>$content];
        }


        // Obtenir la liste des commentaires Fasers
        $moviesComments = [];
        foreach($comments as $key=>$comment){
            // test obligatoires car on souhaite obtenir directement les commentaires sur le films (1er degrées)
            $firstDegrade = $comment->getReplyTo();
            if( $firstDegrade == null ){
                $link = "https://api.themoviedb.org/3/movie/".$comment->getMovieId()."?api_key=".$secret."&language=fr-FR";
                $response = $client->request('GET', $link);
                $content = $response->toArray();

                $moviesComments[]=["comment"=>$comment,"movie"=>$content];
            }
        }
        return $this->render("user/show.html.twig",["user"=>$user,"movies"=>$movies, "movieComments"=> $moviesComments, 'isFriend'=>$isFriend ]);
    }

    /**
     * @Route("/user/edit", name="user_edit")
     */
    public function edit(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $friendRepo = $entityManager->getRepository(Friend::class);
        $friendRequests = $friendRepo->findBy(['status' => "Pending",'friend'=>$user]);
        $requestsSent = $friendRepo->findBy(['status' => "Pending",'user'=>$user]);
        $friends = $friendRepo->getFriends($user,'Accepted');
        return $this->render("user/edit.html.twig",["friendRequests"=>$friendRequests,"friends"=>$friends,'requestsSent'=>$requestsSent]);
    }
}
