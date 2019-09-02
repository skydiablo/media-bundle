<?php

namespace SkyDiablo\MediaBundle\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use League\Flysystem\Util;
use SkyDiablo\MediaBundle\Entity\Media;
use SkyDiablo\MediaBundle\Model\FlySystem\File;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaService
 */
class MediaStorageService {

    const FILESYSTEM_SKYDIABLO = 'skydiablo';
    const FILESYSTEM_SOURCE = 'source';

    /**
     * @var FilesystemInterface|Filesystem
     */
    private $filesystem;

    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * MediaService constructor.
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem) {
        $this->filesystem = $filesystem;
        $this->mountManager = new MountManager([self::FILESYSTEM_SKYDIABLO => $this->filesystem]);
    }

    /**
     * @param Media $media
     * @return File
     */
    public function getFileByMedia(Media $media) {
        return $this->getFileByFilename($media->getFilename());
    }

    /**
     * @param string $filename
     * @return File
     */
    public function getFileByFilename(string $filename) {
        $filename = Util::normalizePath($filename);
        return new File($this->filesystem, $filename);
    }

    /**
     * Ist File und Ziel bereits identisch
     * @param File $file
     * @param string $destinationFilename
     * @return bool
     */
    public function isFileEaqualToDestination(File $file, string $destinationFilename) {
        return ($file->getFilesystem() === $this->filesystem) &&
                ($file->getPath() === $destinationFilename);
    }

    /**
     * @param File $sourceFile
     * @param $destinationFilename
     * @return bool
     */
    public function copyFileIntoStorage(File $sourceFile, $destinationFilename) {
        $this->mountManager->mountFilesystem(self::FILESYSTEM_SOURCE, $sourceFile->getFilesystem());
        try {
            $disableAsserts = $this->filesystem->getConfig()->get('disable_asserts', false);
            $this->filesystem->getConfig()->set('disable_asserts', true); // ignore existing files
            return $this->mountManager->copy(
                            $this->prefixMount(self::FILESYSTEM_SOURCE, $sourceFile->getPath()),
                            $this->prefixMount(self::FILESYSTEM_SKYDIABLO, $destinationFilename),
                            ['ContentType' => $sourceFile->getMimetype()]
            );
        } finally {
            $this->filesystem->getConfig()->set('disable_asserts', $disableAsserts);
        }
    }

    /**
     * @param File $sourceFile
     * @param string $destinationFilename
     * @return bool
     */
    public function moveFileIntoStorage(File $sourceFile, string $destinationFilename) {
        $this->mountManager->mountFilesystem(self::FILESYSTEM_SOURCE, $sourceFile->getFilesystem());
        return $this->mountManager->move(
                        $this->prefixMount(self::FILESYSTEM_SOURCE, $sourceFile->getPath()),
                        $this->prefixMount(self::FILESYSTEM_SKYDIABLO, $destinationFilename)
        );
    }

    /**
     * @param $mount
     * @param $path
     * @return string
     */
    protected function prefixMount($mount, $path) {
        return sprintf('%s://%s', $mount, ltrim($path, '/'));
    }

}
