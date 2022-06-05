<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\Juega;
use App\Entity\Juego;
use App\Entity\Reporta;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AltaJuegoType;
use App\Form\VerificadoType;
use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;



/**
     * @Route("/admin")
     */
class AdminController extends AbstractController
{
    /**
     * @Route("/index", name="app_adminIndex")
     */
    public function index(Security $sec,EntityManagerInterface $em): Response
    {
        $user=$sec->getUser()->getUserIdentifier();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /**
     * @Route("/verificar", name="app_verificar")
     */
    public function verificar(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $error="";
        $form = $this->createForm(VerificadoType::class);
        $form->handleRequest($request);
        $username =$em->getRepository(User::class)->findOneBy(array('username'=>$form->get('usuario')->getData()));
        if ($form->isSubmitted() && $form->isValid()) {
            if($username){
                $username->setVerificado(true);
                $em->persist($username);
                $em->flush();
                return $this->redirectToRoute('app_adminIndex');
            }else{
                $error="No existe ese usuario";
            }
            
        }
        return $this->render('admin/verificado.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/banear", name="app_banear")
     */
    public function banear(Security $sec,EntityManagerInterface $em,Request $request): Response
    {
        $error="";
        $form = $this->createForm(VerificadoType::class);
        $form->handleRequest($request);
        $username =$em->getRepository(User::class)->findOneBy(array('username'=>$form->get('usuario')->getData()));
        if ($form->isSubmitted() && $form->isValid()) {
            if($username){
                $juegas=$em->getRepository(Juega::class)->findBy(array('usernameUser'=>$username));
                foreach ($juegas as $juega) {
                    $em->remove($juega);
                }
                
                $posts=$username->getPosts();
                foreach ($posts as $post) {
                    $postsRespondidas=$em->getRepository(Post::class)->findBy(array('idRespuesta'=>$post->getId()));
                    foreach ($postsRespondidas as $postsRespondida) {
                        $postsRespondida->setRespuesta(false);
                        $postsRespondida->setIdRespuesta(null);
                    }
                }
                foreach ($posts as $post) {
                     $em->remove($post);
                }
                $em->remove($username);
                $em->flush();
                return $this->redirectToRoute('app_adminIndex');
            }else{
                $error="No existe ese usuario";
            }
            
        }
        return $this->render('admin/banear.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }
    

    /**
       * @Route("/altaJuego", name="app_altaJuego")
     */
    public function altaJuego(Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        $juego = new Juego();
        $form = $this->createForm(AltaJuegoType::class, $juego);

        $form->handleRequest($request);
        $error = "";
        if ($form->isSubmitted() && $form->isValid()) {
            
            $juego->setCategoria($form->get("categoria")->getData());
            $juego->setNombre($form->get("nombre")->getData());
            $juego->setDescripcion($form->get("descripcion")->getData());
            $uploadedFile=$form->get("foto")->getData();
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $img=file_get_contents($form->get("foto")->getData());
            // Move the file to the directory where brochures are stored
               
            $uploadedFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
            $juego->setFoto($img);
            $error="";
            
            $game = $em->getRepository(Juego::class)->findOneBy(array('nombre'=>$juego->getNombre()));
            if (!$game) {
                
                    
                    try {
                        $em->persist($juego);
                        $em->flush();
                    } catch (\Exception $e) {
                        return new Response("Esto ha petao");
                    }
                    return $this->redirectToRoute('app_adminIndex');
               
            } else {

                $error = "Ya existe ese juego";
            }
        }

        return $this->render('admin/altaJuego.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    

    /**
       * @Route("/menuReportes", name="app_menuReportes")
     */
    public function menuReportes(Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
        return $this->render('admin/gestionReports.html.twig', [
          
        ]);
    }

    /**
       * @Route("/reportesJuegos", name="app_reportesJuegos")
     */
    public function reportesJuegos(Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
        $reportesJuegos=$em->getRepository(Reporta::class)->findBy(array('tipoReport'=>0));
        $juegos=$em->getRepository(Juego::class)->findAll();        

        return $this->render('admin/reportesJuegos.html.twig', [
            'reportesJuegos' => $reportesJuegos,
           'juegos' => $juegos
        ]);
    }

    /**
       * @Route("/reportesInapropiados", name="app_reportesInapropiados")
     */
    public function reportesInapropiados(Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
       
        $reportesInapropiados=$em->getRepository(Reporta::class)->findBy(array('tipoReport'=>1));

        return $this->render('admin/reportesInapropiados.html.twig', [
       
            'reportesInapropiados' => $reportesInapropiados
        ]);
    }

     /**
       * @Route("/reportarPost/{idReport}", name="app_reportarPostInapropiado")
     */
    public function reportarPost($idReport,Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
       $report=$em->getRepository(Reporta::class)->findOneBy(array('id'=>$idReport));
       $post=$report->getIdPost();
        $post->setFoto(null);
        $post->setContenido("El contenido de este post ha sido eliminado");
        $em->remove($report);
        $em->persist($post);
        $em->flush();

        return $this->redirectToRoute('app_reportesInapropiados');
    }

     /**
       * @Route("/eliminarReporte/{idReport}", name="app_eliminarReporte")
     */
    public function eliminarReporte($idReport,Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
       $report=$em->getRepository(Reporta::class)->findOneBy(array('id'=>$idReport));
       $em->remove($report);
  
        $em->flush();

        return $this->redirectToRoute('app_reportesInapropiados');
    }

    /**
       * @Route("/eliminarReporte2/{idReport}", name="app_eliminarReporte2")
     */
    public function eliminarReporte2($idReport,Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        
       $report=$em->getRepository(Reporta::class)->findOneBy(array('id'=>$idReport));
       $em->remove($report);
  
        $em->flush();

        return $this->redirectToRoute('app_reportesJuegos');
    }

    /**
       * @Route("/cambiarjuego/{idJuego}/{idReport}", name="app_cambiarjuego")
     */
    public function cambiarjuego($idJuego,$idReport,Request $request,EntityManagerInterface $em,SluggerInterface $slugger): Response
    {
        
        $juego=$em->getRepository(Juego::class)->findOneBy(array('id'=>$idJuego));
       $report=$em->getRepository(Reporta::class)->findOneBy(array('id'=>$idReport));
       $post=$report->getIdPost();
        $post->setIdJuego($juego);
        $em->remove($report);
        $em->persist($post);
        $em->flush();

        return $this->redirectToRoute('app_reportesJuegos');
    }
}
