<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\Model\ResultsModel;
use Vladimino\Discoverist\Rating\Connector;

/**
 * Class ResultsController
 * @package Vladimino\Discoverist\Controller
 */
class ResultsController extends AbstractController
{
    const PARAM_TOUR = 'tournament';
    const PARAM_SEARCH = 'search';

    const SEARCH_DEFAULT = 'Германия';

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $model           = new ResultsModel(new Connector());
        $tours           = $model->getTours();
        $currentTourId   = $request->get(self::PARAM_TOUR, $tours[0]['id']);
        $searchFilter    = $request->get(self::PARAM_SEARCH, self::SEARCH_DEFAULT);
        $currentTourInfo = $model->getTourInfo($currentTourId);
        $results         = $model->getResultsFromTournament($currentTourId, $searchFilter);

        return $this->render(
            'results',
            [
                'tours'           => $tours,
                'currentTourId'   => $currentTourId,
                'currentTourInfo' => $currentTourInfo,
                'results'         => $results,
            ]
        );
    }
}
