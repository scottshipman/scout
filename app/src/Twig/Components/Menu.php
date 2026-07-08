<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Menu
{
    /**
     * @return array<int, array{label: string, path: string, external: bool}>
     */
    public function getItems(): array
    {
        return [
            ['label' => 'Home', 'path' => 'app_home', 'external' => false],
            ['label' => 'Resume', 'path' => '/files/kenneth-shipman-resume.pdf', 'external' => true],
            ['label' => 'Playground', 'path' => 'app_playground', 'external' => false],
        ];
    }
}
