<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Form\Type;

use SkyDiablo\MediaBundle\Form\DataTransformer\MediaUploadTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of MediaType
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 */
class MediaType extends AbstractType
{

    const UPLOAD_FIELD_NAME = 'media';

    protected MediaUploadTransformer $mediaUploadTransformer;

    public function __construct(MediaUploadTransformer $mediaUploadTransformer)
    {
        $this->mediaUploadTransformer = $mediaUploadTransformer;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->mediaUploadTransformer);
        $builder->add(self::UPLOAD_FIELD_NAME, FileType::class, [
            'data_class' => null,
            'setter' => function (&$media, $value, $form) {
                if ($value instanceof UploadedFile) {
                    $media = [MediaType::UPLOAD_FIELD_NAME => $value];
                }
            },
        ]);

        // TODO: find a propper way to show/hide unlink checkbox...
        //$showUnlink = ($options['data'] ?? $builder->getData()) instanceof \SkyDiablo\MediaBundle\Entity\Media;
        $showUnlink = true;
        if ($showUnlink) {
            $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
                if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
                    $event->setData(null);
                }
            });
            $builder->add('unlink', CheckboxType::class, [
                'label' => 'Delete', //TODO: add translations!
                'mapped' => false,
                'data' => false,
                'required' => false,
            ]);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['media'] = null;
    }

    public function getParent(): string
    {
        return FormType::class;
    }

}
