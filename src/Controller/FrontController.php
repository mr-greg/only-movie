<?php

namespace App\Controller;

use App\Entity\Actors;
use App\Entity\Categories;
use App\Entity\Movies;
use App\Form\ActorsType;
use App\Form\CategoriesType;
use App\Form\MoviesType;
use App\Repository\ActorsRepository;
use App\Repository\CategoriesRepository;
use App\Repository\MoviesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(MoviesRepository $repository)
    {

        //ici on appelle le repository de Movies afin d'effectuer une requete de SELECT (affichage)
        // on recupere toutes les entrées de movies avec la méthode findAll()
        $movies = $repository->findAll();


        return $this->render('front/home.html.twig', [
            'movies' => $movies
        ]);

    }

    /**
     * @Route("/addMovies", name="addMovies")
     */
    public function addMovies(Request $request, EntityManagerInterface $manager)
    {
        // Ici on injecte en dépendance Request (de symfony\component\HttpFoundation) afin de récupérer toutes les données chargées dans nos SUPERGLOBALES ($_POST, $_GET ...), on injecte de même l' EntityManagerInterface (de Doctrine\ORM) afin d'effectuer toute requête d'INSERT, de MODIFICATION ou de SUPPRESSION

        $movie = new Movies();
        // ici on instancie un nouvel objet vide de la classe Movies

        $form = $this->createForm(MoviesType::class, $movie, ['add' => true]);
        // ici on instancie un objet de la classe Form qui attend en argument sur quel formulaire il doit se baser et le liens avec l'entité avec l'entité en second argument affin qu'il puisse effectuer les controles

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            // condition de traitement du formulaire (l'ordre des conditions est impératif)

            $coverFile = $form->get('cover')->getData();
            //dd($coverFile);
            $coverName = date('YmdHis') . uniqid() . $coverFile->getClientOriginalName();
            $coverFile->move($this->getParameter('cover_directory'),
                $coverName);
            //dd($movie);
            $movie->setCover($coverName);
            $manager->persist($movie);
            $manager->flush();


            $this->addFlash('success', 'Ajout effectué avec succès');
            return $this->redirectToRoute('listMovies');


        endif;

        return $this->render('front/addMovies.html.twig', [
            'form' => $form->createView()

        ]);
    }

    /**
     * @Route("/editMovies/{id}", name="editMovies")
     */
    public function editMovies(Movies $movie, Request $request, EntityManagerInterface $manager)
    {

        $form = $this->createForm(MoviesType::class, $movie, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $coverFile = $form->get('coverUpdate')->getData();
            // si on a une photo en modification
            if ($coverFile):
                //alors on renomme le fichier
                $coverName = date('dmYHis') . uniqid() . $coverFile->getClientOriginalName();
                // puis on l'upload dans notre dossier 'uploads'
                $coverFile->move($this->getParameter('cover_directory'), $coverName);
                // on supprime l'ancienne photo présente dans le dossier d'uploads
                unlink($this->getParameter('cover_directory') . '/' . $movie->getCover());
                // on affecte le nouveau nom de fichier à notre objet
                $movie->setCover($coverName);

            endif;
            // on prépare la requête et la gardons en mémoire
            $manager->persist($movie);
            // on execute la ou les requetes
            $manager->flush();
            $this->addFlash('success', 'Modification effectuée avec succès');
            return $this->redirectToRoute('listMovies');
        endif;


        return $this->render('front/editMovies.html.twig', [
            'form' => $form->createView(),
            'movie' => $movie

        ]);
    }

    /**
     * @Route("/addCategories", name="addCategories")
     * @Route("/editCategories/{id}", name="editCategories")
     */
    public function addCategories(Request $request, EntityManagerInterface $manager, CategoriesRepository $repository, $id = null)
    {
        $ajout = false;

        $categories = $repository->findAll();

        if (!$id):
            $categorie = new Categories();
            $ajout = true;
        else:
            $categorie = $repository->find($id);
        endif;


        $form = $this->createForm(CategoriesType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $manager->persist($categorie);
            $manager->flush();

            if (!$id):
                $this->addFlash('success', 'Catégorie ajoutée avec succès');
            else:
                $this->addFlash('success', 'Catégorie modifiée avec succès');
            endif;

            return $this->redirectToRoute('addCategories');
        endif;

        return $this->render('front/addCategories.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
            'ajout' => $ajout
        ]);


    }


    /**
     * @Route("/listMovies", name="listMovies")
     */
    public function listMovies(MoviesRepository $repository)
    {
        $movies = $repository->findAll();

        return $this->render("front/listMovies.html.twig", [
            'movies' => $movies
        ]);
    }

    /**
     * @Route("/deleteMovies/{id}", name="deleteMovies")
     */
    public function deleteMovies(Movies $movies, EntityManagerInterface $manager)
    {
        $this->addFlash('success', $movies->getTitle() . ' supprimé avec succès');
        $manager->remove($movies);
        $manager->flush();
        return $this->redirectToRoute('listMovies');

    }

    /**
     * @Route("/deleteCategories/{id}", name="deleteCategories")
     */
    public function deleteCategories(Categories $categories, EntityManagerInterface $manager)
    {
        $this->addFlash('success', 'Catégorie supprimé avec succès');
        $manager->remove($categories);
        $manager->flush();
        return $this->redirectToRoute('addCategories');

    }

    /**
     * @Route("/actors", name="actors")
     * @Route("/editActors/{id}", name="editActors")
     */
    public function Actors(ActorsRepository $repository, EntityManagerInterface $manager, Request $request, $id = null)
    {
        $ajout = false;

        $actors = $repository->findAll();

        if (!$id):
            $actor = new actors();
            $ajout = true;
        else:
            $actor = $repository->find($id);
        endif;


        $form = $this->createForm(ActorsType::class, $actor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $manager->persist($actor);
            $manager->flush();

            if (!$id):
                $this->addFlash('success', 'Acteur ajouté avec succès');
            else:
                $this->addFlash('success', 'Acteur modifié avec succès');
            endif;

            return $this->redirectToRoute('actors');
        endif;


        return $this->render('front/actors.html.twig', [
            'form' => $form->createView(),
            'ajout' => $ajout,
            'actors' => $actors
        ]);
    }


    /**
     * @Route("/deleteActors/{id}", name="deleteActors")
     */
    public function deleteActors(Actors $actors, EntityManagerInterface $manager)
    {
        $this->addFlash('success', 'Acteur supprimé avec succès');
        $manager->remove($actors);
        $manager->flush();
        return $this->redirectToRoute('actors');

    }

    /**
     * @Route("/detailActors/{id}", name="detailActors")
     */
    public function detailActors(ActorsRepository $repository, $id)
    {

        $actors = $repository->find($id);

        return $this->render("front/detailActors.html.twig", [
            'actors' => $actors
        ]);

    }

     /**
     * @Route("/detailMovies/{id}", name="detailMovies")
     */
    public function detailMovies(MoviesRepository $repository, $id)
    {

        $movies = $repository->find($id);

        return $this->render("front/detailMovies.html.twig", [
            'movies' => $movies
        ]);
    }


    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @Route("/usersMovies", name="usersMovies")
     * @Route("/addActor/{param}", name="addActor")
     */
    public function usersMovies(Request $request, EntityManagerInterface $manager, $param=null)
    {
        $affich=false;
        if ($param):
           // dd('coucou');
         $affich=true;
            endif;


        $movie = new Movies();
        $form = $this->createForm(MoviesType::class, $movie, ['add' => true]);
        $form->handleRequest($request);
        $actor = new Actors();
        $formActor = $this->createForm(ActorsType::class, $actor);
        $formActor->handleRequest($request);

        if ($formActor->isSubmitted() && $formActor->isValid()):
            $manager->persist($actor);
            $manager->flush();
                $affich=false;
            return $this->redirectToRoute('usersMovies',['affich'=>$affich]);

        endif;

        if ($form->isSubmitted() && $form->isValid()):
            $coverFile = $form->get('cover')->getData();
            //dd($coverFile);
            $coverName = date('YmdHis') . uniqid() . $coverFile->getClientOriginalName();
            $coverFile->move($this->getParameter('cover_directory'),
                $coverName);
            //dd($movie);
            $movie->setCover($coverName);
            $movie->setCreatedBy($this->getUser());
            $manager->persist($movie);
            $manager->flush();

            return $this->redirectToRoute('listUsersMovies');
        endif;


        return $this->render('front/usersMovies.html.twig',[
            'form'=>$form->createView(),
            'formActor'=>$formActor->createView(),
            'affich'=>$affich
        ]);

    }


    /**
     * @Route("/listUsersMovies", name="listUsersMovies")
     */
    public function listUsersMovies(MoviesRepository $repository)
    {
        $movies=$repository->findBy(['CreatedBy'=>$this->getUser()]);

        return $this->render('front/listUsersMovies.html.twig',[
            'movies'=>$movies
        ]);
    }

    /**
     * @Route("/editUsersMovies/{id}", name="editUsersMovies")
     */
    public function editUsersMovies(Movies $movie, Request $request, EntityManagerInterface $manager)
    {

        $form = $this->createForm(MoviesType::class, $movie, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $coverFile = $form->get('coverUpdate')->getData();
            // si on a une photo en modification
            if ($coverFile):
                //alors on renomme le fichier
                $coverName = date('dmYHis') . uniqid() . $coverFile->getClientOriginalName();
                // puis on l'upload dans notre dossier 'uploads'
                $coverFile->move($this->getParameter('cover_directory'), $coverName);
                // on supprime l'ancienne photo présente dans le dossier d'uploads
                unlink($this->getParameter('cover_directory') . '/' . $movie->getCover());
                // on affecte le nouveau nom de fichier à notre objet
                $movie->setCover($coverName);

            endif;
            // on prépare la requête et la gardons en mémoire
            $manager->persist($movie);
            // on execute la ou les requetes
            $manager->flush();
            $this->addFlash('success', 'Modification effectuée avec succès');
            return $this->redirectToRoute('listUsersMovies');
        endif;


        return $this->render('front/editUsersMovies.html.twig', [
            'form' => $form->createView(),
            'movie' => $movie

        ]);
    }

    /**
     * @Route("/listUsers", name="listUsers")
     */
    public function listUsers(UsersRepository $repository)
    {

        $users = $repository->findAll();

        return $this->render("front/listUsers.html.twig", [
            'users' => $users
        ]);

    }

}
