<?php

namespace SkyDiablo\MediaBundle\Serializer\Event;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use SkyDiablo\MediaBundle\Entity\Image;
use SkyDiablo\MediaBundle\Service\SourceCollectionService;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaEvent
 */
class MediaEvent implements EventSubscriberInterface {

    /**
     * @var SourceCollectionService
     */
    private $sourceCollectionService;

    /**
     * MediaEvent constructor.
     * @param SourceCollectionService $sourceCollectionService
     */
    public function __construct(SourceCollectionService $sourceCollectionService) {
        $this->sourceCollectionService = $sourceCollectionService;
    }

    /**
     * Returns the events to which this class has subscribed.
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            ['event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize', 'class' => Image::class],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event) {
        $image = $event->getObject();
        if ($image instanceof Image) {
            $collection = $this->sourceCollectionService->generateImageCollection(
                    $image,
                    [200, 400, 800] //todo: set as parameter
            );
            /** @var JsonSerializationVisitor $visitor */
            $visitor = $event->getVisitor();
            $visitor->setData('source', array_values($collection));
        }
    }

}
