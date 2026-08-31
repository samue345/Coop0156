<?php

namespace App\Models\Concerns;

use App\Support\HashidsCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasHashidsCode
{
    public function initializeHasHashidsCode(): void
    {
        $this->append('hashids_code');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null || ctype_digit((string) $value)) {
            return parent::resolveRouteBinding($value, $field);
        }

        return static::findByHashidsCode((string) $value);
    }

    public function scopeWhereHashidsCode(Builder $query, string $code): Builder
    {
        $id = HashidsCode::decode($code, static::class);

        return $query->whereKey($id ?? 0);
    }

    public static function findByHashidsCode(string $code): ?static
    {
        $id = HashidsCode::decode($code, static::class);

        return $id === null ? null : static::query()->find($id);
    }

    protected function hashidsCode(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->getKey() === null
                ? null
                : HashidsCode::encode((int) $this->getKey(), static::class)
        );
    }
}
