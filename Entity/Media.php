<?php

namespace SkyDiablo\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use SkyDiablo\MediaBundle\Model\FlySystem\File;
use SkyDiablo\DoctrineBundle\ORM\Entity\ActiveEntity;
use SkyDiablo\DoctrineBundle\ORM\Entity\Traits\CreatedAtInterface;
use SkyDiablo\DoctrineBundle\ORM\Entity\Traits\CreatedAtTrait;
use SkyDiablo\MediaBundle\Entity\Repository\MediaRepository;
use SkyDiablo\MediaBundle\Model\Traits\SerializerObjectTypeTrait;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class Media
 * @ORM\Entity(repositoryClass="MediaRepository")
 * @ORM\Table(name="skydiablo_media")
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=64)
 * @ORM\EntityListeners({"SkyDiablo\MediaBundle\Entity\Listener\MediaListener"})
 * @Serializer\Discriminator(
 *     field="type",
 *     map={
 *         "Image": "SkyDiablo\MediaBundle\Entity\Image"
 *     }
 *  )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
abstract class Media extends ActiveEntity implements CreatedAtInterface {

    use SerializerObjectTypeTrait,
        CreatedAtTrait;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=512, nullable=false)
     * @Serializer\Groups({"API_STORE_EXTENSION"})
     * @Serializer\Type("string")
     */
    private $filename;

    /**
     * @var File
     */
    private $file;

    /**
     * @var int
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size = 0;

    /**
     * @var Mime
     * @ORM\Cache("READ_ONLY")
     * @ORM\ManyToOne(targetEntity="Mime", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $mime;

    /**
     * Media constructor.
     * @param File $file
     * @param Mime $mime
     */
    public function __construct(File $file = null, Mime $mime = null) {
        $this->setFile($file);
        $this->mime = $mime;
    }

    public function __clone()
    {
        parent::__clone();
        $this->createdAt = null;
    }


    /**
     * @return File
     */
    public function getFile(): File {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * @return Mime
     */
    public function getMime(): Mime {
        return $this->mime;
    }

    /**
     * @internal only for internal usage
     * @param File $file
     * @param bool $forceUpdateMetadata
     * @return Media
     */
    public function setFile(File $file, bool $forceUpdateMetadata = false): Media {
        $this->file = $file;
        $this->filename = $file->getPath();
        if ($forceUpdateMetadata || !$this->id) { // only set meta data on new media object - this will/should never change later!
            $this->size = $file->getSize(); //beware: this will always fetch the data from origin source or connect remote storage, not a good idea for S3 or other remote sources...
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @internal see \SkyDiablo\MediaBundle\Entity\Listener\MediaListener::onPreUpdate
     */
    public function setFilename(string $filename) {
        $this->filename = $filename;
    }

}
