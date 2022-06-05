<?php

namespace App\Controller;

use App\Entity\Gusta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Juega;
use App\Entity\Juego;
use App\Entity\Post;
use App\Entity\Reporta;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PostType;
use App\Form\RespuestaType;
use App\Form\UserType;
use DateTime;

/**
     * @Route("/forum")
     */
class UserController extends AbstractController
{
    /**
     * @Route("/index", name="app_index")
     */
    public function index(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $user=$sec->getUser()->getRoles();
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $id=$userobj->getId();
        if(in_array("ROLE_ADMIN",$user)){
            return $this->redirectToRoute('app_adminIndex');
        }else {
            $juegos = $em->getRepository(Juego::class)->findAll();
            $juega=$em->getRepository(Juega::class)->findBy(array('usernameUser'=>$id));
            if($juega!=null){
                $post = new Post();
                $form = $this->createForm(PostType::class, $post);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    //$post->setContenido($form->get('contenido')->getData());
                    $post->setFecha(new DateTime('now'));
                    $post->setIdJuego($form->get('idJuego')->getData());
                    $post->setContenido($form->get('contenido')->getData());
                    if($form->get('foto')->getData()!=""){
                        $post->setFoto(file_get_contents($form->get('foto')->getData()));
                    }
                    $post->setIdUser($userobj);
                    $post->setRespuesta(false);
                    $em->persist($post);
                        $em->flush();
                    return $this->redirectToRoute('app_index');
                }
                $idsJuego=array();
                foreach ($juega as $jueg) {
                    array_push($idsJuego,$jueg->getIdJuego()->getId());
                }
                
               $posts=$em->getRepository(Post::class)->getPosts($idsJuego);

                $mgs=$em->getRepository(Gusta::class)->findBy(array('idUser'=>$userobj));

               return $this->render('user/index.html.twig', [
                    'form' => $form->createView(),
                    'juegos' => $juegos,
                    'user' => $userId,
                    'posts' => $posts,
                    'mgs' => $mgs
                ]);
            }else{
                $juegos = $em->getRepository(Juego::class)->findAll();
                
               
                return $this->render('user/addJuegosUser.html.twig', [
                    'user'=> $userId,
                    'juegos' => $juegos,
                
                    'controller_name' => 'UserController'
                ]);
            }

            
        }

       
    }

    /**
     * @Route("/crearpost/{juego}/{mensaje}/{img}", name="app_altapost")
     */
    public function crearPost($juego,$mensaje,$img,Security $sec,EntityManagerInterface $em): Response
    {
        $post=new Post();
        
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $juegoobj=$em->getRepository(Juego::class)->findOneBy(array('id'=>$juego));
        $post->setIdUser($userobj);
        $post->setIdJuego($juegoobj);
        $post->setContenido($mensaje);
        $post->setRespuesta(false);
        $post->setFecha(new DateTime('now'));
       
            $post->setFoto(file_get_contents(str_replace("º","\\",$img)));
        
        $em->persist($post);
        $em->flush();
        
        return $this->redirectToRoute('app_index');
       
    }


    /**
     * @Route("/altajuegos/{ids}/{nombre}", name="app_altajuego")
     */
    public function altajuegos($ids,$nombre,Security $sec,EntityManagerInterface $em): Response
    {
        $juegos=explode(",",$ids);
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$nombre));
        foreach ($juegos as $juego) {
            $game=new Juega();
            $juegoo=$em->getRepository(Juego::class)->findOneBy(array('id'=>$juego));
            $game->setIdJuego($juegoo);
            $game->setUsernameUser($userobj);
            $em->persist($game);
            $em->flush();
        }

        return $this->redirectToRoute('app_index');
       
    }

    /**
     * @Route("/perfil", name="app_perfil")
     */
    public function perfil(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $posts=$em->getRepository(Post::class)->getPostsUser($userobj->getId());
        $nposts=count($posts);
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        $form2 = $this->createForm(UserType::class, $userobj);
        $form2->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$post->setContenido($form->get('contenido')->getData());
            $post->setFecha(new DateTime('now'));
            $post->setIdJuego($form->get('idJuego')->getData());
            $post->setContenido($form->get('contenido')->getData());
            if($form->get('foto')->getData()!=""){
                $post->setFoto(file_get_contents($form->get('foto')->getData()));
            }
            $post->setIdUser($userobj);
            $post->setRespuesta(false);
            $em->persist($post);
                $em->flush();
            return $this->redirectToRoute('app_perfil');
        }
        if ($form2->isSubmitted() && $form2->isValid()) {

            //$post->setContenido($form->get('contenido')->getData());
            
           
            if($form2->get('nombre')->getData()!=""){
                $userobj->setNombre($form2->get('nombre')->getData());
            }
            if($form2->get('foto')->getData()!=""){
                $userobj->setFoto(file_get_contents($form2->get('foto')->getData()));
            }
            if($form2->get('descripcion')->getData()!=""){
                $userobj->setDescripcion($form2->get('descripcion')->getData());
            }
            if($form2->get('ubicacion')->getData()!=""){
                $userobj->setUbicacion($form2->get('ubicacion')->getData());
            }
           
            $em->persist($userobj);
                $em->flush();
            return $this->redirectToRoute('app_perfil');
        }
        $mgs=$em->getRepository(Gusta::class)->findBy(array('idUser'=>$userobj));
        return $this->render('user/perfil.html.twig', [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'user' => $userobj,
            'posts' => $posts,
            'nposts' => $nposts,
            'mgs' => $mgs
        ]);


    }

    /**
     * @Route("/perfil/{usuario}", name="app_perfilUsu")
     */
    public function perfilUsu($usuario,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $usuario2=$em->getRepository(User::class)->findOneBy(array('username'=>$usuario));
        if($usuario==$userobj->getUsername()){
            return $this->redirectToRoute('app_perfil');
        }
        $posts=$em->getRepository(Post::class)->getPostsUser($usuario2->getId());
        $nposts=count($posts);
        
        $mgs=$em->getRepository(Gusta::class)->findBy(array('idUser'=>$userobj));
       
        return $this->render('user/perfilUsu.html.twig', [

            'user' => $usuario2,
            'posts' => $posts,
            'nposts' => $nposts,
            'mgs' => $mgs
        ]);


    }
   /**
     * @Route("/masjuegos", name="app_masjuegos")
     */
    public function masjuegos(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $juega=$em->getRepository(Juega::class)->findBy(array('usernameUser'=>$userobj));
        $idsJuego=array();
        foreach ($juega as $jueg) {
            array_push($idsJuego,$jueg->getIdJuego()->getId());
        }
        $juegosSinJugar=$em->getRepository(Juego::class)->getJuegosNoSeleccionados($idsJuego);

        return $this->render('user/masjuegos.html.twig', [
            'juegos' => $juegosSinJugar,
            'user' => $userobj
            
        ]);
    }
    /**
     * @Route("/juegos", name="app_juegos")
     */
    public function juegos(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $juega=$em->getRepository(Juega::class)->findBy(array('usernameUser'=>$userobj));
        $idsJuego=array();
        foreach ($juega as $jueg) {
            array_push($idsJuego,$jueg->getIdJuego()->getId());
        }
        $juegosSinJugar=$em->getRepository(Juego::class)->getJuegosNoSeleccionados($idsJuego);
        $juegosJugados=$em->getRepository(Juego::class)->getJuegosSeleccionados($idsJuego);
        return $this->render('user/juegos.html.twig', [
            'juegosSinJugar' => $juegosSinJugar,
            'juegosJugados' => $juegosJugados,
            'user' => $userobj
            
        ]);
    }
    /**
     * @Route("/perfilJuego/{idJuego}", name="app_perfilJuego")
     */
    public function perfilJuego($idJuego,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $juego=$em->getRepository(Juego::class)->findOneBy(array('id'=>$idJuego));
        $posts=$em->getRepository(Post::class)->getPostsOfGame($juego->getId());
        $nposts=count($posts);
        $mgs=$em->getRepository(Gusta::class)->findBy(array('idUser'=>$userobj));
        return $this->render('user/perfilJuego.html.twig', [
            'juego'=>$juego,
            'posts'=>$posts,
            'nposts' => $nposts,
            'mgs' => $mgs
            
        ]);
    }

    /**
     * @Route("/contestar/{idPost}", name="app_contestar")
     */
    public function contectar($idPost,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $postAContestar=$em->getRepository(Post::class)->findOneBy(array('id'=>$idPost));
        

        $post = new Post();
        $form = $this->createForm(RespuestaType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$post->setContenido($form->get('contenido')->getData());
            $post->setFecha(new DateTime('now'));
            
            $post->setContenido($form->get('contenido')->getData());
            if($form->get('foto')->getData()!=""){
                $post->setFoto(file_get_contents($form->get('foto')->getData()));
            }
            $post->setIdJuego($postAContestar->getIdJuego());
            $post->setIdUser($userobj);
            $post->setRespuesta(true);
            $post->setIdRespuesta($postAContestar->getId());
                $em->persist($post);
                $em->flush();
            return $this->redirectToRoute('app_index');
        }
       
        return $this->render('user/responder.html.twig', [
             'form' => $form->createView(),
             'postAContestar' => $postAContestar,
             'userobj' => $userobj
         ]);


    }

    /**
     * @Route("/hilo/{idPost}", name="app_hilo")
     */
    public function hilo($idPost,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $postOriginal=$em->getRepository(Post::class)->findOneBy(array('id'=>$idPost));

        $postsRespuesta=$em->getRepository(Post::class)->getPostsRespuestas($idPost);
        $mgs=$em->getRepository(Gusta::class)->findBy(array('idUser'=>$userobj));
       
        return $this->render('user/hilo.html.twig', [
             'postOriginal' => $postOriginal,
             'postsRespuesta' => $postsRespuesta,
             'user' =>$userobj,
             'mgs' => $mgs
         ]);


    }

    /**
     * @Route("/reportar/{idPost}", name="app_reportar")
     */
    public function reportar($idPost,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $postAReportar=$em->getRepository(Post::class)->findOneBy(array('id'=>$idPost));

        
        
       
        return $this->render('user/report.html.twig', [
             'postAReportar' => $postAReportar,
             'user' =>$userobj
         ]);


    }

    /**
     * @Route("/enviarReporte/{idPost}/{tipo}", name="app_enviarReporte")
     */
    public function enviarReporte($idPost,$tipo,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $postAReportar=$em->getRepository(Post::class)->findOneBy(array('id'=>$idPost));
        
        $reporte=new Reporta();
        $reporte->setIdUser($userobj);
        $reporte->setTipoReport($tipo);
        $reporte->setIdPost($postAReportar);
        $em->persist($reporte);
        $em->flush();
        
       
        return $this->redirectToRoute('app_index');


    }

     /**
     * @Route("/mg/{idPost}/{redirect}", name="app_mg")
     */
    public function mg($idPost,$redirect,Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $userId=$sec->getUser()->getUserIdentifier();
        $userobj=$em->getRepository(User::class)->findOneBy(array('username'=>$userId));
        $post=$em->getRepository(Post::class)->findOneBy(array('id'=>$idPost));
        $mg=$em->getRepository(Gusta::class)->findOneBy(array('idPost'=>$post,'idUser'=>$userobj));
        
        if ($mg) {
            $em->remove($mg);
        }else{
            $newmg=new Gusta();
            $newmg->setIdPost($post);
            $newmg->setIdUser($userobj);
            $em->persist($newmg);
        }
       $em->flush();
        return $this->redirectToRoute($redirect);


    }
    
}
