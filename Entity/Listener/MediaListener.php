<?php

namespace SkyDiablo\MediaBundle\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\File;
use League\Flysystem\FileNotFoundException;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Service\MediaStorageService;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaListener
 */
class MediaListener {

    const MEDIA_DEFAULT_BASE_PATH = 'skydiablo/media';

    /**
     * @var MediaStorageService
     */
    private $mediaStorageService;

    /**
     * @var \SplObjectStorage
     */
    private $updateFiles;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @param MediaStorageService $mediaStorageService
     * @param string $basePath
     * @todo refactor basepath to an path generator (object or closure)
     */
    public function __construct(MediaStorageService $mediaStorageService, string $basePath = self::MEDIA_DEFAULT_BASE_PATH) {
        $this->mediaStorageService = $mediaStorageService;
        $this->updateFiles = new \SplObjectStorage();
        $this->basePath = $basePath;
    }

    /**
     * generate an unique not existing filename in database
     * @param Media $media
     * @return string
     */
    protected function generateUniqueFilename(Media $media) {
        return implode('/', [
            $this->basePath,
            $media->getId()
        ]);
    }

    /**
     * on new media entity, store/move file in skydiablo storage
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @ORM\PostPersist()
     */
    public function onPostPersist(Media $media, LifecycleEventArgs $eventArgs) {
        $this->copyFileIntoInternalStorage($media);
    }

    /**
     * @param Media $media
     * @param PreUpdateEventArgs $eventArgs
     * @ORM\PreUpdate()
     */
    public function onPreUpdate(Media $media, PreUpdateEventArgs $eventArgs) {
        if ($eventArgs->hasChangedField('filename')) { // is this a good identifier?
            $destinationFilename = $this->generateUniqueFilename($media);

            $eventArgs->setNewValue('filename', $destinationFilename);
            $media->setFilename($destinationFilename);

            $this->updateFiles->attach($media, true);
        }
    }

    /**
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @ORM\PostUpdate()
     */
    public function onPostUpdate(Media $media, LifecycleEventArgs $eventArgs) {
        if ($this->updateFiles->contains($media)) {
            try {
                $this->copyFileIntoInternalStorage($media, true);
            } finally {
                $this->updateFiles->detach($media);
            }
        }
    }

    /**
     * @param Media $media
     * @param bool $forceUpdateMetadata
     */
    protected function copyFileIntoInternalStorage(Media $media, bool $forceUpdateMetadata = false) {
        $destinationFilename = $this->generateUniqueFilename($media);
        if ($this->mediaStorageService->copyFileIntoStorage($media->getFile(), $destinationFilename)) {
            /** @var File $file */
            $file = $this->mediaStorageService->getFileByFilename($destinationFilename);
            $media->setFile($file, $forceUpdateMetadata);
            $media->save();
        } else {
            throw new \RuntimeException('Can not move media file from "%s" to "%s"', $media->getFile()->getPath(), $destinationFilename);
        }
    }

    /**
     * on entity load from database, create file object for given file-path
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @ORM\PostLoad()
     */
    public function onPostLoad(Media $media, LifecycleEventArgs $eventArgs) {
        /** @var File $file */
        $file = $this->mediaStorageService->getFileByMedia($media);
        $media->setFile($file);
    }

    /**
     * on entity delete, also delete related file
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @ORM\PostRemove()
     */
    public function onPostRemove(Media $media, LifecycleEventArgs $eventArgs) {
        try {
            $media->getFile()->delete();
        } catch (FileNotFoundException $e) {
            // iggen - file already removed?
        }
        //todo: remove empty dirs ?
    }

}
