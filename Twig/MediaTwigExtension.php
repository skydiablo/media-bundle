<?php

namespace SkyDiablo\MediaBundle\Twig;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaTwigExtension
 */
class MediaTwigExtension extends AbstractExtension implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    const MEDIA_ROUTER_SERVICE_ID = 'skydiablo_media.service_media_router';
    const FUNCTION_MEDIA_URL = 'mediaUrl';

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(self::FUNCTION_MEDIA_URL, [$this, 'generateMediaUrl'])
        ];
    }

    /**
     * @param Media $media
     * @param int $maxWidth
     * @param int $maxHeight
     * @return string Media URL
     * @todo add dynamic way to define output format/mimetype
     */
    public function generateMediaUrl(Media $media, int $maxWidth, int $maxHeight)
    {
        $mediaRouter = $this->container->get(self::MEDIA_ROUTER_SERVICE_ID);
        return $mediaRouter->generateRoute($media, new Dimension($maxWidth, $maxHeight), new Mime('image/jpeg', 'jpg'));
    }

}
