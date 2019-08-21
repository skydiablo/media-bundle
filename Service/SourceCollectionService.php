<?php

namespace SkyDiablo\MediaBundle\Service;

use Imagine\Image\Box;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Image;
use SkyDiablo\MediaBundle\Entity\Mime;
use SkyDiablo\MediaBundle\Model\MediaSource;
use SkyDiablo\MediaBundle\Service\MediaRouter\MediaRouterInterface;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class SourceCollectionService
 */
class SourceCollectionService {

    const DIMENSIONS_PRESET_SMALL = [200, 200];

    /**
     * @var MediaRouterInterface
     */
    private $mediaRouter;

    /**
     * SourceCollectionService constructor.
     * @param MediaRouterInterface $mediaRouter
     */
    public function __construct(MediaRouterInterface $mediaRouter) {
        $this->mediaRouter = $mediaRouter;
    }

    /**
     * 
     * @param Image $media
     * @param array $collectionMaxDimensions
     * @param Mime $mime
     * @return MediaSource
     */
    public function generateImageCollection(Image $media, array $collectionMaxDimensions, Mime $mime = null) {
        $result = [];
        $originalBox = new Box($media->getDimension()->getWidth(), $media->getDimension()->getHeight());
        $ratio = $originalBox->getWidth() / $originalBox->getHeight();
        foreach ($collectionMaxDimensions AS $maxDimension) {
            if ($ratio < 1) {
                $box = $originalBox->heighten($maxDimension);
            } else {
                $box = $originalBox->widen($maxDimension);
            }
            $dimension = new Dimension($box->getWidth(), $box->getHeight());
            /** @var Mime $destinationMime */
            $destinationMime = $mime ?? $media->getMime();

            $url = $this->mediaRouter->generateRoute($media, $dimension, $destinationMime);

            $result[$destinationMime->getType() . '#' . $dimension->hash()] = new MediaSource(
                    $url, $dimension, $destinationMime
            );
        }
        return $result;
    }

}
