<?php

namespace Vladimino\Discoverist\Model;

use Tightenco\Collect\Support\Collection;
use Vladimino\Discoverist\Rating\Connector;

class ResultsModel extends AbstractRatingAwareModel
{
    /**
     * @var Collection
     */
    private $tourResults;

    public function __construct(Connector $connector)
    {
        parent::__construct($connector);

        $this->tourResults = \collect([]);
    }

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

    public function getTourInfo(int $tournamentId): array
    {
        return $this->connector->getTourInfo($tournamentId);
    }

    public function getPlayedToursForTown(string $town, int $seasonId): array
    {
        $teams = $this->geAllTeamsForTowns([$town]);
        $tourIds = $this->getPlayedTournamentsIDsByTeams($teams, $seasonId);
        $tours = $this->getToursInfoByTourIds($tourIds);
        $orderedTours = $this->orderToursByDate($tours);

        return $this->filterToursByDateEnd($orderedTours, \time());
    }

    private function applyFilterToResults(array $teams): void
    {
        $teamIds = \array_column($teams, Connector::KEY_TEAM_ID);
        $this->tourResults = $this->tourResults->filter(
            function (array $result) use ($teamIds) {
                return isset($result['idteam']) && in_array($result['idteam'], $teamIds);
            }
        );
    }

    private function populatePlaceAndTown(array $teams): void
    {
        $highestPlaceInRange = 1;
        $lastGroupedPoints = 0;
        $displayPlace = '';
        $results = $this->tourResults->toArray();
        $points2ValuesMap = $this->buildPoints2ValuesMap();
        $teams2TownsMap = $this->buildTeams2TownsMap($teams);

        foreach ($results as $key => $result) {
            $currentPoints = $result[Connector::KEY_POINTS];
            $uniquePointsCount = $points2ValuesMap[$currentPoints];

            if ($uniquePointsCount > 1                    // non-unique
                && (
                    $lastGroupedPoints !== $currentPoints // new points value
                    || !strpos($displayPlace, '-') // prev was not grouped
                )
            ) {
                // calculate grouped display place value
                $lastGroupedPoints = $currentPoints;
                $lowestPlaceInRange = $highestPlaceInRange + $uniquePointsCount - 1;
                $displayPlace = $highestPlaceInRange.'-'.$lowestPlaceInRange;
                $highestPlaceInRange += $uniquePointsCount;
            } elseif ($uniquePointsCount === 1) {
                // no specific logic here
                $displayPlace = (string)$highestPlaceInRange;
            }

            $results[$key][Connector::KEY_PLACE] = $displayPlace;
            $results[$key][Connector::KEY_TOWN] = $teams2TownsMap[$result[Connector::KEY_TEAM_ID]];

            if ($uniquePointsCount === 1) {
                $highestPlaceInRange++;
            }
        }

        $this->tourResults = \collect($results);
    }

    private function buildPoints2ValuesMap(): array
    {
        $points = \array_column($this->tourResults->toArray(), Connector::KEY_POINTS);

        return \array_count_values($points);
    }

    private function orderResultsByPoints(): void
    {
        $this->tourResults = $this->tourResults
            ->sortByDesc(Connector::KEY_POINTS);
    }

    private function buildTeams2TownsMap(array $teams): array
    {
        $townsMap = [];
        foreach ($teams as $team) {
            $townsMap[$team[Connector::KEY_TEAM_ID]] = $team[Connector::KEY_TOWN];
        }

        return $townsMap;
    }

    private function populateTournamentResults(int $tournamentID): void
    {
        $this->tourResults = \collect($this->connector->getTourResults($tournamentID));
    }

    private function filterToursByDateEnd(array $tours, int $time): array
    {
        return \collect($tours)
            ->filter(
                function (array $tour) use ($time) {
                    return \strtotime($tour[Connector::KEY_DATE_END]) < $time;
                }
            )->toArray();
    }
}
