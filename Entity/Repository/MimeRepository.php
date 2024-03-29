<?php

namespace SkyDiablo\MediaBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use SkyDiablo\DoctrineBundle\ORM\Repository\BaseRepository;
use SkyDiablo\MediaBundle\Entity\Mime;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MimeRepository
 */
class MimeRepository extends BaseRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mime::class);
    }

    /**
     * @param $mimeType
     * @return Mime|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByMimeType($mimeType)
    {
        $qb = $this->createQueryBuilder();
        return $qb
            ->where(
                $qb->expr()->eq($this->entityField('type'), ':type')
            )
            ->setParameter('type', $mimeType, Types::STRING)
            ->getQuery()->getOneOrNullResult();
    }

}
