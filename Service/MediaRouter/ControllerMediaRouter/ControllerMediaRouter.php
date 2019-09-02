<?php

namespace SkyDiablo\MediaBundle\Service\MediaRouter\ControllerMediaRouter;

use SkyDiablo\MediaBundle\Controller\ImageController;
use SkyDiablo\MediaBundle\Entity\Embeddables\Dimension;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Entity\Mime;
use SkyDiablo\MediaBundle\Service\MediaRouter\MediaRouterInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class SkyDiabloControllerMediaRouter
 */
class ControllerMediaRouter implements MediaRouterInterface {

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $psk;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface 
     */
    private $encoder;

    function __construct(RouterInterface $router, $psk = null) {
        $this->router = $router;
        $this->psk = $psk;
        $encoderWrapper = new EncoderWrapper();
        $encoderFactory = new EncoderFactory([$encoderWrapper->getEncoderName() => ['algorithm' => 'ripemd128', 'encode_as_base64' => false, 'iterations' => 2]]);
        $this->encoder = $encoderFactory->getEncoder($encoderWrapper);
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @return string Resource URL
     */
    public function generateRoute(Media $media, Dimension $dimension, Mime $mime) {
        $params = [
            'mediaId' => $media->getId(),
            'hash' => $this->hashMediaParams($media, $dimension, $mime),
            'maxX' => $dimension->getWidth(),
            'maxY' => $dimension->getHeight(),
            'format' => $mime->getExtension(),
        ];

        $url = $this->router->generate(ImageController::ROUTE_NAME_IMAGE_THUMBNAIL, $params, RouterInterface::ABSOLUTE_URL);
        return $url;
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @param string $psk
     * @return string
     */
    protected function hashMediaParams(Media $media, Dimension $dimension, Mime $mime) {
        $haystack = $this->generateMediaParamsHaystack($media, $dimension, $mime, $this->psk);
        return $this->encoder->encodePassword($haystack, null);
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @param string $psk
     * @param string $hash
     * @return bool
     */
    public function validateMediaParams(Media $media, Dimension $dimension, Mime $mime, string $hash) {
        $haystack = $this->generateMediaParamsHaystack($media, $dimension, $mime, $this->psk);
        return $this->encoder->isPasswordValid($hash, $haystack, null);
    }

    /**
     * @param Media $media
     * @param Dimension $dimension
     * @param Mime $mime
     * @param string $psk
     * @return string
     */
    protected function generateMediaParamsHaystack(Media $media, Dimension $dimension, Mime $mime, string $psk) {
        $haystack = implode('|', [
            $psk,
            $media->getId(),
            $psk,
            $dimension->hash(),
            $psk,
            $mime->getType(),
            $psk
        ]);
        return $haystack;
    }

}
