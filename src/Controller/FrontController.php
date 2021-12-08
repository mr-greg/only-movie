<?php

namespace App\Controller;

use App\Entity\Actors;
use App\Entity\Cart;
use App\Entity\Categories;
use App\Entity\Movies;
use App\Entity\Orders;
use App\Entity\Pricing;
use App\Entity\Reviews;
use App\Form\ActorsType;
use App\Form\CategoriesType;
use App\Form\MoviesType;
use App\Form\PricingType;
use App\Repository\ActorsRepository;
use App\Repository\CategoriesRepository;
use App\Repository\MoviesRepository;
use App\Repository\PricingRepository;
use App\Repository\ReviewsRepository;
use App\Repository\UsersRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

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
    * @Route("/formReview/{id}/{param}", name="formReview")
    */
    public function detailMovies(MoviesRepository $repository, ReviewsRepository $reviewsRepository, Request $request, EntityManagerInterface $manager, $id = null, $param = null)
    {
        $affich = false;

        if($param){
            $affich = true;
        }
        
        $movies = $repository->find($id);

        $reviews=$reviewsRepository->findBy(['movie' => $movies ], ['publish_date' => 'DESC'], 5);
        // dd($reviews);
        $user = $this->getUser();
        $result = $reviewsRepository->findBy(['createdBy' => $user, 'movie' => $movies]);

        if(count($result)==0){
            $review = new Reviews();

        }else{
            $affich = false;
            $this->addFlash('danger', 'Vous avez déjà voté sur ce film');
        }

        if( !empty($_POST) ){

            
            
            $comment = $request->request->get('review');
            $rating = $request->request->get('rating');
            
            $user = $this->getUser();
            
            $result = $reviewsRepository->findBy(['createdBy' => $user, 'movie' => $movies ]);
            $review = new Reviews();

            $review->setCreatedBy($user)->setComment($comment)->setPublishDate(new \DateTime())->setRating($rating)->setMovie($movies);

            $manager->persist($review);
            $manager->flush();

            $this->addFlash('success', 'Merci de votre contribution :3');

            //on fait la redirect où on récup l'id, faut lui passer en paramètre comme ci-dessous
            return $this->redirectToRoute('detailMovies', ['id'=>$id]);
            
        }


        return $this->render("front/detailMovies.html.twig", [
            'movies' => $movies,
            'affich' => $affich,
            'reviews' => $reviews
        ]);
    }

    /**
     * @Route("/reviews/{id}", name="reviews")
     */
    public function reviews(ReviewsRepository $reviewsRepository, MoviesRepository $repository, $id)
    {

        $movie = $repository->find($id);
        $reviews = $reviewsRepository->findBy(['movie'=> $movie], ['publish_date' => 'DESC']);

        return $this->render('front/reviews.html.twig', [
            'reviews' => $reviews,
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
    * @Route("/deleteUsersMovies/{id}", name="deleteUsersMovies")
    */
    public function deleteUsersMovies(Movies $movies, EntityManagerInterface $manager)
    {
        $this->addFlash('success', $movies->getTitle() . ' supprimé avec succès');
        $manager->remove($movies);
        $manager->flush();
        return $this->redirectToRoute('listUsersMovies');

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

    /**
     * @Route("/listReviews/{id}", name="listReviews")
     */
    public function listReviews(MoviesRepository $moviesRepository , ReviewsRepository $repository, $id=null)
    {
        $movie = $moviesRepository->find($id);
        
        $reviews = $repository->findBy([
            'movie'=>$movie
        ]);

        

        return $this->render("front/listReviews.html.twig", [
            'reviews' => $reviews,
            'movie'=>$movie
        ]);

    }


    //ici vu qu'on avait besoin de rediriger vers l'id du film, on a rajouté un /{movie} dans l'URL pour récup l'info
    /**
     * @Route("/deleteReview/{id}/{movie}", name="deleteReview")
     */
    public function deleteReview(Reviews $reviews, EntityManagerInterface $manager, $movie)
    {

        $manager->remove($reviews);
        $manager->flush();
        $this->addFlash('success', 'Commentaire supprimé avec succès');
        return $this->redirectToRoute('listReviews', ['id'=>$movie]);

    }

    /**
     * @Route("/listPricing", name="listPricing")
     * @Route("/editPricing/{id}", name="editPricing")
     */
    public function listPricing(PricingRepository $repository, Request $request, EntityManagerInterface $manager, $id=null)
    {

        if( $id ){
            $pricing = $repository->find($id);
        }else{
            $pricing = new Pricing();
        }

        $pricings=$repository->findAll();


        $form = $this->createForm(PricingType::class, $pricing);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){

            $manager->persist($pricing);
            $manager->flush();

            if($id){
                $this->addFlash('success', 'Forfait modifié avec succès');
            }else{
                $this->addFlash('success', 'Forfait créé avec succès');
            }

            return $this->redirectToRoute('listPricing');
        }

        return $this->render('front/listPricing.html.twig', [
            'form'=>$form->createView(),
            'pricings'=>$pricings
        ]);

    }


    /**
     * @Route("/deletePricing/{id}", name="deletePricing")
     */
    public function deletePricing(Pricing $pricing, EntityManagerInterface $manager)
    {
        
        $manager->remove($pricing);
        $manager->flush();
        $this->addFlash('success', 'forfait supprimé avec succès');
        return $this->redirectToRoute('listPricing');

    }


        /**
     * @Route("/addCart/{id}/{route}", name="addCart")
     */
    public function addCart($id, PanierService $panierService, $route)
    {
        $panierService->add($id);

        ($panierService->getFullCart());

        if ($route=='home'):
        $this->addFlash('success', 'Ajout au panier effectué !');
        return $this->redirectToRoute('home');
        else:
        return $this->redirectToRoute('fullCart');
        endif;

    }

    /**
     * @Route("/removeCart/{id}", name="removeCart")
     */
    public function removeCart($id, PanierService $panierService)
    {
        $panierService->remove($id);
        $this->addFlash('success', 'Article retiré !');
        return $this->redirectToRoute('fullCart');


    }

    /**
     * @Route("/deleteCart/{id}", name="deleteCart")
     */
    public function deleteCart($id, PanierService $panierService)
    {
        $panierService->delete($id);
        return $this->redirectToRoute('fullCart');


    }

    /**
     * @Route("/fullCart", name="fullCart")
     * @Route("/order/{param}", name="order")
     */
    public function fullCart(PanierService $panierService, PricingRepository $repository, $param=null)
    {

        $pricings=$repository->findAll();
        $affich = false;
        if( $param ){

            $affich=true;
            
        }

        $fullCart=$panierService->getFullCart();

        return $this->render('front/fullCart.html.twig',[
            'fullCart'=>$fullCart,
            'affich'=>$affich,
            'pricings'=>$pricings
        ]);

    }


    /**
     *
     * @Route("/finalOrder/{id}", name="finalOrder")
     */
    public function order(PricingRepository $repository, PanierService $panierService, EntityManagerInterface $manager, $id = null)
    {
        if (!empty($_GET['pricing'])):
            $pricing = $repository->find($_GET['pricing']);
            $price = $pricing->getPrice();
            $panier = $panierService->getFullCart();
            $count = 0;
            foreach ($panier as $item):
                $count += $item['quantity'];
            endforeach;
            $total = $count * $price;
            $affich = true;
            return $this->render('front/fullCart.html.twig', [
                'affich' => $affich,
                'total' => $total,
                'pricings' => "",
                'price' => $_GET['pricing']

            ]);

        endif;

        if ($id):
            $forfait = $repository->find($id);
            $orders = new Orders();
            $orders->setDate(new \DateTime())->setPricing($forfait)->setUser($this->getUser());
            $panier = $panierService->getFullCart();

            foreach ($panier as $item):

                $cart = new Cart();
                $cart->setOrders($orders)->setMovies($item['movie'])->setQuantity($item['quantity']);
                $manager->persist($cart);

                $panierService->delete($item['movie']->getId());
            
            endforeach;
            $manager->persist($orders);
            $manager->flush();
            $this->addFlash('success', "Merci pour votre achat");
            return $this->redirectToRoute('home');


        endif;


    }


}


