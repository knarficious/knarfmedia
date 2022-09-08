<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private TokenGeneratorInterface $tokenGenerator;
    private EntityManagerInterface $em;

    public function __construct(EmailVerifier $emailVerifier, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $em)
    {
        $this->emailVerifier = $emailVerifier;
        $this->tokenGenerator = $tokenGenerator;
        $this->em = $em;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // set the ROLE
            $user->setRoles(['ROLE_USER']);
            
            //persist
            $this->em->persist($user);
            
            try {
                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                    //->from(new Address('admin@franckruer.fr', 'Knarf Media admin'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    );
                
                $email = $form->getData()->getEmail();
                $this->addFlash('info', 'Un e-mail d\'activation de votre compte a été envoyé à l\'adresse: '.$email);                               
            } catch (ORMException $e) {
                
                $this->addFlash('error', 'Problème lors de l\'enregistrement');
            }
           
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/info-activation", name="info_activation")     
     */
    public function infoActivateAction()
    {
        
        // Si le visiteur est déjà identifié, on le redirige
        if ($this->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('profile');
        }
        
        return $this->render(
            'registration/info_activation.html.twig');
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */ 
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $userId = $request->get('userId');
        
        if(null === $userId)
        {
            return $this->redirectToRoute('homepage');
        }
        
        $user = $userRepository->findOneBy(['username' => $userId]);
        
        if (null === $user)
        {
            return $this->redirectToRoute('homepage');
        }
        
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }
        
        $this->addFlash('success', 'Votre compte est maintenant activé: vous pouvez vous connecter');

        return $this->redirectToRoute('app_login');
    }
}
