<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('medias', MediaType::class, [
            'allow_add' => true,
            'required' => true,
            'allow_delete' => true
        ]);
    }
    
    /**
     * @return string
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix()
    {
        parent::getBlockPrefix();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getParent()
     */
    public function getParent()
    {
        return PostType::class;
    }
}