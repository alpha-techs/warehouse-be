<?php

namespace App\Observers;

use App\Models\Container;

class ContainerObserver
{
    public function deleting(Container $container): void
    {
        $container->items()->delete();
    }

    public function restoring(Container $container): void
    {
        $container->items()->withTrashed()->restore();
    }

    public function forceDeleting(Container $container): void
    {
        $container->items()->withTrashed()->forceDelete();
    }
}
