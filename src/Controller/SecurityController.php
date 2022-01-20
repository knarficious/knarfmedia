<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityController extends AbstractController
{
   use TargetPathTrait;
   
   /**
    * @Route("/login", name="app_login")
    * @param Request $request
    * @param Security $security
    * @param AuthenticationUtils $helper
    * @return Response
    */
   public function login(Request $request, Security $security, AuthenticationUtils $helper): Response
   {
        if($security->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('user_index');
        }
        
        $this->saveTargetPath($request->getSession(), 'main', $this->generateUrl('user_index'));
        
        return $this->render('security/login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError()
        ]);
    }
    
    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
