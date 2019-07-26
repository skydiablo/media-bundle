<?php

namespace SkyDiablo\MediaBundle\Service\MediaRouter\SkyDiabloImageResizer;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use SkyDiablo\MediaBundle\Service\MediaRouter\MediaRouterInterface;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class SkyDiabloImageResizerRouter
 */
class Router implements MediaRouterInterface {

    private $endpoint;

    /**
     * SkyDiabloImageResizerRouter constructor.
     * @param $endpoint
     */
    public function __construct($endpoint) {
        $this->endpoint = rtrim($endpoint, '/');
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime) {
        if ($media->getMime()->getType() !== Mime::TYPE_IMAGE_GIF) {
            return sprintf(
                    '%s/resize/%d/%d/%s',
                    $this->endpoint,
                    $dimension->getWidth(),
                    $dimension->getHeight(),
                    ltrim($media->getFilename(), '/')
            );
        } else {
            throw new \DomainException(sprintf('Mime type %s not supported by %s', $media->getMime(), get_class($this)));
        }
    }

}
