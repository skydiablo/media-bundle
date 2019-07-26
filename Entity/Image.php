<?php


namespace SkyDiablo\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use League\Flysystem\File;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class Image
 * @ORM\Entity()
 * @ORM\Table(name="skydiablo_media_image")
 */
class Image extends Media
{
    /**
     * @var Dimension
     * @ORM\Embedded(class="SkyDiablo\MediaBundle\Entity\Embeddables\Dimension", columnPrefix="dimension_")
     */
    private $dimension;

    /**
     * Image constructor.
     * @param File $file
     * @param Mime $mime
     * @param Dimension $dimension
     */
    public function __construct(File $file = null, Mime $mime = null, Dimension $dimension = null)
    {
        parent::__construct($file, $mime);
        $this->dimension = $dimension;
    }

    /**
     * @return Dimension
     */
    public function getDimension(): Dimension
    {
        return $this->dimension;
    }

}