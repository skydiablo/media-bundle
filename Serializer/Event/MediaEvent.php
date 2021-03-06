<?php

namespace SkyDiablo\MediaBundle\Serializer\Event;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use ReflectionClass;
use SkyDiablo\MediaBundle\Annotation\Serializer\ImageCollectionDimension;
use SkyDiablo\MediaBundle\Entity\Image;
use SkyDiablo\MediaBundle\Service\SourceCollectionService;
use SkyDiablo\MediaBundle\Service\MimeGuesser;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaEvent
 */
class MediaEvent implements EventSubscriberInterface
{

    const SOURCE_KEY = 'source';

    /**
     * @var SourceCollectionService
     */
    private $sourceCollectionService;

    /**
     *
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var MimeGuesser
     */
    private $mimeGuesser;

    public function __construct(SourceCollectionService $sourceCollectionService, Reader $annotationReader)
    {
        $this->sourceCollectionService = $sourceCollectionService;
        $this->annotationReader = $annotationReader;
        $this->mimeGuesser = new MimeGuesser();
    }

    /**
     * Returns the events to which this class has subscribed.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize', 'class' => Image::class],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $image = $event->getObject();
        if ($image instanceof Image) {
            /** @var JsonSerializationVisitor $visitor */
            $visitor = $event->getVisitor();
            if ($visitor->hasData(self::SOURCE_KEY)) { //already defined
                return;
            }

            $originalMaxDimension = max([$image->getDimension()->getHeight(), $image->getDimension()->getWidth()]);
            $annotations = $this->getImageCollectionDimensions($event);
            $collection = [];
            foreach ($annotations as $annotation) {
                $dimensions = [$originalMaxDimension];
                if ($annotation instanceof ImageCollectionDimension) {
                    $uniqueArray = array_unique($annotation->getDimensions());
                    $transArray = array_combine($uniqueArray, $uniqueArray);
                    if (isset($transArray['*'])) {
                        $transArray['*'] = $originalMaxDimension;
                    }
                    $dimensions = array_values($transArray);
                }

                $annotatedMime = $annotation->getMime();
                if ($annotatedMime === '*') {
                    $mime = $image->getMime(); // or null as fallback?
                } else {
                    $mime = $this->mimeGuesser->guess($annotatedMime);
                }
                $collection += $this->sourceCollectionService->generateImageCollectionByMaxDimensions(
                    $image,
                    $dimensions,
                    $mime
                );
            }

            if (!$collection) {
                $collection = $this->sourceCollectionService->generateImageCollectionByMaxDimensions(
                    $image,
                    [$originalMaxDimension]
                );
            }

            $visitor->setData(self::SOURCE_KEY, array_values($collection)); //deprecated
//            $visitor->visitProperty(new StaticPropertyMetadata('', 'source', array_values($collection)), array_values($collection)); //TODO: funzt ned :(
        }
    }

    /**
     * @param ObjectEvent $event
     * @return ImageCollectionDimension[]
     */
    protected function getImageCollectionDimensions(ObjectEvent $event): array
    {
        $meta = $event->getContext()->getMetadataStack()->top();
        $rClass = new ReflectionClass($meta->class);
        $property = $rClass->getProperty($meta->name);
        $annotations = $this->annotationReader->getPropertyAnnotations($property) ?? [];
        return array_filter(array_map(function ($anno) {
            if ($anno instanceof ImageCollectionDimension) {
                return $anno;
            }
        }, $annotations));
    }

}
