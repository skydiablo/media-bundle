<?php

namespace SkyDiablo\MediaBundle\Command;

use AppBundle\Tool\StringTool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\StreamWrapper;
use League\Flysystem\Filesystem;
use SkyDiablo\MediaBundle\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class ReloadMediaSourceCommand
 */
class ReloadMediaSourceCommand extends ContainerAwareCommand
{

    const NAME = 'skydiablo.media.reload-source';
    const ARGUMENT_MEDIA_IDS = 'media-ids';

    protected function configure()
    {
        parent::configure();
        $this
            ->setName(self::NAME)
            ->addArgument(self::ARGUMENT_MEDIA_IDS, InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Media Ids');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Reload Media Source');

        $mediaRepository = $this->getContainer()->get('skydiablo_media.entity_repository.media_repository');
        $mediaList = $mediaRepository->getByIds($input->getArgument(self::ARGUMENT_MEDIA_IDS));

        /** @var Filesystem $memoryFilesystem */
        $memoryFilesystem = $this->getContainer()->get('oneup_flysystem.memory_filesystem');
        $httpClient = new Client();

        /** @var Media $media */
        foreach ($mediaList as $media) {
            if ($source = $media->getSource()) {
                if (StringTool::beginWith($source, 'http')) {
                    $io->text(sprintf('Reload media (%d) source: "%s"', $media->getId(), $source));

                    //download
                    $tmpFilename = uniqid();
                    $memoryFilesystem->putStream(
                        $tmpFilename, // storage id
                        StreamWrapper::getResource( // convert PSR7 stream to PHP native resource
                            $httpClient->get($source)->getBody() // download from HTTP source
                        )
                    ); // persist in local memory
                    $file = $memoryFilesystem->get($tmpFilename); // retrieve file object from filesystem

                    $media->setFile($file, true);
                    $media->save(); // will move/copy file to internal storage
                    $file->delete(); // free memory
                } else {
                    $io->warning(sprintf('Given source "%s" is not a valid HTTP source', $source));
                }
            } else {
                $io->note(sprintf('There is no source for media "%d" available', $media->getId()));
            }
        }

        $io->text('done');
    }


}