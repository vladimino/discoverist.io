<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Core\Config;
use Vladimino\Discoverist\Error\TeamNotFoundException;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class AbstractRatingAwareModel
 * @package Vladimino\Discoverist\Model
 */
class AbstractRatingAwareModel
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
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @param int $teamId
     *
     * @return array
     * @throws TeamNotFoundException
     */
    public function getTeamById($teamId)
    {
        if (!isset($this->teams[$teamId])) {
            throw new TeamNotFoundException();
        }

        return $this->teams[$teamId];
    }
}
