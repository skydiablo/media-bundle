<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use Imgix\ShardStrategy;
use Imgix\UrlBuilder;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class ImgIXComMediaRouter
 */
class ImgIXComMediaRouter implements MediaRouterInterface
{

    const FIT_CLIP = 'clip';

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * only the basename of media filename will be used to generate the imgix-url
     * @var bool
     */
    private $useBasename;

    /**
     * ImgIXComMediaRouter constructor.
     * @param string $secureUrlToken
     * @param string $domain
     * @param bool $useBasename
     */
    public function __construct(string $domain, string $secureUrlToken, bool $useBasename = true)
    {
        $this->useBasename = $useBasename;
        $this->urlBuilder = new UrlBuilder($domain, true, $secureUrlToken, ShardStrategy::CRC, false);
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        //https://docs.imgix.com/apis/url
        $params = [
            'w' => $dimension->getWidth(),
            'h' => $dimension->getHeight(),
            'fm' => $mime->getExtension(), //https://docs.imgix.com/apis/url/format/fm
            'fit' => self::FIT_CLIP, //https://docs.imgix.com/apis/url/size/fit
        ];
        return $this->urlBuilder->createURL(
            $this->useBasename ? basename($media->getFilename()) : $media->getFilename(),
            $params
        );
    }
}