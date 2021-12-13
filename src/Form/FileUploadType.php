<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FileUploadType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder
        ->add('upload_file', DropzoneType::class, [
          'label' => false,
          'mapped' => false, 
          'required' => true,
          'constraints' => [
            new File([ 
              'mimeTypes' => [ 
                'text/x-comma-separated-values', 
                'text/comma-separated-values', 
                'text/x-csv', 
                'text/csv', 
                'text/plain',
                'application/octet-stream', 
                'application/vnd.ms-excel', 
                'application/x-csv', 
                'application/csv', 
              ],
              'mimeTypesMessage' => "This document isn't valid.",
            ])
          ],
        ])
        ->add('send', SubmitType::class); 
  }
}