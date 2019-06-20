<?php

namespace Vladimino\Discoverist\Domain;

class Team
{
    private $teamName;
    private $teamTown;

    public function __construct(string $teamName, string $teamTown)
    {
        $this->teamName = $teamName;
        $this->teamTown = $teamTown;
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): void
    {
        $this->teamName = $teamName;
    }

    public function getTeamTown(): string
    {
        return $this->teamTown;
    }

    public function setTeamTown(string $teamTown): void
    {
        $this->teamTown = $teamTown;
    }
}
