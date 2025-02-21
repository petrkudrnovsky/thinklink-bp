<?php

namespace App;

use Doctrine\ORM\EntityManager;
use Pgvector\Doctrine\PgvectorSetup;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        # Source: https://github.com/pgvector/pgvector-php?tab=readme-ov-file#doctrine for PgvectorSetup
        # Source: https://stackoverflow.com/questions/30528792/running-code-on-symfony-kernel-initialisation for overriding the boot method
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get(EntityManager::class);
        PgvectorSetup::registerTypes($entityManager);
    }
}
