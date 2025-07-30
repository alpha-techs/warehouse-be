<?php

namespace App\Contracts\Services;

interface DashboardServiceInterface
{
    public function getStats(): array;
}
