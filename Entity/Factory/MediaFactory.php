<?php


namespace SkyDiablo\MediaBundle\Entity\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Imagine\Image\Box;
use InvalidArgumentException;
use League\Flysystem\Util\MimeType;
use SkyDiablo\DoctrineBundle\ORM\Entity\Factory\ActiveEntityFactory;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Image;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use SkyDiablo\MediaBundle\Entity\Repository\MimeRepository;
use SkyDiablo\MediaBundle\Model\FlySystem\File;
use SkyDiablo\MediaBundle\Service\ImageService;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaFactory
 * @method Media|Image createObject($createCallback)
 */
class MediaFactory extends ActiveEntityFactory
{

    /**
     * @var MimeRepository
     */
    private $mimeRepository;

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * MediaFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param MimeRepository $mimeRepository
     * @param ImageService $imageService
     */
    public function __construct(EntityManagerInterface $entityManager, MimeRepository $mimeRepository, ImageService $imageService)
    {
        parent::__construct($entityManager);
        $this->mimeRepository = $mimeRepository;
        $this->imageService = $imageService;
    }

    /**
     * @param File $file
     * @param string $externalId
     * @return null|Media
     */
    public function createMediaByFile(File $file)
    {
        $media = null;
        $mimeType = strtolower($file->getMimetype());
        switch (true) { // TODO: add an dynamic way...
            case explode('/', $mimeType, 2)[0] == 'image':
                $media = $this->createImageByFile($file);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Given mime-type "%s" is not suitable', $mimeType));
        }
        return $media;
    }

    /**
     * @param $createCallback
     * @return null
     */
    protected function doCreateObject($createCallback = null)
    {
        $media = null;
        if (is_callable($createCallback)) {
            $media = $createCallback();
        }
        return $media;
    }

    /**
     * @param File $file
     * @return Image
     */
    public function createImageByFile(File $file)
    {
        return $this->createObject(function () use ($file) {
            $mimeType = $file->getMimetype();
            $mime = $this->mimeRepository->getByMimeType($mimeType) ?: new Mime($mimeType, $this->mimeTypeToFileExtension($mimeType));

            /** @var Box $imageDimension */
            $imageDimension = null;
            // normalize input data
            $imageFile = $this->imageService->processImageFile($file, null, $mime->getExtension(), $imageDimension); // $imageDimension will fill by reference

            $dimension = new Dimension($imageDimension->getWidth(), $imageDimension->getHeight());
            return new Image($imageFile, $mime, $dimension);
        });
    }

    /**
     * @param File $file
     * @return Image
     */
    public function createMediaByFileWithoutProcessing(File $file, string $mimeType)
    {
        return $this->createObject(function () use ($file, $mimeType) {
            $mime = $this->mimeRepository->getByMimeType($mimeType) ?: new Mime($mimeType, $this->mimeTypeToFileExtension($mimeType));

            $image = $this->imageService->loadImageFromFile($file);
            $imageDimension = $image->getSize();

            $dimension = new Dimension($imageDimension->getWidth(), $imageDimension->getHeight());
            return new Image($file, $mime, $dimension);
        });
    }

    /**
     * convert a mimeType to file-extension: image/jpeg => jpg
     * @param $mimeType
     * @return string
     */
    protected function mimeTypeToFileExtension(string $mimeType)
    {
        $list = MimeType::getExtensionToMimeTypeMap(); // get mapping list
        $list = array_filter($list, function (string $value) use ($mimeType) { // reduce list to matching mimeTypes
            return $value === $mimeType;
        });
        foreach ($list AS $extension => $value) { // search first extension with 3 chars
            if (strlen($extension) == 3) {
                return $extension;
            }
        }
        reset($list); // reset array pointer to first element
        return key($list); // get first array key
    }


}