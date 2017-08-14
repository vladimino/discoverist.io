<?php

namespace Vladimino\Discoverist\Rating;

use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class Connector
 *
 * @package Vladimino\Discoverist\Rating
 */
class Connector
{
    const API_ENDPOINT_TOURNAMENTS = 'http://rating.chgk.info/api/tournaments/';
    const API_ENDPOINT_TEAMS       = 'http://rating.chgk.info/api/teams/';

    const API_SUFFIX_LIST   = '/list';
    const API_SUFFIX_SEARCH = 'search';
    const API_SUFFIX_TOURS  = '/tournaments';

    const API_PARAM_TOWN = 'town';
    const API_FORMAT     = '.json';

    const KEY_TEAM_ID    = 'idteam';
    const KEY_TOUR_ID    = 'idtournament';
    const KEY_TOWN       = 'town';
    const KEY_PLACE      = 'place';
    const KEY_POINTS     = 'questions_total';
    const KEY_TOURS      = 'tournaments';
    const KEY_DATE_START = 'date_start';
    const KEY_NAME       = 'name';
    const KEY_DATE_END   = 'date_end';

    private const COLUMN_INDEX_TOWN = 1;

    private const AGENT         = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
    private const COOKIE        = 'chgk_last_seen_news=2016-08-21+19%3A16%3A13';

    private const CACHE_EXPIRATION = 86400; // 60 sec * 60 min * 24 h = 24 hours

    /**
     * @var \Vladimino\Discoverist\Rating\Geo
     */
    private $geoClient;

    /**
     * @var \Memcached
     */
    private $cache;

    /**
     * Connector constructor.
     *
     * @param \Vladimino\Discoverist\Rating\Geo $geoClient
     * @param \Memcached $cache
     */
    public function __construct(Geo $geoClient, \Memcached $cache)
    {
        $this->geoClient = $geoClient;
        $this->cache = $cache;
    }

    /**
     * @param int $tourId
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getTourInfo($tourId): array
    {
        $url            = self::API_ENDPOINT_TOURNAMENTS . $tourId .  self::API_FORMAT;
        $tournamentInfo = $this->makeRequest($url);

        if (!isset($tournamentInfo[0])) {
            throw new \RuntimeException('Ошибка получения инфрормации по турниру с ID ' . $tourId);
        }

        return $tournamentInfo[0];
    }

    /**
     * @param int $tourId
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getTourResults($tourId): array
    {
        $url               = self::API_ENDPOINT_TOURNAMENTS . $tourId . self::API_SUFFIX_LIST . self::API_FORMAT;
        $tournamentResults = $this->makeRequest($url);

        if (empty($tournamentResults)) {
            throw new \InvalidArgumentException('Ошибка получения результатов турнира с ID ' . $tourId);
        }

        return $tournamentResults;
    }

    /**
     * @param int $teamId
     *
     * @return array
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getTeamInfo($teamId): array
    {
        $url      = self::API_ENDPOINT_TEAMS . $teamId . self::API_FORMAT;
        $teamInfo = $this->makeRequest($url);

        if (!isset($teamInfo[0]['name'], $teamInfo[0]['town'])) {
            return [];
        }

        return [
            'name' => $teamInfo[0]['name'],
            'city' => $teamInfo[0]['town']
        ];
    }

    /**
     * @param int $teamId
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getToursByTeam(int $teamId): array
    {
        $seasonId = '/last';
        $url = self::API_ENDPOINT_TEAMS . $teamId . self::API_SUFFIX_TOURS . $seasonId . self::API_FORMAT;

        return $this->makeRequest($url);
    }

    /**
     * @param string $town
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function searchTeamsByTown($town): array
    {
        $url      = self::API_ENDPOINT_TEAMS . self::API_SUFFIX_SEARCH . self::API_FORMAT
            . '?' . self::API_PARAM_TOWN . '=' . $town;
        $teams    = $this->makeRequest($url);

        return $teams['items'] ?? [];
    }

    /**
     * @param string $country
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getTownsByCountry(string $country): array
    {
        $url   = $this->geoClient->getTownsByCountryUrl($country);
        $html  = $this->makeRequest($url, false);
        $dom   = HtmlDomParser::str_get_html($html);
        $towns = [];

        foreach ($dom->find('.colored_table tr') as $row) {
            $index = 0;
            foreach ($row->find('td') as $column) {
                if ($index !== self::COLUMN_INDEX_TOWN) {
                    $index++;
                    continue;
                }

                $towns[] = \trim($column->plaintext);
                break;
            }
        }

        return $towns;
    }

    /**
     * @param string $url
     * @param bool $convertJSON
     *
     * @return array|string
     * @throws \RuntimeException
     */
    public function makeRequest(string $url, bool $convertJSON = true)
    {
        // Try to get results from cache first
        $cachedResult = $this->cache->get($url);
        if ($cachedResult) {
            return $cachedResult;
        }

        $resource = \curl_init($url);
        \curl_setopt_array(
            $resource,
            [
                \CURLOPT_RETURNTRANSFER => true,
                \CURLOPT_HEADER => 0,
                \CURLOPT_RETURNTRANSFER => 1,
                \CURLOPT_USERAGENT => self::AGENT,
                \CURLOPT_COOKIE => self::COOKIE,
            ]
        );

        $output = \curl_exec($resource);
        \curl_close($resource);

        if (empty($output)) {
            throw new \RuntimeException('Ошибка получения результата от API рейтинга. URL: ' . $url);
        }

        if ($convertJSON === false) {
            return $output;
        }

        $results = \json_decode($output, true);

        if (null === $results) {
            throw new \RuntimeException('Ошибка конвертации результата от API рейтинга. Url: ' . $url);
        }

        // Cache result
        $this->cache->set($url, $results, self::CACHE_EXPIRATION);

        return $results;
    }
}
