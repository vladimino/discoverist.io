<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Rating\Connector;

/**
 * Class ResultsModel
 *
 * @package Model
 */
class ResultsModel extends AbstractRatingAwareModel
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $tourResults;

    /**
     * ResultsModel constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        parent::__construct($connector);

        $this->tourResults = \collect([]);
    }

    /**
     * @param int $tournamentID
     * @param string $countryFilter
     * @param string $townFilter
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getFilteredResultsFromTournament(
        int $tournamentID,
        string $countryFilter,
        string $townFilter
    ): array {
        $filteredTowns = !empty($townFilter) ? [$townFilter] : $this->getTownsByCountry($countryFilter);
        $filteredTeams = $this->geAllTeamsForTowns($filteredTowns);

        $this->populateTournamentResults($tournamentID);
        $this->applyFilterToResults($filteredTeams);
        $this->orderResultsByPoints();
        $this->populatePlaceAndTown($filteredTeams);

        return $this->tourResults->toArray();
    }

    /**
     * @param int $tournamentId
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getTourInfo(int $tournamentId): array
    {
        return $this->connector->getTourInfo($tournamentId);
    }

    /**
     * @param string $town
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getPlayedToursForTown(string $town): array
    {
        $teams        = $this->geAllTeamsForTowns([$town]);
        $tourIds      = $this->getPlayedTournamentsIDsByTeams($teams);
        $tours        = $this->getToursInfoByTourIds($tourIds);
        $orderedTours = $this->orderToursByDate($tours);

        while (\strtotime($orderedTours[0]['date_end']) > \time()) {
            \array_shift($orderedTours);
        }

        return $orderedTours;
    }

    /**
     * @param array $teams
     *
     * @return void
     */
    private function applyFilterToResults(array $teams) : void
    {
        $teamIds           = \array_column($teams, Connector::KEY_TEAM_ID);
        $this->tourResults = $this->tourResults->filter(
            function ($result) use ($teamIds) {
                return \in_array($result['idteam'], $teamIds);
            }
        );
    }

    /**
     * @param array $teams
     *
     * @return void
     */
    private function populatePlaceAndTown(array $teams): void
    {
        $highestPlaceInRange = 1;
        $lastGroupedPoints   = 0;
        $displayPlace        = '';
        $results             = $this->tourResults->toArray();
        $points2ValuesMap    = $this->buildPoints2ValuesMap();
        $teams2TownsMap      = $this->buildTeams2TownsMap($teams);

        foreach ($results as $key => $result) {
            $currentPoints     = $result[Connector::KEY_POINTS];
            $uniquePointsCount = $points2ValuesMap[$currentPoints];

            if ($uniquePointsCount > 1                    // non-unique
                && ($lastGroupedPoints !== $currentPoints // new points value
                || !\strpos($displayPlace, '-'))   // prev was not grouped
            ) {
                // calculate grouped display place value
                $lastGroupedPoints   = $currentPoints;
                $lowestPlaceInRange  = $highestPlaceInRange + $uniquePointsCount - 1;
                $displayPlace        = $highestPlaceInRange . '-' . $lowestPlaceInRange;
                $highestPlaceInRange += $uniquePointsCount;
            } elseif ($uniquePointsCount === 1) {
                // no specific logic here
                $displayPlace = $highestPlaceInRange;
            }

            $results[$key][Connector::KEY_PLACE] = $displayPlace;
            $results[$key][Connector::KEY_TOWN]  = $teams2TownsMap[$result[Connector::KEY_TEAM_ID]];

            if ($uniquePointsCount === 1) {
                $highestPlaceInRange++;
            }
        }

        $this->tourResults = \collect($results);
    }

    /**
     * @return array
     */
    private function buildPoints2ValuesMap(): array
    {
        $points = \array_column($this->tourResults->toArray(), Connector::KEY_POINTS);

        return \array_count_values($points);
    }

    /**
     * @return void
     */
    private function orderResultsByPoints(): void
    {
        $this->tourResults = $this->tourResults
            ->sortByDesc(Connector::KEY_POINTS);
    }

    /**
     * @param array $teams
     *
     * @return array
     */
    private function buildTeams2TownsMap(array $teams): array
    {
        $townsMap = [];
        foreach ($teams as $team) {
            $townsMap[$team[Connector::KEY_TEAM_ID]] = $team[Connector::KEY_TOWN];
        }

        return $townsMap;
    }

    /**
     * @param int $tournamentID
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function populateTournamentResults(int $tournamentID): void
    {
        $this->tourResults = \collect($this->connector->getTourResults($tournamentID));
    }
}
