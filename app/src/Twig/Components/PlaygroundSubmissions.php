<?php

namespace App\Twig\Components;

use App\Entity\PlaygroundSubmit;
use App\Repository\PlaygroundSubmitRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PlaygroundSubmissions
{
    public function __construct(private PlaygroundSubmitRepository $repository)
    {
    }

    /**
     * @return PlaygroundSubmit[]
     */
    public function getSubmissions(): array
    {
        return $this->repository->findBy([], ['dateCreated' => 'DESC']);
    }
}
