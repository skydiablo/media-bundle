<?php

namespace SkyDiablo\MediaBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Mime;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaSource
 */
class MediaSource implements \JsonSerializable
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var Dimension
     */
    private $dimension;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * MediaSource constructor.
     * @param string $url
     * @param Dimension $dimension
     * @param Mime $mime
     */
    public function __construct(string $url, Dimension $dimension, Mime $mime)
    {
        $this->url = $url;
        $this->dimension = $dimension;
        $this->mime = $mime;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return Dimension
     */
    public function getDimension(): Dimension
    {
        return $this->dimension;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'url' => $this->url,
            'mime' => $this->mime->getType(),
            'dimension' => $this->dimension
        ];
    }
}