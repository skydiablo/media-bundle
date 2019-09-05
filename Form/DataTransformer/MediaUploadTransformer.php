<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Form\DataTransformer;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use SkyDiablo\MediaBundle\Entity\Factory\MediaFactory;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Form\Type\MediaType;
use SkyDiablo\MediaBundle\Model\FlySystem\File;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of MediaUploadTransformer
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 */
class MediaUploadTransformer implements DataTransformerInterface {

    /**
     * @var MediaFactory
     */
    private $mediaFactory;

    /**
     * @var FilesystemInterface
     */
    private $uploadFilesystem;
    private $mediaFormFieldName;

    public function __construct(MediaFactory $mediaFactory) {
        $this->mediaFactory = $mediaFactory;
        $basePath = ini_get('upload_tmp_dir');
        if (!$basePath) {
            $basePath = sys_get_temp_dir(); //php default behavior
        }
        $basePath = realpath($basePath);
        $this->uploadFilesystem = new Filesystem(new Local($basePath));
    }

    public function reverseTransform($value) {
        $media = $value[MediaType::UPLOAD_FIELD_NAME] ?? $value;
        if ($media instanceof UploadedFile) {
            $file = new File($this->uploadFilesystem, $media->getFilename());
            return $this->mediaFactory->createMediaByFile($file);
        }
        return $value;
    }

    public function transform($value) {
        if ($value instanceof Media) {
            return [MediaType::UPLOAD_FIELD_NAME => $value];
        }
        return $value;
    }

    public function getMediaFormFieldName() {
        return $this->mediaFormFieldName;
    }

    public function setMediaFormFieldName($mediaFormFieldName) {
        $this->mediaFormFieldName = $mediaFormFieldName;
        return $this;
    }

}
