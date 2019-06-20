<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Error\SameTeamException;
use Vladimino\Discoverist\Rating\Connector;

class Face2FaceModel extends AbstractRatingAwareModel
{
    public const DEFAULT_TEAM1_ID = 68786; // Сцилла
    public const DEFAULT_TEAM2_ID = 67678; // Котобусуер Тор

    private const RESULT_TEAM_1_WIN = 'team1win';
    private const RESULT_TEAM_2_WIN = 'team2win';
    private const RESULT_DRAW       = 'draw';

    /**
     * @var int
     */
    protected $team1Wins = 0;
    /**
     * @var int
     */
    protected $team2Wins = 0;
    /**
     * @var int
     */
    protected $draws     = 0;

    public function getResultsForTeams(int $team1ID, int $team2ID, int $currentSeasonId): array
    {
        $this->validateInput($team1ID, $team2ID);

        $teams          = $this->buildTeamArrayFromTeamIDs($team1ID, $team2ID);
        $playedToursIDs = $this->getPlayedTournamentsIDsByTeams($teams, $currentSeasonId);
        $results        = [];

        foreach ($playedToursIDs as $tourID) {
            $resultForTour = $this->getTourResults($team1ID, $team2ID, $tourID);
            if (null !== $resultForTour) {
                $results[] = $resultForTour;
            }
        }

        return $results;
    }

    public function getTotals(): array
    {
        return [
            'games' => $this->getTotalGamesCount(),
            'team1wins' => $this->team1Wins,
            'team2wins' => $this->team2Wins,
            'draws' => $this->draws,
        ];
    }

    private function getTotalGamesCount(): int
    {
        return $this->team1Wins + $this->draws + $this->team2Wins;
    }

    private function getTourResults(int $team1ID, int $team2ID, int $tourID): ?array
    {
        $tourInfo         = $this->connector->getTourInfo($tourID);
        $results         = $this->connector->getTourResults($tourID);
        $filteredResults = $this->filterResults($results, $team1ID, $team2ID);

        if (\count($filteredResults) !== 2) {
            return null;
        }

        $team1Result = $this->filterResultsByTeam($filteredResults, $team1ID);
        $team2Result = $this->filterResultsByTeam($filteredResults, $team2ID);

        if (!isset(
            $team1Result[Connector::KEY_POINTS],
            $team2Result[Connector::KEY_POINTS]
        )
        ) {
            return null;
        }

        return [
            'result'      => $this->processResultForTour($team1Result, $team2Result),
            'tour_id'     => $tourID,
            'tour_name'   => $tourInfo[Connector::KEY_NAME],
            'team1points' => $team1Result[Connector::KEY_POINTS],
            'team2points' => $team2Result[Connector::KEY_POINTS],
        ];
    }

    private function filterResults(array $results, int $team1ID, int $team2ID): array
    {
        return \array_filter(
            $results,
            function ($result) use ($team1ID, $team2ID) {
                return ((int)$result['idteam'] === $team1ID
                    || (int)$result['idteam'] === $team2ID
                );
            }
        );
    }

    private function processResultForTour(array $team1Result, array $team2Result): string
    {
        $team1Points = $team1Result[Connector::KEY_POINTS];
        $team2Points = $team2Result[Connector::KEY_POINTS];

        if ($team1Points > $team2Points) {
            $this->team1Wins++;

            return self::RESULT_TEAM_1_WIN;
        }

        if ($team2Points > $team1Points) {
            $this->team2Wins++;

            return self::RESULT_TEAM_2_WIN;
        }

        $this->draws++;

        return self::RESULT_DRAW;
    }

    private function filterResultsByTeam(array $results, int $teamID): array
    {
        $filteredResults = \array_filter(
            $results,
            function ($result) use ($teamID) {
                return (int)$result['idteam'] === $teamID;
            }
        );

        return (array)\array_pop($filteredResults);
    }

    private function validateInput(int $team1ID, int $team2ID): void
    {
        if ($team1ID === $team2ID) {
            throw new SameTeamException();
        }
    }

    private function buildTeamArrayFromTeamIDs(int ...$teamIDs): array
    {
        $teams = [];
        foreach ($teamIDs as $teamID) {
            $teams[][Connector::KEY_TEAM_ID] = $teamID;
        }

        return $teams;
    }
}
