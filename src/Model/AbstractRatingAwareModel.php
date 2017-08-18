<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Core\Config;
use Vladimino\Discoverist\Error\TeamNotFoundException;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class AbstractRatingAwareModel
 *
 * @package Vladimino\Discoverist\Model
 */
class AbstractRatingAwareModel
{
    const COUNTRY_GERMANY = 'Германия';
    const TOWN_BERLIN     = 'Берлин';
    const CURRENT_SEASON  = 50;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var array
     */
    protected $allSeasons;

    /**
     * @var array
     */
    protected $allTeams;

    /**
     * ResultsModel constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector  = $connector;
        $this->allSeasons = Config::get('seasons');
    }

    /**
     * @param string $countryFilter
     * @param string $townFilter
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getFilteredTeams(string $countryFilter, string $townFilter = ''): array
    {
        $filteredTowns = !empty($townFilter) ? [$townFilter] : $this->getTownsByCountry($countryFilter);

        return $this->geAllTeamsForTowns($filteredTowns);
    }

    /**
     * @param int $teamId
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws TeamNotFoundException
     */
    public function getTeamById(int $teamId): array
    {
        $team = $this->allTeams[$teamId] ?? $this->connector->getTeamInfo($teamId);
        if (empty($team)) {
            throw new TeamNotFoundException($teamId);
        }

        return $team;
    }

    /**
     * @return array
     */
    public function getAllSeasons(): array
    {
        return $this->allSeasons;
    }

    /**
     * @param array $towns
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function geAllTeamsForTowns(array $towns): array
    {
        $teams = [];
        foreach ($towns as $town) {
            $teams = \array_merge($teams, $this->getTeamsForTown($town));
        }

        return $teams;
    }

    /**
     * @param string $country
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getTownsByCountry(string $country): array
    {
        return $this->connector->getTownsByCountry($country);
    }

    /**
     * @param array $teams
     * @param int $seasonId
     *
     * @return array
     */
    protected function getPlayedTournamentsIDsByTeams(array $teams, int $seasonId): array
    {
        $tourIds = [];
        foreach ($teams as $team) {
            $teamWithTours = $this->connector->getToursByTeam($team[Connector::KEY_TEAM_ID], $seasonId);
            if ($teamWithTours[Connector::KEY_TOURS]) {
                $tourIds = \array_merge($tourIds, $teamWithTours[Connector::KEY_TOURS]);
            }
        }

        return \array_unique($tourIds);
    }

    /**
     * @param array $tours
     *
     * @return array
     */
    protected function orderToursByDate(array $tours): array
    {
        $toursCollection = \collect($tours);

        return $toursCollection
            ->sortByDesc(Connector::KEY_DATE_START)
            ->toArray();
    }

    /**
     * @param array $tourIds
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getToursInfoByTourIds(array $tourIds): array
    {
        $tours = [];
        foreach ($tourIds as $tourId) {
            $tours[] = $this->connector->getTourInfo($tourId);
        }

        return $tours;
    }

    /**
     * @param string $town
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function getTeamsForTown(string $town): array
    {
        $teams = \collect($this->connector->searchTeamsByTown($town));

        return $teams
            ->sortBy(Connector::KEY_NAME)
            ->toArray();
    }
}
