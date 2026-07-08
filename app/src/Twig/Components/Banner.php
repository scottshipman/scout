<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Banner
{
    public string $title = 'Kenneth Scott Shipman';

    public string $subtitle = 'Experienced Software Development Leader & Solutions Architect, AWS Cloud';
}
