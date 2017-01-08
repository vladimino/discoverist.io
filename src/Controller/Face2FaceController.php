<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\Error\SameTeamException;
use Vladimino\Discoverist\Model\Face2FaceModel;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class ResultsController
 * @package Vladimino\Discoverist\Controller
 */
class Face2FaceController extends AbstractController
{
    const PARAM_TEAM1_ID = 'team1ID';
    const PARAM_TEAM2_ID = 'team2ID';

    const DEFAULT_TEAM1_ID = 3476; // Псевдопептиды
    const DEFAULT_TEAM2_ID = 49888; // Палладины

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $model        = new Face2FaceModel(new Connector());
        $errorMessage = '';
        $team1ID      = $request->get(self::PARAM_TEAM1_ID, self::DEFAULT_TEAM1_ID);
        $team2ID      = $request->get(self::PARAM_TEAM2_ID, self::DEFAULT_TEAM2_ID);

        try {
            $face2faceResults = $model->getFace2FaceResults($team1ID, $team2ID);
        } catch (SameTeamException $e) {
            $face2faceResults = [];
            $errorMessage     = $e->getMessage();
        }

        return $this->render(
            'face2face',
            [
                'team1id'       => $team1ID,
                'team2id'       => $team2ID,
                'teams'         => $model->getTeams(),
                'team1'         => $model->getTeamById($team1ID),
                'team2'         => $model->getTeamById($team2ID),
                'error_message' => $errorMessage,
                'fac2face'      => $face2faceResults,
                'totals'        => $model->getTotals(),
                'section'       => 'f2f',
            ]
        );
    }
}
