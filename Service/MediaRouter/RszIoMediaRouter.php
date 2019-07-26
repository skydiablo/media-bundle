<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class RszIoMediaRouter
 */
class RszIoMediaRouter implements MediaRouterInterface
{

    const URL_TEMPLATE = 'http://{HOST}.rsz.io{PATH}?{QUERY}&w={WIDTH}&h={HEIGHT}&mode=max&format={FORMAT}';

    /**
     * @var MediaRouterInterface
     */
    private $preMediaRouter;

    /**
     * RszIoMediaRouter constructor.
     * @param MediaRouterInterface $preMediaRouter
     */
    public function __construct(MediaRouterInterface $preMediaRouter)
    {
        $this->preMediaRouter = $preMediaRouter;
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        $originUrl = $this->preMediaRouter->generateRoute($media, $dimension, $mime);
        $urlParts = parse_url($originUrl);
        $replace = [
            '{HOST}' => $urlParts['host'] ?? null,
            '{PATH}' => $urlParts['path'] ?? null,
            '{WIDTH}' => $dimension->getWidth(),
            '{HEIGHT}' => $dimension->getHeight(),
            '{QUERY}' => $urlParts['query'] ?? null,
            '{FORMAT}' => $mime->getExtension()
        ];
        $url = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getURLTemplate()

        );
        return $url;
    }

    /**
     * @return string
     */
    protected function getURLTemplate() {
        return self::URL_TEMPLATE;
    }
}