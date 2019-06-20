<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\Model\ResultsModel;
use Vladimino\Discoverist\Rating\Connector;

class ResultsController extends AbstractController
{
    private const PARAM_TOUR         = 'tournament';
    private const PARAM_DEFAULT_TOWN = 'defaultTown';

    public function indexAction(Request $request): Response
    {
        $defaultTown = $request->get(
            self::PARAM_DEFAULT_TOWN,
            $this->session->get(self::PARAM_DEFAULT_TOWN) ?? ResultsModel::TOWN_BERLIN
        );
        $this->session->set(self::PARAM_DEFAULT_TOWN, $defaultTown);

        $defaultCountry = $request->get(
            self::PARAM_DEFAULT_COUNTRY,
            $this->session->get(self::PARAM_DEFAULT_COUNTRY) ?? ResultsModel::COUNTRY_GERMANY
        );
        $this->session->set(self::PARAM_DEFAULT_COUNTRY, $defaultCountry);

        $currentSeasonId = $request->get(
            self::PARAM_SEASON,
            $this->session->get(self::PARAM_SEASON) ?? ResultsModel::CURRENT_SEASON
        );
        $this->session->set(self::PARAM_SEASON, $currentSeasonId);

        /** @var ResultsModel $model */
        $model     = $this->container['model.results'];
        $tours     = $model->getPlayedToursForTown($defaultTown, $currentSeasonId);
        $toursColl = \collect($tours);

        $currentTourId   = $request->get(self::PARAM_TOUR, $toursColl->first()[Connector::KEY_TOUR_ID] ?? 0);
        $currentTourInfo = $model->getTourInfo($currentTourId);

        $allSeasons      = $model->getAllSeasons();

        $townFilter = $request->get(self::PARAM_TOWN, '');
        $results    = $model->getFilteredResultsFromTournament($currentTourId, $defaultCountry, $townFilter);

        return $this->render(
            'results',
            [
                'tours' => $tours,
                'allSeasons' => $allSeasons,
                'defaultTown' => $defaultTown,
                'defaultCountry' => $defaultCountry,
                'currentSeasonId' => $currentSeasonId,
                'currentTourId' => $currentTourId,
                'currentTourInfo' => $currentTourInfo,
                'results' => $results,
                'section' => self::SECTION_RESULTS,
            ]
        );
    }
}
