<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Service;

use SkyDiablo\MediaBundle\Entity\Mime;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * Description of MimeGuesser
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 */
class MimeGuesser {

    /**
     * @var MimeTypesInterface
     */
    private $mimeTypes;

    function __construct() {
        $this->mimeTypes = new MimeTypes();
    }

    /**
     * @param string $input
     * @return Mime
     */
    public function guess(string $input) {
        if (false !== \strpos($input, '/')) {
            $mime = new Mime($input, $this->mimeTypes->getExtensions($input)[0]);
        } else {
            $mime = new Mime($this->mimeTypes->getMimeTypes($input)[0], $input);
        }
        return $mime;
    }

}
