<?php

namespace App\Models\Concerns;

use App\Support\HashidsCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasHashidsCode
{
    public function initializeHasHashidsCode(): void
    {
        $this->append('hashids_code');
    }

    /**
     * @param int|string $value
     * @param string|null $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $routeKey = (string) $value;

        if ($field !== null || ctype_digit($routeKey)) {
            return $this->resolveRouteBindingQuery($this, $value, $field)->first();
        }

        return static::findByHashidsCode($routeKey);
    }

    public function scopeWhereHashidsCode(Builder $query, string $code): Builder
    {
        $id = HashidsCode::decode($code, static::class);

        return $query->whereKey($id ?? 0);
    }

    /**
     * @return static|null
     */
    public static function findByHashidsCode(string $code): ?Model
    {
        $id = HashidsCode::decode($code, static::class);

        if (!$id) {
            return null;
        }

        return static::query()->find($id);
    }

    protected function hashidsCode(): Attribute
    {
        return Attribute::get(
            fn (): ?string => !$this->getKey()
                ? null
                : HashidsCode::encode($this->getKey(), static::class)
        );
    }
}
