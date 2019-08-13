<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Form\Type;

use SkyDiablo\MediaBundle\Form\DataTransformer\MediaUploadTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of MediaType
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 */
class MediaType extends AbstractType {

    /**
     * @var MediaUploadTransformer
     */
    private $mediaUploadTransformer;

    public function __construct(MediaUploadTransformer $mediaUploadTransformer) {
        $this->mediaUploadTransformer = $mediaUploadTransformer;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefault('data_class', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer($this->mediaUploadTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
        $view->vars['media'] = null;
    }

    public function getParent(): string {
        return FileType::class;
    }

}
