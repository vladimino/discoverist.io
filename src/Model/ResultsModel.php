<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Core\Config;
use Vladimino\Discoverist\Core\Utils;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class ResultsModel
 * @package Model
 */
class ResultsModel
{
    /**
     * @var Connector
     */
    protected $connector;
    /**
     * @var array
     */
    protected $tours;
    /**
     * @var array
     */
    protected $teams;

    /**
     * ResultsModel constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
        $this->tours     = array_reverse(Config::get('tours'));
        $this->teams     = Config::get('teams');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTours()
    {
        if (empty($this->tours)) {
            throw new \Exception('Ошибка загрузки конфигурации, список турниров пуст');
        }

        return $this->tours;
    }

    /**
     * @param int    $tournamentID
     * @param string $searchFilter
     *
     * @return array
     */
    public function getResultsFromTournament($tournamentID, $searchFilter)
    {
        $results         = $this->connector->getTourResults($tournamentID);
        $filteredResults = $this->filterResults($results, $searchFilter);
        Utils::subKeySort($filteredResults, 'questions_total');

        return $this->addPlaceAndCityToResults($filteredResults);
    }

    /**
     * @param int $tournamentId
     *
     * @return array
     */
    public function getTourInfo($tournamentId)
    {
        return $this->connector->getTourInfo($tournamentId);
    }

    /**
     * @param array  $results
     * @param string $searchFilter
     *
     * @return array
     */
    private function filterResults($results, $searchFilter)
    {
        $filteredResults = array_filter(
            $results,
            function ($result) use ($searchFilter) {
                $isKnown       = array_key_exists($result['idteam'], $this->teams);
                $satisfyFilter = ($isKnown
                                  && ($searchFilter == 'Германия'
                                      || $this->teams[$result['idteam']]['city'] == $searchFilter)
                );

                return $satisfyFilter;
            }
        );

        return $filteredResults;
    }

    private function addPlaceAndCityToResults($results)
    {
        $i          = 1;
        $lastPoints = 0;
        $place      = '';

        $samePointsCount = $this->countSamePointsForResults($results);

        foreach ($results as $key => $result) {
            $repeat = $samePointsCount[$result['questions_total']];
            if ($repeat > 1 && ($lastPoints != $result['questions_total'] || !strpos($place, '-'))) {
                $lastPoints = $result['questions_total'];
                $lastPlace  = $i + $repeat - 1;
                $place      = $i.'-'.$lastPlace;
                $i          = $i + $repeat;
            } elseif ($repeat == 1) {
                $place = $i;
            }
            $results[$key]['place'] = $place;
            $results[$key]['city']  = $this->teams[$result['idteam']]['city'];
            if ($repeat == 1) {
                $i++;
            }
        }

        return $results;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    private function countSamePointsForResults($results)
    {
        $samePointsCount = [];
        foreach ($results as $result) {
            $points = $result['questions_total'];
            if (!isset($samePointsCount[$points])) {
                $samePointsCount[$points] = 1;

            } else {
                $samePointsCount[$points]++;
            }
        }

        return $samePointsCount;
    }
}
