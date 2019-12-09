<?php

namespace SkyDiablo\MediaBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use SkyDiablo\DoctrineBundle\ORM\Repository\BaseRepository;
use SkyDiablo\MediaBundle\Entity\Media;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaRepository
 * @method Media[] getAll($amount = null, $offset = null, $order = null, $orderField = 'id')
 */
class MediaRepository extends BaseRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Media::class);
    }

    /**
     * @param $filename
     * @return Media|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function byFilenme($filename) {
        $qb = $this->createQueryBuilder();
        return $qb
                        ->where(
                                $qb->expr()->eq($this->entityField('filename'), ':filename')
                        )
                        ->setParameter('filename', $filename, Type::STRING)
                        ->getQuery()->getOneOrNullResult();
    }

}
