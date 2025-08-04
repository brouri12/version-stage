<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootRedirectController extends AbstractController
{
    #[Route('/', name: 'root_redirect')]
    public function __invoke(): Response
    {
        return $this->redirectToRoute('home', status: 302);
    }
}
