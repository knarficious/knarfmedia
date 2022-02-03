<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * 
     * @var LoggerInterface $logger
     */
    private $logger;
    
    /**
     * 
     * @var MailerInterface $mailer;
     */
    private $mailer;
    
    /**
     * 
     * @var ManagerRegistry
     */
    private $doctrine;

    
    public function __construct(LoggerInterface $logger, MailerInterface $mailer, ManagerRegistry $doctrine)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    { 
        $user = $token->getUser();
        
        if ($request->request->get("_target_path") !== "")
        {
            $response = new RedirectResponse($request->request->get("_target_path"));
            
        }
        else
        {
            if (in_array('ROLE_ADMIN', $user->getRoles()))
            {
                $response = new RedirectResponse('admin/posts');
            }
            
            else 
            {
                $response = new RedirectResponse('profile');
            }
        }
        
        $this->logger->info("User " . $user->getId() . " has been logged", ['user' => $user]);
        $response->headers->setCookie(new Cookie('success_connection', $token->getUserIdentifier(), 0));
        $request->getSession()->getFlashBag()->add('success', 'Bienvenue '.$token->getUserIdentifier()  .', VOUS ETES CONNECTE');
        
        $repository = $this->doctrine->getRepository(User::class);
        
        $dbUser = $repository->find($user->getId());
        
        if ($user == $dbUser)
        {
            $email = (new TemplatedEmail())
            ->to($dbUser->getEmail())
            ->subject('Vous vous êtes connecté')
            ->text('Vous vous êtes connecté aujourd\'hui avec l\'adresse IP: '.$this->getUserIpAddr().' depuis le client: '.$request->headers->get('User-Agent').'',
                'text/plain');
                $this->mailer->send($email);
        }
         return $response;

    }
    
    private function getUserIpAddr(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    
}