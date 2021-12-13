<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('startDate', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
        ]);
        
        $builder->add('endDate', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
        ]);

        $builder->add('showWithTax', ChoiceType::class, [
            'placeholder' => 'Show Total',
            'label' => false,
            'mapped' => false, 
            'required' => false,
            'choices' => [
                'TOTAL_TE' => 'total_te',
                'TOTAL_TI' => 'total_ti',
            ],
        ]);

        $builder->add('send', SubmitType::class); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
