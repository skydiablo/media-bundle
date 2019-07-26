<?php


namespace SkyDiablo\MediaBundle\Entity\Embeddables;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class Dimension
 * @ORM\Embeddable()
 */
class Dimension implements \JsonSerializable
{

    /**
     * @var int
     * @ORM\Column(name="width", type="integer", nullable=false)
     */
    private $width = 0;

    /**
     * @var int
     * @ORM\Column(name="height", type="integer", nullable=false)
     */
    private $height = 0;

    /**
     * Dimension constructor.
     * @param int $width
     * @param int $height
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }


    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight() : int
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function hash()
    {
        return sprintf('%dx%d', $this->width, $this->height);
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
            'width' => $this->width,
            'height' => $this->height
        ];
    }
}