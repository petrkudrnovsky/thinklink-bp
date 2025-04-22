<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Webmozart\Assert\Assert;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(NoteRepository $noteRepository): Response
    {
        $user = $this->getUser();
        Assert::nullOrIsInstanceOf($user, User::class);

        if ($user) {
            $notes = $noteRepository->findBy(['owner' => $user], ['editedAt' => 'DESC']);
        } else {
            $notes = [];
        }

        return $this->render('home/index.html.twig', [
            'notes' => $notes,
        ]);
    }
}
