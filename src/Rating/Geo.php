<?php

namespace Vladimino\Discoverist\Rating;

class Geo
{
    const URL = 'https://rating.chgk.info/geo.php';

    const PARAM_COUNTRY = 'country';
    const PARAM_LAYOUT = 'layout';

    const LAYOUT_TOWNS = 'town_list';

    const COUNTRY_GERMANY = 'Германия';

    public function getTownsByCountryUrl(string $country): string
    {
        $country = mb_convert_encoding($country, 'WINDOWS-1251', 'UTF-8');
        $params = [
            self::PARAM_LAYOUT => self::LAYOUT_TOWNS,
            self::PARAM_COUNTRY => $country,
        ];

        return $this->buildUrl($params);
    }

    public function buildUrl(array $params): string
    {
        return self::URL.'?'.\http_build_query($params);
    }
}
