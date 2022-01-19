<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ReCaptcha\ReCaptcha;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fullName',TextType::class, [
            'label' => 'Votre Nom',
        ])
        ->add('email',EmailType::class, [
            'label' => 'Votre Email'
        ])
        ->add('message', TextareaType::class, [
            'attr' => ['rows' => 5],
        ])
        ->add('captcha', Recaptcha3Type::class, [
            'constraints' => new ReCaptcha3([
                'message' => 'karser_recaptcha3.message',
                'messageMissingValue' => 'karser_recaptcha3.message_missing_value',
            ]),
            'action_name' => 'homepage',
            
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
