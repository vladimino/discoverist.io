<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Error\LoadConfigException;
use Vladimino\Discoverist\Error\SameTeamException;

/**
 * Class Face2FaceModel
 *
 * @package Model
 */
class Face2FaceModel extends AbstractRatingAwareModel
{
    protected $team1Wins = 0;
    protected $team2Wins = 0;
    protected $draws     = 0;

    const RESULT_TEAM_1_WIN = 'team1win';
    const RESULT_TEAM_2_WIN = 'team2win';
    const RESULT_DRAW       = 'draw';

    /**
     * @param int $team1ID
     * @param int $team2ID
     *
     * @return array
     * @throws \Exception
     * @throws SameTeamException
     * @throws LoadConfigException
     */
    public function getResultsForTeams(int $team1ID, int $team2ID): array
    {
        $this->validateInput($team1ID, $team2ID);

        $results = [];
        foreach ($this->allTours as $tour) {
            $resultForTour = $this->getTourResults($team1ID, $team2ID, $tour);
            if (null !== $resultForTour) {
                $results[] = $resultForTour;
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getTotals(): array
    {
        return [
            'games' => $this->getTotalGamesCount(),
            'team1wins' => $this->team1Wins,
            'team2wins' => $this->team2Wins,
            'draws' => $this->draws,
        ];
    }

    /**
     * @return int
     */
    private function getTotalGamesCount(): int
    {
        return $this->team1Wins + $this->draws + $this->team2Wins;
    }

    /**
     * @param int $team1ID
     * @param int $team2ID
     * @param array $tour
     *
     * @return array
     * @throws \Exception
     */
    public function getTourResults(int $team1ID, int $team2ID, array $tour): ?array
    {
        $results         = $this->connector->getTourResults($tour['id']);
        $filteredResults = $this->filterResults($results, $team1ID, $team2ID);

        if (\count($filteredResults) !== 2) {
            return null;
        }

        $team1Result = $this->filterResultsByTeam($filteredResults, $team1ID);
        $team2Result = $this->filterResultsByTeam($filteredResults, $team2ID);

        if (!isset(
            $team1Result[AbstractRatingAwareModel::KEY_POINTS],
            $team2Result[AbstractRatingAwareModel::KEY_POINTS]
        )
        ) {
            return null;
        }

        return [
            'result' => $this->processResultForTour($team1Result, $team2Result),
            'tour_id' => $tour['id'],
            'tour_name' => $tour['name'],
            'team1points' => $team1Result[AbstractRatingAwareModel::KEY_POINTS],
            'team2points' => $team2Result[AbstractRatingAwareModel::KEY_POINTS],
        ];
    }

    /**
     * @param array $results
     * @param int $team1ID
     * @param int $team2ID
     *
     * @return array
     */
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

    /**
     * @param array $team1Result
     * @param array $team2Result
     *
     * @return string
     */
    private function processResultForTour(array $team1Result, array $team2Result): string
    {
        $team1Points = $team1Result[AbstractRatingAwareModel::KEY_POINTS];
        $team2Points = $team2Result[AbstractRatingAwareModel::KEY_POINTS];

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

    /**
     * @param array $results
     * @param int $teamID
     *
     * @return array
     */
    private function filterResultsByTeam(array $results, int $teamID): array
    {
        $filteredResults = \array_filter(
            $results,
            function ($result) use ($teamID) {
                return (int)$result['idteam'] === $teamID;
            }
        );

        return \array_pop($filteredResults);
    }

    /**
     * @param int $team1ID
     * @param int $team2ID
     *
     * @throws \Vladimino\Discoverist\Error\LoadConfigException
     * @throws \Vladimino\Discoverist\Error\SameTeamException
     */
    private function validateInput(int $team1ID, int $team2ID): void
    {
        if ($team1ID === $team2ID) {
            throw new SameTeamException();
        }

        if (empty($this->allTours)) {
            throw new LoadConfigException();
        }
    }
}
