<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class SkyDiabloControllerMediaRouter
 */
class SkyDiabloControllerMediaRouter implements MediaRouterInterface
{

    const ROUTE_NAME_IMAGE_THUMBNAIL = 'get_image_thumbnail';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * SkyDiabloControllerMediaRouter constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        $params = [
            'mediaId' => $media->getId(),
            'maxX' => $dimension->getWidth(),
            'maxY' => $dimension->getHeight(),
            'format' => $mime->getExtension(),
        ];

        $url = $this->router->generate(self::ROUTE_NAME_IMAGE_THUMBNAIL, $params, RouterInterface::ABSOLUTE_URL);
        return $url;
    }
}