<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class ContainerAwareChainMediaRouter
 */
class ContainerAwareChainMediaRouter extends ChainMediaRouter implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @param string $mediaRouterServiceId
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string
     */
    protected function handleMediaRouter($mediaRouterServiceId, Media $media, Dimension $dimension, Mime $mime)
    {
        /** @var MediaRouterInterface $mediaRouter */
        $mediaRouter = $this->container->get($mediaRouterServiceId);
        return parent::handleMediaRouter($mediaRouter, $media, $dimension, $mime);
    }


}