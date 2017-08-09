<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\Model\Face2FaceModel;

/**
 * Class ResultsController
 *
 * @package Vladimino\Discoverist\Controller
 */
class Face2FaceController extends AbstractController
{
    const PARAM_TEAM1_ID        = 'team1ID';
    const PARAM_TEAM2_ID        = 'team2ID';
    const PARAM_CUSTOM_TEAM1_ID = 'team1IDcustom';
    const PARAM_CUSTOM_TEAM2_ID = 'team2IDcustom';

    const DEFAULT_TEAM1_ID = 3476; // Псевдопептиды
    const DEFAULT_TEAM2_ID = 4649; // Берлитанты

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Vladimino\Discoverist\Error\TeamNotFoundException
     * @throws \Vladimino\Discoverist\Error\LoadConfigException
     */
    public function indexAction(Request $request): Response
    {
        /** @var Face2FaceModel $model */
        $model         = $this->container['model.face2face'];
        $errorMessage  = '';
        $customTeam1ID = $request->get(self::PARAM_CUSTOM_TEAM1_ID);
        $customTeam2ID = $request->get(self::PARAM_CUSTOM_TEAM2_ID);
        $team1ID       = $customTeam1ID ?: (int)$request->get(self::PARAM_TEAM1_ID, self::DEFAULT_TEAM1_ID);
        $team2ID       = $customTeam2ID ?: (int)$request->get(self::PARAM_TEAM2_ID, self::DEFAULT_TEAM2_ID);

        try {
            $team1    = $model->getTeamById($team1ID);
            $team2    = $model->getTeamById($team2ID);
            $results  = $model->getResultsForTeams($team1ID, $team2ID);
            $allTeams = $model->getAllTeams();
        } catch (\Exception $exception) {
            $results      = [];
            $errorMessage = $exception->getMessage();
        }

        return $this->render(
            'face2face',
            [
                'team1id' => $team1ID,
                'customteam1id' => $customTeam1ID,
                'team2id' => $team2ID,
                'customteam2id' => $customTeam2ID,
                'teams' => $allTeams ?? [],
                'team1' => $team1 ?? [],
                'team2' => $team2 ?? [],
                'error_message' => $errorMessage,
                'fac2face' => $results,
                'totals' => $model->getTotals(),
                'section' => self::SECTION_F2F,
            ]
        );
    }
}
