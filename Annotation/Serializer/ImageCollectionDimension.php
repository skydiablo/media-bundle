<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Annotation\Serializer;

/**
 * Description of ImageCollectionDimension
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ImageCollectionDimension {

    private $dimensions;
    private $mime;

    public function __construct(array $data) {
        if ($value = $data['value']) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->dimensions = $value;
        } else {
            $this->dimensions = ['*'];
        }
        $this->mime = $data['mime'] ?? '*';
    }

    public function getDimensions(): array {
        return $this->dimensions;
    }

    public function getMime() {
        return $this->mime;
    }

}
