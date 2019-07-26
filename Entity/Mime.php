<?php


namespace SkyDiablo\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use SkyDiablo\DoctrineBundle\ORM\Entity\Entity;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class Mime
 * @ORM\Entity(repositoryClass="SkyDiablo\MediaBundle\Entity\Repository\MimeRepository")
 * @ORM\Table(name="skydiablo_media_mime")
 * @ORM\Cache(usage="READ_ONLY")
 */
class Mime extends Entity
{

    const TYPE_IMAGE_JPEG = 'image/jpeg';
    const EXTENSION_JPEG = 'jpg';

    const TYPE_IMAGE_GIF = 'image/gif';
    const EXTENSION_GIF = 'gif';


    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=32, nullable=false)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="extension", type="string", length=8, nullable=false)
     */
    private $extension;

    /**
     * Mime constructor.
     * @param string $type
     * @param string $extension
     */
    public function __construct(string $type, string $extension)
    {
        $this->type = strtolower($type);
        $this->extension = strtolower($extension);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    public function __toString() {
        return $this->getType();
    }

}