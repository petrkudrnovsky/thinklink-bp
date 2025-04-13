<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'notes' => $this->getCurrentUser()?->getNotes(),
        ]);
    }

    private function getCurrentUser(): ?User
    {
        $user = $this->getUser();
        if(!$user instanceof User) {
            return null;
        }
        return $user;
    }
}
