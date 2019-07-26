<?php

namespace SkyDiablo\MediaBundle\Service;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use League\Flysystem\File;
use League\Flysystem\FilesystemInterface;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class ImageService
 */
class ImageService {

    const DEFAULT_MAX_IMAGE_HEIGHT = 1536;
    const DEFAULT_MAX_IMAGE_WIDTH = 1536;

    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var FilesystemInterface
     */
    private $memoryFilesystem;

    /**
     * @var array
     */
    private $options;

    /**
     * ImageService constructor.
     * @param ImagineInterface $imagine
     * @param FilesystemInterface $memoryFilesystem
     */
    public function __construct(ImagineInterface $imagine, FilesystemInterface $memoryFilesystem, array $options = null) {
        $this->imagine = $imagine;
        $this->memoryFilesystem = $memoryFilesystem;
        $this->options = $options;
    }

    /**
     * @param File $file
     * @param BoxInterface $maxDimension
     * @param string $format
     * @param BoxInterface $imageDimension
     * @return File
     */
    public function processImageFile(File $file, BoxInterface $maxDimension = null, string $format = Mime::EXTENSION_JPEG, BoxInterface &$imageDimension = null) {
        $image = $this->loadImageFromFile($file);
        $image = $this->validateImageMaxDimension(
                $image,
                $maxDimension,
                $imageDimension
        );
        return $this->loadImageIntoFile($image, $format);
    }

    /**
     * @param File $file
     * @return ImageInterface
     */
    public function loadImageFromFile(File $file) {
        $stream = $file->readStream();
        try {
            return $this->imagine->read($stream);
        } finally {
            if (is_resource($stream)) {
                @fclose($stream);
            }
        }
    }

    /**
     * Check if image needs to be processed before uploading
     *
     * @param ImageInterface $image
     * @return bool
     */
    protected function imageNeedsProcessing(ImageInterface $image, string $format, BoxInterface $maxDimension = null): bool {
        $imageDimension = $image->getSize();
        $maxDimension = $maxDimension ?: new Box(self::DEFAULT_MAX_IMAGE_WIDTH, self::DEFAULT_MAX_IMAGE_HEIGHT);
        return !$maxDimension->contains($imageDimension);
    }

    /**
     * @param ImageInterface $image
     * @param BoxInterface|null $maxDimension
     * @param BoxInterface $imageDimension
     * @return ImageInterface
     */
    public function validateImageMaxDimension(ImageInterface $image, BoxInterface $maxDimension = null, BoxInterface &$imageDimension = null) {
        $imageDimension = $image->getSize();
        $maxDimension = $maxDimension ?: new Box(self::DEFAULT_MAX_IMAGE_WIDTH, self::DEFAULT_MAX_IMAGE_HEIGHT);
        if (!$maxDimension->contains($imageDimension)) { //check image dimensions
            $image = $image->thumbnail($maxDimension);
            $imageDimension = $image->getSize();
        }
        return $image;
    }

    /**
     * @param ImageInterface $image
     * @param string $format
     * @return File
     */
    public function loadImageIntoFile(ImageInterface $image, string $format = Mime::EXTENSION_JPEG) {
        $tmpFilename = uniqid();
        $this->memoryFilesystem->put(
                $tmpFilename, // storage id
                $image->get($format, $this->options ?? [])
        ); // persist in local memory
        /** @var File $file */
        $file = $this->memoryFilesystem->get($tmpFilename);
        return $file;
    }

}
