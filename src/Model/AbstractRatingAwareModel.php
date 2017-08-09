<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Core\Config;
use Vladimino\Discoverist\Error\LoadConfigException;
use Vladimino\Discoverist\Error\TeamNotFoundException;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class AbstractRatingAwareModel
 *
 * @package Vladimino\Discoverist\Model
 */
class AbstractRatingAwareModel
{
    const KEY_PLACE   = 'place';
    const KEY_POINTS  = 'questions_total';
    const KEY_TEAM_ID = 'idteam';
    const KEY_TOWN    = 'town';

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var array
     */
    protected $allTours;

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
        $this->connector = $connector;
        $this->allTours  = \array_reverse(Config::get('tours'));
        $this->allTeams  = Config::get('teams');
    }

    /**
     * @return array
     * @throws LoadConfigException
     */
    public function getTours(): array
    {
        if (empty($this->allTours)) {
            throw new LoadConfigException();
        }

        return $this->allTours;
    }

    /**
     * @return array
     * @throws \Vladimino\Discoverist\Error\LoadConfigException
     */
    public function getAllTeams(): array
    {
        if (empty($this->allTeams)) {
            throw new LoadConfigException();
        }

        return $this->allTeams;
    }

    /**
     * @param int $teamId
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
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
}
