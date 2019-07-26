<?php

namespace SkyDiablo\MediaBundle\Model\Traits;

use JMS\Serializer\Annotation as Serializer;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class SerilizerObjectTypeTrait
 */
trait SerializerObjectTypeTrait {

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("type")
     * @Serializer\Groups({"API_DEFAULT"})
     */
    public function serializerObjectType() {
        return $this->generateSerializeTypeValue();
    }

    /**
     * @return string
     */
    protected function generateSerializeTypeValue() {
        return (new \ReflectionClass($this))->getShortName();
    }

}
