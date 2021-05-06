<?php declare(strict_types=1);

namespace Polus\Adr\Bref\Actions;

trait EmptyActionTrait
{
    public function getInput(): ?string
    {
        return null;
    }

    public function getResponder(): ?string
    {
        return null;
    }
}
