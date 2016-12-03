<?php

namespace Vladimino\Discoverist\Rating;

/**
 * Class Connector
 * @package Vladimino\Discoverist\Rating
 */
class Connector
{
    const API_TOURNAMENT_URL = 'http://rating.chgk.info/api/tournaments/';
    const API_LIST_SUFFIX = '/list';
    const API_FORMAT = '.json';

    /**
     * @param int $tourId
     *
     * @return array
     * @throws \Exception
     */
    public function getTourInfo($tourId)
    {
        $url            = self::API_TOURNAMENT_URL.$tourId.self::API_FORMAT;
        $tournamentInfo = $this->makeRequest($url);

        if (!isset($tournamentInfo[0])) {
            throw new \Exception('Ошибка получения инфрормации по турниру с ID '.$tourId);
        }

        return $tournamentInfo[0];
    }

    /**
     * @param int $tourId
     *
     * @return array
     * @throws \Exception
     */
    public function getTourResults($tourId)
    {
        $url               = self::API_TOURNAMENT_URL.$tourId.self::API_LIST_SUFFIX.self::API_FORMAT;
        $tournamentResults = $this->makeRequest($url);

        if (empty($tournamentResults)) {
            throw new \Exception('Ошибка получения результатов турнира с ID '.$tourId);
        }

        return $tournamentResults;
    }

    /**
     * @param string $url
     *
     * @return array
     * @throws \Exception
     */
    public function makeRequest($url)
    {
        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
                CURLOPT_COOKIE         => 'chgk_last_seen_news=2016-08-21+19%3A16%3A13',
            ]
        );

        $output = curl_exec($ch);
        curl_close($ch);

        if (empty($output)) {
            throw new \Exception('Ошибка получения результата от API рейтинга');
        }

        $results = json_decode($output, true);

        if (empty($results)) {
            throw new \Exception('Ошибка конвертации результата от API рейтинга');
        }

        return $results;
    }
}
