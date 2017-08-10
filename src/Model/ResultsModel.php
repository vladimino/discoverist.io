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
    const COUNTRY_GERMANY = 'Германия';

    const TOWN_BERLIN = 'Берлин';

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
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getRealTimeTours(): array
    {
        $towns   = [self::TOWN_BERLIN];
        $teams   = $this->geAllTeamsForTowns($towns);
        $tourIds = $this->getPlayedTournamentsByTeams($teams);

        $tours = [];
        foreach ($tourIds as $tourId) {
            $tours[] = $this->connector->getTourInfo($tourId);
        }

        return $this->orderToursByDate($tours);
    }

    /**
     * @param array $teams
     *
     * @return void
     */
    private function applyFilterToResults(array $teams) : void
    {
        $teamIds           = \array_column($teams, self::KEY_TEAM_ID);
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
            $currentPoints     = $result[self::KEY_POINTS];
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

            $results[$key][self::KEY_PLACE] = $displayPlace;
            $results[$key][self::KEY_TOWN]  = $teams2TownsMap[$result[self::KEY_TEAM_ID]];

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
        $points = \array_column($this->tourResults->toArray(), self::KEY_POINTS);

        return \array_count_values($points);
    }

    /**
     * @return void
     */
    private function orderResultsByPoints(): void
    {
        $this->tourResults = $this->tourResults
            ->sortByDesc(self::KEY_POINTS);
    }

    /**
     * @param array $tours
     *
     * @return array
     */
    private function orderToursByDate(array $tours): array
    {
        $toursCollection = \collect($tours);

        return $toursCollection
            ->sortByDesc('date_start')
            ->toArray();
    }

    /**
     * @param string $country
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getTownsByCountry(string $country): array
    {
        return $this->connector->getTownsByCountry($country);
    }

    /**
     * @param array $towns
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function geAllTeamsForTowns(array $towns): array
    {
        $teams = [];
        foreach ($towns as $town) {
            $teams = \array_merge($teams, $this->connector->searchTeamsByTown($town));
        }

        return $teams;
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
            $townsMap[$team[self::KEY_TEAM_ID]] = $team[self::KEY_TOWN];
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

    /**
     * @param array $teams
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getPlayedTournamentsByTeams(array $teams): array
    {
        $tourIds = [];
        foreach ($teams as $team) {
            $teamWithTours = $this->connector->getToursByTeam($team[self::KEY_TEAM_ID]);
            if ($teamWithTours[self::KEY_TOURS]) {
                $tourIds = \array_merge($tourIds, $teamWithTours[self::KEY_TOURS]);
            }
        }


        return \array_unique($tourIds);
    }
}
