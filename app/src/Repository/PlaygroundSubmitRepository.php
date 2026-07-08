<?php

namespace App\Repository;

use App\Entity\PlaygroundSubmit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaygroundSubmit>
 */
class PlaygroundSubmitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaygroundSubmit::class);
    }
}
