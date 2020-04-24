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
use App\Service\APIManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


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
            return $this->redirectToRoute('app_login' );
        }

        return $this->render("security/inscription.html.twig", [
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * @Route("/user/show/{id}", name="user_show")
     */
    public function show(Int $id, EntityManagerInterface $entityManager, APIManager $apiManager): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $friendRepo = $entityManager->getRepository(Friend::class);
        $user = $userRepo->find($id);
        $isFriend = $friendRepo->hasFriend($this->getUser(),$user);
        $ratings = $user->getRatings();
        $comments = $user->getComments();

        $movies = [];
        foreach($ratings as $key=>$rating){
            $movies[]=["rating"=>$rating,"movie"=>$apiManager->getMovie($rating->getMovieId())];
        }

        // Obtenir la liste des commentaires Fasers
        $moviesComments = [];
        foreach($comments as $key=>$comment){
            // mandatory test because we want to get direct comments on the films (1st degrees)
            $firstDegrade = $comment->getReplyTo();
            if( $firstDegrade == null ){
                $moviesComments[]=["comment"=>$comment,"movie"=>$apiManager->getMovie($comment->getMovieId())];
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



    /**
    * @Route("/ajax", name="ajax_action")
    */
    public function ajaxAction(Request $request, EntityManagerInterface $entityManager )
    {
        /* on récupère la valeur envoyée */
        $research = $request->request->get('research');
        if( isset($research) ){
           
            // Request who return array whitch response
            $query = $entityManager->createQuery(
                "SELECT u FROM App\Entity\User u WHERE u.email like :research " 
            )->setParameter('research', "$research%");
            $result = $query->getResult();
              
            // transform response for return Response
            $researchUser = array();
            foreach( $result as $user  ){
                $researchUser[] =  $tabUser = array(
                    "id"    => $user->getId(),
                    "email"  => $user->getEmail(),
                );
            }    
            $info =  $researchUser;
        }

        /* On renvoie une réponse encodée en JSON */
        $response = new Response(json_encode(array(
            'info' => $info
        )));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}