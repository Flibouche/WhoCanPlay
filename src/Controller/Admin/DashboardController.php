<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Topic;
use App\Entity\Feature;
use App\Entity\Disability;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
        
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // Je crée la route qui me permettra d'afficher les différentes Disabilities
        $url = $this->adminUrlGenerator->setController(DisabilityCrudController::class)->generateUrl();

        return $this->redirect($url);

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('WhoCanPlay');
    }

    public function configureMenuItems(): iterable
    {
        // La méthode yield permet de retourner un élément comme un tableau mais sans l'usage du mot clé "return"
        yield MenuItem::section('Disabilities');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create disability', 'fas fa-plus', Disability::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show disability', 'fas fa-eye', Disability::class),
        ]);

        yield MenuItem::section('Features');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create feature', 'fas fa-plus', Feature::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show feature', 'fas fa-eye', Feature::class),
        ]);

        yield MenuItem::section('Games');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create game', 'fas fa-plus', Game::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show game', 'fas fa-eye', Game::class),
        ]);

        yield MenuItem::section('Images');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create image', 'fas fa-plus', Image::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show image', 'fas fa-eye', Image::class),
        ]);

        yield MenuItem::section('Posts');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create post', 'fas fa-plus', Post::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show post', 'fas fa-eye', Post::class),
        ]);

        yield MenuItem::section('Topics');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create topic', 'fas fa-plus', Topic::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show topic', 'fas fa-eye', Topic::class),
        ]);

        yield MenuItem::section('users');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Create topic', 'fas fa-plus', User::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show topic', 'fas fa-eye', User::class),
        ]);
    }
}
