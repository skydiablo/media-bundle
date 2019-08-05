<?php

namespace SkyDiablo\MediaBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use SkyDiablo\DoctrineBundle\ORM\Repository\BaseRepository;
use SkyDiablo\MediaBundle\Entity\Mime;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MimeRepository
 */
class MimeRepository extends BaseRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Mime::class);
    }

    /**
     * @param $mimeType
     * @return Mime|null
     */
    public function getByMimeType($mimeType) {
        $qb = $this->createQueryBuilder();
        return $qb
                        ->where(
                                $qb->expr()->eq($this->entityField('type'), ':type')
                        )
                        ->setParameter('type', $mimeType, Type::STRING)
                        ->getQuery()->getOneOrNullResult();
    }

}
