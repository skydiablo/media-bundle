<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * this is just an distribution router without processing like image resizing...
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class S3MediaRouter
 */
class S3MediaRouter implements MediaRouterInterface
{

    const URL_TEMPLATE = 'http://s3-{REGION}.amazonaws.com/{BUCKET}/{PATH}';

    /**
     * AWS region e.g.: eu-west-1
     * @var string
     */
    private $region;

    /**
     * S3MediaRouter constructor.
     * @param string $region AWS S3 region, e.g.: eu-west-1
     */
    public function __construct(string $region)
    {
        $this->region = $region;
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime)
    {
        $filesystem = $media->getFile()->getFilesystem();
        if (($filesystem instanceof Filesystem) && (($adapter = $filesystem->getAdapter()) instanceof AwsS3Adapter)) {
            /** @var AwsS3Adapter $adapter */
            $url = str_replace(
                ['{REGION}', '{BUCKET}', '{PATH}'],
                [$this->region, $adapter->getBucket(), $adapter->getPathPrefix() . $media->getFilename()],
                self::URL_TEMPLATE
            );
            return $url;
        } else {
            throw new \DomainException('Invalid Filesystem!');
        }
    }
}