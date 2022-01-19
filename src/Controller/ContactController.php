<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer, Recaptcha3Validator $recaptcha3Validator): Response
    {
        
        $form = $this->createForm(ContactType::class);
        
        $form->handleRequest($request);
        
        
        if($form->isSubmitted() && $form->isValid()) {
            
            $score = $recaptcha3Validator->getLastResponse()->getScore();
            
            $contactFormData = $form->getData();
            
            $message = (new Email())
            ->from($contactFormData['email'])
            ->to('franckruer@orange.fr')
            ->subject('You got mail')
            ->text('Sender : '.$contactFormData['email'].\PHP_EOL.
                $contactFormData['message'],
                'text/plain');
                $mailer->send($message);
                
                $this->addFlash('success', 'Your message has been sent');
                
                return $this->redirectToRoute('blog_index');
        }       
        
        
        return $this->render('contact/index.html.twig', [
            'our_form' => $form->createView()
        ]);
    }
    
    
}
