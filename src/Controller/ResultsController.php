<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\Model\ResultsModel;

/**
 * Class ResultsController
 *
 * @package Vladimino\Discoverist\Controller
 */
class ResultsController extends AbstractController
{
    const PARAM_TOUR         = 'tournament';
    const PARAM_DEFAULT_TOWN = 'defaultTown';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function indexAction(Request $request): Response
    {
        $defaultTown = $request->get(self::PARAM_DEFAULT_TOWN, ResultsModel::TOWN_BERLIN);
        /** @var ResultsModel $model */
        $model       = $this->container['model.results'];
        $tours       = $model->getPlayedToursForTown($defaultTown);

        $currentTourId = $request->get(self::PARAM_TOUR, $tours[0]['idtournament'] ?? 0);
        $countryFilter = $request->get(self::PARAM_COUNTRY, ResultsModel::COUNTRY_GERMANY);
        $townFilter    = $request->get(self::PARAM_TOWN, '');

        $currentTourInfo = $model->getTourInfo($currentTourId);
        $results         = $model->getFilteredResultsFromTournament($currentTourId, $countryFilter, $townFilter);

        return $this->render(
            'results',
            [
                'tours' => $tours,
                'currentTourId' => $currentTourId,
                'currentTourInfo' => $currentTourInfo,
                'results' => $results,
                'section' => self::SECTION_RESULTS,
            ]
        );
    }
}
