<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Core\Config;
use Vladimino\Discoverist\Error\TeamNotFoundException;
use Vladimino\Discoverist\Rating\Connector;

class AbstractRatingAwareModel
{
    public const COUNTRY_GERMANY = 'Германия';
    public const TOWN_BERLIN     = 'Берлин';
    public const CURRENT_SEASON  = 52;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var array
     */
    protected $allSeasons;

    public function __construct(Connector $connector)
    {
        $this->connector  = $connector;
        $this->allSeasons = Config::get('seasons');
    }

    public function getFilteredTeams(string $countryFilter, string $townFilter = ''): array
    {
        $filteredTowns = !empty($townFilter) ? [$townFilter] : $this->getTownsByCountry($countryFilter);

        return $this->geAllTeamsForTowns($filteredTowns);
    }

    public function getTeamById(int $teamId): array
    {
        $team = $this->connector->getTeamInfo($teamId);
        if (empty($team)) {
            throw new TeamNotFoundException($teamId);
        }

        return (array)$team;
    }

    public function getAllSeasons(): array
    {
        return $this->allSeasons;
    }

    protected function geAllTeamsForTowns(array $towns): array
    {
        $teams = [];
        foreach ($towns as $town) {
            $teams = \array_merge($teams, $this->getTeamsForTown($town));
        }

        return $teams;
    }

    protected function getTownsByCountry(string $country): array
    {
        return $this->connector->getTownsByCountry($country);
    }

    protected function getPlayedTournamentsIDsByTeams(array $teams, int $seasonId): array
    {
        $tourIds = [];
        foreach ($teams as $team) {
            $teamWithTours = $this->connector->getToursByTeam($team[Connector::KEY_TEAM_ID], $seasonId);

            if (isset($teamWithTours[Connector::KEY_TOURS])) {
                $tourIds = \array_merge($tourIds, $teamWithTours[Connector::KEY_TOURS]);
            }
        }

        return \array_unique($tourIds);
    }

    protected function orderToursByDate(array $tours): array
    {
        $toursCollection = \collect($tours);

        return $toursCollection
            ->sortByDesc(Connector::KEY_DATE_START)
            ->toArray();
    }

    protected function getToursInfoByTourIds(array $tourIds): array
    {
        $tours = [];
        foreach ($tourIds as $tourId) {
            $tours[] = $this->connector->getTourInfo($tourId);
        }

        return array_filter($tours);
    }

    protected function getTeamsForTown(string $town): array
    {
        $teams = \collect($this->connector->searchTeamsByTown($town));

        return $teams
            ->sortBy(Connector::KEY_NAME)
            ->toArray();
    }
}
