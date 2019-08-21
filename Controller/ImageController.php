<?php

namespace SkyDiablo\MediaBundle\Controller;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SkyDiablo\MediaBundle\Entity\Image;
use SkyDiablo\MediaBundle\Message\ImageThumbnailAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 * Class ImageController
 */
class ImageController extends BaseController {

    const ROUTE_NAME_IMAGE_THUMBNAIL = 'skydiablo_media_bundle_get_image_thumbnail';

    /**
     * @var ImagineInterface
     */
    private $imagine;

    public function __construct(ImagineInterface $imagine) {
        $this->imagine = $imagine;
    }

    /**
     * Generate thumbnails from image media objects
     * @param Image $media
     * @param int $maxX
     * @param int $maxY
     * @param string $format
     * @return StreamedResponse
     * @Route("/image/{mediaId}/thumbnail/{maxX}x{maxY}.{format}",
     *     name=SkyDiablo\MediaBundle\Controller\ImageController::ROUTE_NAME_IMAGE_THUMBNAIL,
     *     methods="get",
     *     requirements={
     *          "media": "\d+",
     *          "maxX": "\d+",
     *          "maxY": "\d+",
     *          "format": "[a-z]{3,}"
     *     }
     * )
     * @ParamConverter("media", options={"id" = "mediaId"})
     */
    public function getImageThumbnailAction(Image $media, $maxX = 200, $maxY = 200, $format = 'jpg') {
        //TODO: add event for permission handling ?!
        /** @var ImagineInterface $imagine */
        $image = $this->imagine->read($media->getFile()->readStream())->thumbnail(new Box($maxX, $maxY));
        return StreamedResponse::create(function () use ($image, $format) {
                    $image->show($format);
                });
    }

}
