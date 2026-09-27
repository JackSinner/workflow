<?php

declare(strict_types=1);

namespace Workflow\V2\Support;

use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Keep the legacy local wall time readable by older workers while recording an
 * unambiguous UTC instant for new workers. Existing history is never inferred.
 */
final class HistoryRecordedAt implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        $utc = $attributes['recorded_at_utc'] ?? null;

        if ($utc !== null) {
            return Carbon::parse((string) $utc, 'UTC');
        }

        return $value === null ? null : Carbon::parse((string) $value, date_default_timezone_get());
    }

    /**
     * @return array{recorded_at: string|null, recorded_at_utc: string|null}
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return ['recorded_at' => null, 'recorded_at_utc' => null];
        }

        if (is_string($value)) {
            $value = Carbon::parse($value, date_default_timezone_get());
        }

        if (! $value instanceof DateTimeInterface) {
            throw new InvalidArgumentException("{$key} must be a date-time value.");
        }

        $instant = Carbon::instance($value);

        return [
            'recorded_at' => $instant->copy()->setTimezone(date_default_timezone_get())->format('Y-m-d H:i:s.u'),
            'recorded_at_utc' => $instant->utc()->format('Y-m-d H:i:s.u'),
        ];
    }
}
