<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Form\DataTransformer;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use SkyDiablo\MediaBundle\Entity\Factory\MediaFactory;
use SkyDiablo\MediaBundle\Entity\Media;
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
        if ($value instanceof UploadedFile) {
            $file = new File($this->uploadFilesystem, $value->getFilename());
            return $this->mediaFactory->createMediaByFile($file);
        }
    }

    public function transform($value) {
        return $value;
    }

}
