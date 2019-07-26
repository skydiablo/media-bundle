<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use SkyDiablo\CoreBundle\Service\Logger\LoggableInterface;
use SkyDiablo\CoreBundle\Service\Logger\LoggableTrait;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class ChainMediaRouter
 */
class ChainMediaRouter implements MediaRouterInterface, LoggableInterface
{

    use LoggableTrait;

    /**
     * @var MediaRouterInterface[]
     */
    private $mediaRouters;

    /**
     * ChainMediaRouter constructor.
     * @param MediaRouterInterface[] $mediaRouters
     */
    public function __construct(array $mediaRouters)
    {
        $this->mediaRouters = $mediaRouters;
    }


    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        foreach ($this->mediaRouters AS $mediaRouter) {
            if (filter_var($url = $this->handleMediaRouter($mediaRouter, $media, $dimension, $mime))) {
                return $url;
            }
        }
    }

    /**
     * @param MediaRouterInterface $mediaRouter
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string
     */
    protected function handleMediaRouter($mediaRouter, Media $media, Dimension $dimension, Mime $mime)
    {
        try {
            return $mediaRouter->generateRoute($media, $dimension, $mime);
        } catch (\Exception $e) {
            $this->logWarning('Primary MediaRouter can not generate an valid URL', [$e, $media, $dimension, $mime]);
        }
    }

}