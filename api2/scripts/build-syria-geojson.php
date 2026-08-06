<?php

declare(strict_types=1);

/**
 * Builds enriched Syria ADM1/ADM2 GeoJSON with Arabic names for the needs map.
 * Run: php scripts/build-syria-geojson.php
 */

$root = dirname(__DIR__);

$adm1Map = [
    'SYR-DI' => ['name' => 'دمشق', 'name_en' => 'Damascus', 'center' => [33.513, 36.291]],
    'SYR-HL' => ['name' => 'حلب', 'name_en' => 'Aleppo', 'center' => [36.202, 37.161]],
    'SYR-RD' => ['name' => 'ريف دمشق', 'name_en' => 'Rural Damascus', 'center' => [33.583, 36.450]],
    'SYR-HI' => ['name' => 'حمص', 'name_en' => 'Homs', 'center' => [34.732, 36.713]],
    'SYR-HM' => ['name' => 'حماة', 'name_en' => 'Hama', 'center' => [35.132, 36.757]],
    'SYR-LA' => ['name' => 'اللاذقية', 'name_en' => 'Lattakia', 'center' => [35.532, 35.791]],
    'SYR-ID' => ['name' => 'إدلب', 'name_en' => 'Idleb', 'center' => [35.931, 36.634]],
    'SYR-HA' => ['name' => 'الحسكة', 'name_en' => 'Al-Hasakeh', 'center' => [36.512, 40.752]],
    'SYR-DY' => ['name' => 'دير الزور', 'name_en' => 'Deir-ez-Zor', 'center' => [35.336, 40.141]],
    'SYR-TA' => ['name' => 'طرطوس', 'name_en' => 'Tartous', 'center' => [34.893, 35.887]],
    'SYR-RA' => ['name' => 'الرقة', 'name_en' => 'Ar-Raqqa', 'center' => [35.953, 39.006]],
    'SYR-DR' => ['name' => 'درعا', 'name_en' => "Dar'a", 'center' => [32.625, 36.103]],
    'SYR-SU' => ['name' => 'السويداء', 'name_en' => 'As-Sweida', 'center' => [32.708, 36.566]],
    'SYR-QU' => ['name' => 'القنيطرة', 'name_en' => 'Quneitra', 'center' => [33.125, 35.825]],
];

function ringCentroid(array $ring): array
{
    $lng = 0.0;
    $lat = 0.0;
    $n = count($ring);

    if ($n === 0) {
        return [0.0, 0.0];
    }

    foreach ($ring as $point) {
        $lng += (float) $point[0];
        $lat += (float) $point[1];
    }

    return [$lng / $n, $lat / $n];
}

function geometryCentroid(array $geometry): array
{
    if (($geometry['type'] ?? '') === 'Polygon') {
        return ringCentroid($geometry['coordinates'][0] ?? []);
    }

    if (($geometry['type'] ?? '') === 'MultiPolygon') {
        $best = null;
        $bestArea = 0.0;

        foreach ($geometry['coordinates'] as $polygon) {
            $ring = $polygon[0] ?? [];
            $area = abs(polygonArea($ring));

            if ($area > $bestArea) {
                $bestArea = $area;
                $best = ringCentroid($ring);
            }
        }

        return $best ?? [0.0, 0.0];
    }

    return [0.0, 0.0];
}

function polygonArea(array $ring): float
{
    $area = 0.0;
    $n = count($ring);

    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $area += ((float) $ring[$j][0] + (float) $ring[$i][0]) * ((float) $ring[$j][1] - (float) $ring[$i][1]);
    }

    return $area / 2.0;
}

function pointInRing(float $lng, float $lat, array $ring): bool
{
    $inside = false;
    $n = count($ring);

    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = (float) $ring[$i][0];
        $yi = (float) $ring[$i][1];
        $xj = (float) $ring[$j][0];
        $yj = (float) $ring[$j][1];

        $intersect = (($yi > $lat) !== ($yj > $lat))
            && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

        if ($intersect) {
            $inside = ! $inside;
        }
    }

    return $inside;
}

function pointInGeometry(float $lng, float $lat, array $geometry): bool
{
    if (($geometry['type'] ?? '') === 'Polygon') {
        return pointInRing($lng, $lat, $geometry['coordinates'][0] ?? []);
    }

    if (($geometry['type'] ?? '') === 'MultiPolygon') {
        foreach ($geometry['coordinates'] as $polygon) {
            if (pointInRing($lng, $lat, $polygon[0] ?? [])) {
                return true;
            }
        }
    }

    return false;
}

$srcAdm1 = $root.'/front/assets/js/pages/syria_adm1_simplified.geojson';
$srcAdm2 = $root.'/front/assets/js/pages/syria_adm2_simplified.geojson';
$outDir = $root.'/front/assets/geo';

if (! is_file($srcAdm1) || ! is_file($srcAdm2)) {
    fwrite(STDERR, "Source GeoJSON files not found.\n");
    exit(1);
}

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$adm1 = json_decode(file_get_contents($srcAdm1), true, 512, JSON_THROW_ON_ERROR);
$adm2 = json_decode(file_get_contents($srcAdm2), true, 512, JSON_THROW_ON_ERROR);

$preparedAdm1 = [];

foreach ($adm1['features'] as &$feature) {
    $iso = (string) ($feature['properties']['shapeISO'] ?? '');
    $meta = $adm1Map[$iso] ?? null;

    if (! $meta) {
        continue;
    }

    $feature['properties'] = [
        'name' => $meta['name'],
        'name_en' => $meta['name_en'],
        'iso' => $iso,
        'center_lat' => $meta['center'][0],
        'center_lng' => $meta['center'][1],
    ];

    $preparedAdm1[] = [
        'name' => $meta['name'],
        'feature' => $feature,
    ];
}
unset($feature);

foreach ($adm2['features'] as &$feature) {
    [$lng, $lat] = geometryCentroid($feature['geometry']);
    $govName = null;
    $govEn = null;

    foreach ($preparedAdm1 as $gov) {
        if (pointInGeometry($lng, $lat, $gov['feature']['geometry'])) {
            $govName = $gov['name'];
            $govEn = $gov['feature']['properties']['name_en'];
            break;
        }
    }

    $shapeName = (string) ($feature['properties']['shapeName'] ?? '');

    $feature['properties'] = [
        'name' => $shapeName,
        'name_en' => $shapeName,
        'governorate' => $govName,
        'governorate_en' => $govEn,
    ];
}
unset($feature);

$adm1['features'] = array_column($preparedAdm1, 'feature');

file_put_contents(
    $outDir.'/syria-adm1.geojson',
    json_encode($adm1, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

file_put_contents(
    $outDir.'/syria-adm2.geojson',
    json_encode($adm2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

echo 'Wrote '.count($adm1['features'])." governorates and ".count($adm2['features'])." districts.\n";
