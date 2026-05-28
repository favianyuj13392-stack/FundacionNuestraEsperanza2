<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait AdjustsOrder
{
    protected static function getOrderColumn(): string
    {
        return 'order';
    }

    protected static function bootAdjustsOrder(): void
    {
        static::saving(function (Model $model) {
            $orderColumn = static::getOrderColumn();

            if (! $model->isDirty($orderColumn)) {
                return;
            }

            $requestedOrder = $model->{$orderColumn};

            if ($requestedOrder === null) {
                return;
            }

            $query = $model->newModelQuery()->where($orderColumn, '>=', $requestedOrder);

            if ($model->exists) {
                $query->where($model->getKeyName(), '!=', $model->getKey());
            }

            $query->increment($orderColumn);
        });
    }
}
