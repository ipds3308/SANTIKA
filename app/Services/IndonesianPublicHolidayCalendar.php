<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndonesianPublicHolidayCalendar
{
    private const CALENDAR_URL = 'https://calendar.google.com/calendar/ical/id.indonesian%23holiday%40group.v.calendar.google.com/public/basic.ics';

    private const CACHE_KEY = 'indonesian-public-holidays';

    /**
     * @return array{available: bool, holidays: array<string, string>}
     */
    public function calendar(): array
    {
        $cachedCalendar = Cache::get(self::CACHE_KEY);

        if (is_array($cachedCalendar)) {
            return $cachedCalendar;
        }

        try {
            $response = Http::retry([100, 300])
                ->timeout(5)
                ->connectTimeout(2)
                ->withHeaders(['Accept' => 'text/calendar'])
                ->get(self::CALENDAR_URL)
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Unable to retrieve the Indonesian public holiday calendar.', [
                'exception' => $exception->getMessage(),
            ]);

            $calendar = ['available' => false, 'holidays' => []];
            Cache::put(self::CACHE_KEY, $calendar, now()->addMinutes(15));

            return $calendar;
        }

        $body = $response->body();

        if (! str_contains($body, 'BEGIN:VCALENDAR')) {
            Log::warning('The Indonesian public holiday calendar returned an invalid feed.');

            $calendar = ['available' => false, 'holidays' => []];
            Cache::put(self::CACHE_KEY, $calendar, now()->addMinutes(15));

            return $calendar;
        }

        $calendar = [
            'available' => true,
            'holidays' => $this->parseCalendar($body),
        ];

        Cache::put(self::CACHE_KEY, $calendar, now()->addHours(12));

        return $calendar;
    }

    public function holidayName(string $date): ?string
    {
        return $this->calendar()['holidays'][$date] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private function parseCalendar(string $calendar): array
    {
        $calendar = str_replace(["\r\n ", "\r\n\t"], '', $calendar);
        $holidays = [];
        $event = null;

        foreach (preg_split('/\r\n|\n|\r/', $calendar) as $line) {
            if ($line === 'BEGIN:VEVENT') {
                $event = [];

                continue;
            }

            if ($line === 'END:VEVENT') {
                if ($event !== null) {
                    $this->addEventDates($holidays, $event);
                }

                $event = null;

                continue;
            }

            if ($event === null || ! str_contains($line, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $line, 2);
            $property = strtoupper(explode(';', $property, 2)[0]);

            if (in_array($property, ['DTSTART', 'DTEND', 'SUMMARY', 'STATUS'], true)) {
                $event[$property] = $value;
            }
        }

        return $holidays;
    }

    /**
     * @param  array<string, string>  $holidays
     * @param  array<string, string>  $event
     */
    private function addEventDates(array &$holidays, array $event): void
    {
        if (! isset($event['DTSTART']) || ($event['STATUS'] ?? '') === 'CANCELLED') {
            return;
        }

        $start = DateTimeImmutable::createFromFormat('!Ymd', substr($event['DTSTART'], 0, 8));

        if ($start === false) {
            return;
        }

        $end = isset($event['DTEND'])
            ? DateTimeImmutable::createFromFormat('!Ymd', substr($event['DTEND'], 0, 8))
            : $start->modify('+1 day');

        if ($end === false || $end <= $start) {
            $end = $start->modify('+1 day');
        }

        $summary = $this->decodeText($event['SUMMARY'] ?? 'Hari libur nasional');

        for ($date = $start; $date < $end; $date = $date->modify('+1 day')) {
            $holidays[$date->format('Y-m-d')] = $summary;
        }
    }

    private function decodeText(string $value): string
    {
        return preg_replace_callback(
            '/\\\\([nN,;\\\\])/',
            static fn (array $matches): string => match ($matches[1]) {
                'n', 'N' => ' ',
                default => $matches[1],
            },
            $value,
        ) ?: 'Hari libur nasional';
    }
}
