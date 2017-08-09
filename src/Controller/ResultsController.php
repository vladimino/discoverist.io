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
    const PARAM_TOUR    = 'tournament';
    const PARAM_COUNTRY = 'country';
    const PARAM_TOWN    = 'town';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Vladimino\Discoverist\Error\LoadConfigException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function indexAction(Request $request): Response
    {
        /** @var ResultsModel $model */
        $model = $this->container['model.results'];
        $tours = $model->getTours();

        $currentTourId = $request->get(self::PARAM_TOUR, $tours[0]['id']);
        $countryFilter = $request->get(self::PARAM_COUNTRY, ResultsModel::SEARCH_VALUE_GERMANY);
        $townFilter    = $request->get(self::PARAM_TOWN, '');

        $currentTourInfo = $model->getTourInfo($currentTourId);
        $results         = $model->getRealTimeResultsFromTournament($currentTourId, $countryFilter, $townFilter);

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
