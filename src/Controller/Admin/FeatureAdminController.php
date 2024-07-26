<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/feature', name: 'app_admin_feature_')]
class FeatureAdminController extends AbstractController
{
    #[Route('/', name: 'show')]
    public function index(): Response
    {
        return $this->render('admin/features/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
        ]);
    }
}