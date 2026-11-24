<?php

namespace App\Support;

/**
 * Simplified outline of the Netherlands and the main stops, drawn as one inline SVG by sections/route_map.blade.php.
 * Coordinates are real longitude/latitude (rounded), projected with a simple equirectangular projection (longitude scaled by cos 52 degrees).
 * The outline is a deliberately simplified silhouette, not a survey-accurate border.
 */
class NetherlandsMap
{
    public const WIDTH = 520;
    public const HEIGHT = 600;

    /** Mainland outline, clockwise from Den Helder: [longitude, latitude]. */
    private const OUTLINE = [
        [4.75, 52.96], [5.05, 53.07], [5.42, 53.18], [5.90, 53.32], [6.20, 53.40], [6.60, 53.36], [6.95, 53.33], [7.20, 53.24],
        [7.05, 52.95], [7.07, 52.64], [6.95, 52.45], [6.75, 52.20], [6.85, 52.05], [6.40, 51.85], [6.05, 51.80], [6.20, 51.37],
        [6.05, 51.15], [5.98, 50.98], [6.02, 50.75], [5.70, 50.76], [5.70, 50.88], [5.85, 51.05], [5.55, 51.05], [5.50, 51.30],
        [5.10, 51.40], [4.80, 51.45], [4.50, 51.40], [4.25, 51.38], [3.80, 51.28], [3.55, 51.40], [3.50, 51.55], [3.70, 51.72],
        [3.85, 51.82], [4.12, 51.98], [4.25, 52.10], [4.53, 52.37], [4.55, 52.46], [4.60, 52.62],
    ];

    /** IJsselmeer / Markermeer, drawn as a hole in the land. */
    private const LAKE = [[5.12, 52.90], [5.45, 52.82], [5.60, 52.62], [5.30, 52.52], [5.05, 52.55], [5.02, 52.72]];

    /** Stops: key => [label, lon, lat, destination slug, label side]. */
    public const STOPS = [
        'amsterdam' => ['Amsterdam', 4.90, 52.37, 'amsterdam-and-the-cities', 'r'],
        'haarlem' => ['Haarlem', 4.65, 52.38, 'amsterdam-and-the-cities', 'l'],
        'utrecht' => ['Utrecht', 5.12, 52.09, 'amsterdam-and-the-cities', 'r'],
        'delft' => ['Delft', 4.36, 52.01, 'amsterdam-and-the-cities', 'l'],
        'lisse' => ['Keukenhof area', 4.56, 52.26, 'tulips-and-countryside', 'l'],
        'zaanse' => ['Zaanse Schans', 4.82, 52.47, 'tulips-and-countryside', 'r'],
        'kinderdijk' => ['Kinderdijk', 4.64, 51.88, 'tulips-and-countryside', 'r'],
        'giethoorn' => ['Giethoorn', 6.08, 52.74, 'tulips-and-countryside', 'r'],
        'middelburg' => ['Middelburg', 3.61, 51.50, 'zeeland-and-the-delta', 'r'],
    ];

    /** One dashed route through the main stops (order matters). */
    public const ROUTE = ['zaanse', 'amsterdam', 'haarlem', 'lisse', 'delft', 'kinderdijk', 'middelburg'];

    public static function xy(float $lon, float $lat): array
    {
        [$minLon, $maxLon, $minLat, $maxLat] = [3.2, 7.3, 50.7, 53.5];
        $k = cos(deg2rad(52));
        $w = ($maxLon - $minLon) * $k;
        $h = $maxLat - $minLat;
        $scale = min((self::WIDTH - 60) / $w, (self::HEIGHT - 60) / $h);
        $ox = (self::WIDTH - $w * $scale) / 2;
        $oy = (self::HEIGHT - $h * $scale) / 2;

        return [round($ox + ($lon - $minLon) * $k * $scale, 1), round($oy + ($maxLat - $lat) * $scale, 1)];
    }

    private static function path(array $pts, bool $close = true): string
    {
        $d = '';
        foreach ($pts as $i => [$lon, $lat]) {
            [$x, $y] = self::xy($lon, $lat);
            $d .= ($i === 0 ? 'M' : 'L').$x.' '.$y;
        }

        return $d.($close ? 'Z' : '');
    }

    public static function landPath(): string
    {
        return self::path(self::OUTLINE).self::path(self::LAKE);
    }

    public static function routePath(): string
    {
        return self::path(array_map(fn ($k) => [self::STOPS[$k][1], self::STOPS[$k][2]], self::ROUTE), false);
    }
}
