<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class CloudFrontMediaRouter
 */
class CloudFrontMediaRouter implements MediaRouterInterface
{

    const URL_TEMPLATE = '{PROTOCOL}://{HOST}/{MEDIA_ID}?w={WIDTH}&h={HEIGHT}&mode=max&format={FORMAT}';

    /**
     * runtime environment
     * @var string
     */
    private $host;

    /**
     * use https or http
     * @var bool
     */
    private $encrypted = true;

    /**
     * CloudFrontMediaRouter constructor.
     * @param string $host
     * @param bool $encrypted
     */
    public function __construct(string $host, bool $encrypted = true)
    {
        $this->host = $host;
        $this->encrypted = $encrypted;
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        $replace = [
            '{PROTOCOL}' => ($this->encrypted ? 'https' : 'http'),
            '{HOST}' => $this->host,
            '{MEDIA_ID}' => $media->getId(),
            '{WIDTH}' => $dimension->getWidth(),
            '{HEIGHT}' => $dimension->getHeight(),
            '{FORMAT}' => $mime->getExtension()
        ];
        $url = str_replace(
            array_keys($replace),
            array_values($replace),
            self::URL_TEMPLATE
        );

        return $url;
    }
}