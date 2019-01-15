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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function indexAction(Request $request): Response
    {
        /** @var Face2FaceModel $model */
        $model         = $this->container['model.face2face'];
        $errorMessage  = '';
        $customTeam1ID = $request->get(self::PARAM_CUSTOM_TEAM1_ID);
        $customTeam2ID = $request->get(self::PARAM_CUSTOM_TEAM2_ID);
        $townFilter    = $request->get(self::PARAM_TOWN, '');
        $allSeasons    = $model->getAllSeasons();

        $defaultCountry = $request->get(
            self::PARAM_DEFAULT_COUNTRY,
            $this->session->get(self::PARAM_DEFAULT_COUNTRY) ?? Face2FaceModel::COUNTRY_GERMANY
        );
        $this->session->set(self::PARAM_DEFAULT_COUNTRY, $defaultCountry);

        $currentSeasonId = $request->get(
            self::PARAM_SEASON,
            $this->session->get(self::PARAM_SEASON) ?? Face2FaceModel::CURRENT_SEASON
        );
        $this->session->set(self::PARAM_SEASON, $currentSeasonId);

        $team1ID = $customTeam1ID ?: (int)$request->get(self::PARAM_TEAM1_ID, Face2FaceModel::DEFAULT_TEAM1_ID);
        $team2ID = $customTeam2ID ?: (int)$request->get(self::PARAM_TEAM2_ID, Face2FaceModel::DEFAULT_TEAM2_ID);

        try {
            $team1    = $model->getTeamById($team1ID);
            $team2    = $model->getTeamById($team2ID);
            $results  = $model->getResultsForTeams($team1ID, $team2ID, $currentSeasonId);
            $allTeams = $model->getFilteredTeams($defaultCountry, $townFilter);
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
                'allSeasons' => $allSeasons,
                'currentSeasonId' => $currentSeasonId,
                'defaultCountry' => $defaultCountry,
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
