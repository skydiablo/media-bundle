<?php

declare(strict_types=1);

namespace SkyDiablo\MediaBundle\Service\MediaRouter\ControllerMediaRouter;

use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;

/**
 * Description of EncoderWrapper
 *
 * @author Volker von Hoesslin <volker.hoesslin@swsn.de>
 */
class EncoderWrapper implements EncoderAwareInterface {

    const NAME = 'skydiablo_media';

    /**
     * Gets the name of the encoder used to encode the password.
     *
     * If the method returns null, the standard way to retrieve the encoder
     * will be used instead.
     *
     * @return string
     */
    public function getEncoderName() {
        return self::NAME;
    }

}
