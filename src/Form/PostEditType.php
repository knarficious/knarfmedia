<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('media', MediaType::class, [
            'allow_add' => true,
            'required' => true,
            'allow_delete' => true
        ]);
    }
    
    public function getBlockPrefix()
    {
        parent::getBlockPrefix();
    }
    
    public function getParent()
    {
        return PostType::class;
    }
}