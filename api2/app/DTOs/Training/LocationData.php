<?php

namespace App\DTOs\Training;

readonly class LocationData
{
    public function __construct(
        public ?string $venueName = null,
        public ?string $governorate = null,
        public ?string $city = null,
        public ?string $district = null,
        public ?string $address = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $locationVisibility = null,
        public ?string $onlinePlatform = null,
        public ?string $onlineUrl = null,
    ) {}

    public static function extractFromArray(array $data): array
    {
        return array_filter([
            'venue_name' => $data['venue_name'] ?? null,
            'governorate' => $data['governorate'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            'location_visibility' => $data['location_visibility'] ?? null,
            'online_platform' => $data['online_platform'] ?? null,
            'online_url' => $data['online_url'] ?? null,
        ], fn ($v) => $v !== null);
    }

    public function toArray(): array
    {
        return array_filter([
            'venue_name' => $this->venueName,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_visibility' => $this->locationVisibility,
            'online_platform' => $this->onlinePlatform,
            'online_url' => $this->onlineUrl,
        ], fn ($v) => $v !== null);
    }
}
