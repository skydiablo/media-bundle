<?php


namespace SkyDiablo\MediaBundle\Entity\Repository;

use AppBundle\Entity\User\User;
use Doctrine\DBAL\Types\Type;
use SkyDiablo\DoctrineBundle\ORM\Repository\BaseRepository;
use SkyDiablo\MediaBundle\Entity\Media;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class MediaRepository
 * @method Media[] getAll($amount = null, $offset = null, $order = null, $orderField = 'id')
 */
class MediaRepository extends BaseRepository
{

    /**
     * @param $filename
     * @return Media|null
     */
    public function byFilenme($filename)
    {
        $qb = $this->createQueryBuilder();
        return $qb
            ->where(
                $qb->expr()->eq($this->entityField('filename'), ':filename')
            )
            ->setParameter('filename', $filename, Type::STRING)
            ->getQuery()->getOneOrNullResult();
    }

}