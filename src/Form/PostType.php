<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Tag;
use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;

class PostType extends AbstractType
{
    private $slugger;
    private $em;
    
    public function __construct(SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $this->slugger = $slugger;
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary',
                'label' => 'label.summary',
            ])
            ->add('medias', CollectionType::class, [
                'entry_type' => MediaType::class,
                'allow_add' => true,
                'prototype' => true,
            ])   
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content',
                'label' => 'label.content',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'help' => 'help.post_publication',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,          
                'by_reference' => false,
                'required' => false,
                ])

            // form events let you modify information or fields at different steps
            // of the form handling process.
            // See https://symfony.com/doc/current/form/events.html
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post */
                $post = $event->getData();
                if (null !== $postTitle = $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($postTitle)->lower());
                }
            })
             ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                 $data = $event->getData();
                
                 //on vérifie que le tableau enfant ne soit pas vide 
                 if (!empty($data['tags'])) {
                    
                     $tags = $data['tags'];
                    
                     //on parcourt le tableau
                     foreach ($tags as $tagId) {
                        
                         //Pour le besoin de la librairie "https://www.cssscript.com/tags-input-bootstrap-5/"
                         // on vérifie si le tableau contient une/des valeur(s) saisies, et dans ce cas on persiste et renvoie le tableau avec les nouvelles données
                         if (!preg_match("#^[0-9]#", $tagId)) {
                            
                             $tag = new Tag();
                             $tag->setName($tagId);
                             $this->em->persist($tag);
                             $this->em->flush();
                            
                             //array_merge($tags, array($tag->getId()));
                             $data['tags'] = array($tag->getId());
                             //dd($data['tags']);
                             $event->setData($data, $tags);
                         }
                     }                    
                    
                    
                 }
             })
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
