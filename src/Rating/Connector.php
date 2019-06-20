<?php

namespace Vladimino\Discoverist\Rating;

use Sunra\PhpSimple\HtmlDomParser;
use Vladimino\Discoverist\Cache\CacheInterface;
use Vladimino\Discoverist\Domain\Team;

class Connector
{
    public const KEY_TEAM_ID = 'idteam';
    public const KEY_TOUR_ID = 'idtournament';
    public const KEY_TOWN = 'town';
    public const KEY_PLACE = 'place';
    public const KEY_POINTS = 'questions_total';
    public const KEY_TOURS = 'tournaments';
    public const KEY_DATE_START = 'date_start';
    public const KEY_NAME = 'name';
    public const KEY_DATE_END = 'date_end';

    private const COLUMN_INDEX_TOWN = 1;

    private const API_ENDPOINT_TOURNAMENTS = 'https://rating.chgk.info/api/tournaments/';
    private const API_ENDPOINT_TEAMS = 'https://rating.chgk.info/api/teams/';

    private const API_SUFFIX_LIST = '/list';
    private const API_SUFFIX_SEARCH = 'search';
    private const API_SUFFIX_TOURS = '/tournaments';

    private const API_PARAM_TOWN = 'town';
    private const API_FORMAT = '.json';

    private const AGENT = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36'
    .' (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
    private const COOKIE = 'chgk_last_seen_news=2016-08-21+19%3A16%3A13';

    private const CACHE_EXPIRATION = 3600; // 60 sec * 60 min = 1 hour

    private $geoClient;
    private $cache;

    public function __construct(Geo $geoClient, CacheInterface $cache)
    {
        $this->geoClient = $geoClient;
        $this->cache = $cache;
    }

    public function getTourInfo(int $tourId): array
    {
        $url = self::API_ENDPOINT_TOURNAMENTS.$tourId.self::API_FORMAT;
        $tournamentInfo = $this->queryResults($url);

        if (!isset($tournamentInfo[0]) || !is_array($tournamentInfo[0])) {
            return [];
        }

        return $tournamentInfo[0];
    }

    public function getTourResults(int $tourId): array
    {
        $url = self::API_ENDPOINT_TOURNAMENTS.$tourId.self::API_SUFFIX_LIST.self::API_FORMAT;
        $tournamentResults = $this->queryResults($url);

        if (empty($tournamentResults)) {
            throw new \InvalidArgumentException('Ошибка получения результатов турнира с ID '.$tourId);
        }

        return $tournamentResults;
    }

    public function getTeamInfo(int $teamId): array
    {
        $url = self::API_ENDPOINT_TEAMS.$teamId.self::API_FORMAT;
        $teamInfo = $this->queryResults($url);

        $teamData = array_pop($teamInfo);

        if (!isset($teamData['name'], $teamData['town'])) {
            return [];
        }

        $team = new Team($teamData['name'], $teamData['town']);

        return [
            'name' => $team->getTeamName(),
            'city' => $team->getTeamTown(),
        ];
    }

    public function getToursByTeam(int $teamId, int $seasonId): array
    {
        $url = self::API_ENDPOINT_TEAMS.$teamId.self::API_SUFFIX_TOURS.'/'.$seasonId.self::API_FORMAT;

        return $this->queryResults($url);
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function searchTeamsByTown(string $town): array
    {
        $url = self::API_ENDPOINT_TEAMS.self::API_SUFFIX_SEARCH.self::API_FORMAT
            .'?'.self::API_PARAM_TOWN.'='.$town;
        $teams = $this->queryResults($url);

        return $teams['items'] ?? [];
    }

    /**
     * @psalm-suppress TooManyArguments
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress UndefinedDocblockClass
     */
    public function getTownsByCountry(string $country): array
    {
        $url = $this->geoClient->getTownsByCountryUrl($country);
        $html = $this->makeRequest($url);
        $dom = HtmlDomParser::str_get_html($html);
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

    public function queryResults(string $url): array
    {
        $response = $this->makeRequest($url);

        return (array)\json_decode($response, true);
    }

    public function makeRequest(string $url): string
    {
        // Try to get results from cache first
        $cachedResult = $this->cache->get($url);

        if ($cachedResult) {
            return $cachedResult;
        }

        $resource = \curl_init($url);

        if (!$resource) {
            throw new \RuntimeException('Ошибка запроса к API рейтинга. URL: '.$url);
        }

        \curl_setopt_array(
            $resource,
            [
                \CURLOPT_RETURNTRANSFER => true,
                \CURLOPT_HEADER => 0,
                \CURLOPT_USERAGENT => self::AGENT,
                \CURLOPT_COOKIE => self::COOKIE,
            ]
        );

        $output = (string)curl_exec($resource);
        \curl_close($resource);

        if (empty($output)) {
            throw new \RuntimeException('Ошибка получения результата от API рейтинга. URL: '.$url);
        }

        // Cache result
        $this->cache->set($url, $output, self::CACHE_EXPIRATION);

        return $output;
    }
}
