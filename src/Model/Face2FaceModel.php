<?php

namespace Vladimino\Discoverist\Model;

use Vladimino\Discoverist\Error\LoadConfigException;
use Vladimino\Discoverist\Error\SameTeamException;

/**
 * Class Face2FaceModel
 * @package Model
 */
class Face2FaceModel extends AbstractRatingAwareModel
{
    protected $team1Wins = 0;
    protected $team2Wins = 0;
    protected $draws = 0;

    /**
     * @param int $team1ID
     * @param int $team2ID
     *
     * @return array
     * @throws SameTeamException
     * @throws LoadConfigException
     */
    public function getFace2FaceResults($team1ID, $team2ID)
    {
        if ($team1ID == $team2ID) {
            throw new SameTeamException();
        }

        if (empty($this->tours)) {
            throw new LoadConfigException('Ошибка загрузки конфигурации, список турниров пуст');
        }

        $face2face       = [];
        $this->team1Wins = 0;
        $this->team2Wins = 0;
        $this->draws     = 0;

        foreach ($this->tours as $tour) {
            $results    = $this->connector->getTourResults($tour['id']);
            $f2fResults = array_filter(
                $results,
                function ($result) use ($team1ID, $team2ID) {
                    return ($result['idteam'] == $team1ID || $result['idteam'] == $team2ID);
                }
            );

            if (count($f2fResults) !== 2) {
                continue;
            }

            $f2fTeam1Result = array_pop(
                array_filter(
                    $f2fResults,
                    function ($result) use ($team1ID) {
                        return $result['idteam'] == $team1ID;
                    }
                )
            );
            $f2fTeam2Result = array_pop(
                array_filter(
                    $f2fResults,
                    function ($result) use ($team2ID) {
                        return $result['idteam'] == $team2ID;
                    }
                )
            );

            if (empty($f2fTeam1Result['questions_total'])
                || empty($f2fTeam1Result['questions_total'])
            ) {
                continue;
            }

            if ($f2fTeam1Result['questions_total'] > $f2fTeam2Result['questions_total']) {
                $this->team1Wins++;
                $result = 'team1win';
            } elseif ($f2fTeam2Result['questions_total'] > $f2fTeam1Result['questions_total']) {
                $this->team2Wins++;
                $result = 'team2win';
            } else {
                $this->draws++;
                $result = 'draw';
            }

            $face2face[] = [
                'tour_id'     => $tour['id'],
                'tour_name'   => $tour['name'],
                'team1points' => $f2fTeam1Result['questions_total'],
                'team2points' => $f2fTeam2Result['questions_total'],
                'result'      => $result,
            ];
        }

        return $face2face;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        return [
            'team1wins' => $this->team1Wins,
            'team2wins' => $this->team2Wins,
            'draws'     => $this->draws,
        ];
    }
}
