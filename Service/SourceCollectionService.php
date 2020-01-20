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
class SourceCollectionService
{

    const DIMENSIONS_PRESET_SMALL = [200, 200];

    /**
     * @var MediaRouterInterface
     */
    private $mediaRouter;

    /**
     * SourceCollectionService constructor.
     * @param MediaRouterInterface $mediaRouter
     */
    public function __construct(MediaRouterInterface $mediaRouter)
    {
        $this->mediaRouter = $mediaRouter;
    }

    /**
     *
     * @param Image $media
     * @param int[] $collectionMaxDimensions
     * @param Mime $mime
     * @return MediaSource[]
     */
    public function generateImageCollectionByMaxDimensions(Image $media, array $collectionMaxDimensions, Mime $mime = null)
    {
        $result = [];
        $originalBox = new Box($media->getDimension()->getWidth(), $media->getDimension()->getHeight());
        $ratio = $originalBox->getWidth() / $originalBox->getHeight();
        /** @var Mime $destinationMime */
        $destinationMime = $mime ?? $media->getMime();
        foreach ($collectionMaxDimensions AS $maxDimension) {
            if ($ratio < 1) {
                $box = $originalBox->heighten($maxDimension);
            } else {
                $box = $originalBox->widen($maxDimension);
            }
            $dimension = new Dimension($box->getWidth(), $box->getHeight());

            $url = $this->mediaRouter->generateRoute($media, $dimension, $destinationMime);

            $result[$destinationMime->getType() . '#' . $dimension->hash()] = new MediaSource(
                $url, $dimension, $destinationMime
            );
        }
        return $result;
    }

    /**
     * @param Image $media
     * @param Dimension[] $collectionDimensions
     * @param Mime $mime
     * @return MediaSource[]
     */
    public function generateImageCollectionByDimensions(Image $media, array $collectionDimensions, Mime $mime = null)
    {
        $result = [];
        $originalBox = new Box($media->getDimension()->getWidth(), $media->getDimension()->getHeight());
        $ratio = $originalBox->getWidth() / $originalBox->getHeight();
        /** @var Mime $destinationMime */
        $destinationMime = $mime ?? $media->getMime();
        foreach ($collectionDimensions AS $dimension) {
            $box = null;
            if ($dimension->getHeight() && $dimension->getWidth()) {
                $box = $originalBox->heighten($dimension->getHeight());
                if ($box->getWidth() > $dimension->getWidth()) {
                    $box = $originalBox->widen($dimension->getWidth());
                }
            } elseif ($dimension->getHeight()) {
                $box = $originalBox->heighten($dimension->getHeight());
            } elseif ($dimension->getWidth()) {
                $box = $originalBox->widen($dimension->getWidth());
            }

            if ($box instanceof Box) {

                // prevent upscaling
                if(!$originalBox->contains($box)) {
                    $box = $originalBox;
                }

                $dimension = new Dimension($box->getWidth(), $box->getHeight());
                $url = $this->mediaRouter->generateRoute($media, $dimension, $destinationMime);
                $result[$destinationMime->getType() . '#' . $dimension->hash()] = new MediaSource(
                    $url, $dimension, $destinationMime
                );
            }
        }
        return $result;
    }

}
