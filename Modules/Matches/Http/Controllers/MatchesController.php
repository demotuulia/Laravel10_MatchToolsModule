<?php

namespace Modules\Matches\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Modules\Matches\Http\Requests\BaseApiRequest\MatchesRequest;
use Modules\Matches\Jobs\ReindexViewsJob;
use Modules\Matches\Models\Matches;
use Modules\Matches\Services\MatchesService;

class MatchesController extends BaseController
{
    public function index(Request $request, MatchesService $service): string
    {

        $formId = $request->get('form_id');
        $order = $request->get('order_by') ?? null;
        $search = $request->get('search') ?? null;

        $data = $service->search($formId, $search, $order);
        $meta = $data->count() == 0 ? ['message' => 'TRNS_NO_ITEMS_FOUND'] : [];
        return $this->jsonResponse(
            $data->toArray(),
            Response::HTTP_OK,
            $meta,
            true,
        );
    }

    public function create()
    {
    }

    public function store(MatchesRequest $request, MatchesService $service): string
    {
        $values = $request->post();
        $match = $service->store($request->post());

        dispatch(new ReindexViewsJob($values['matches_form_id']));
        return $this->jsonResponse(
            $match->toArray(),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    public function show(Request $request,  $matchId, MatchesService $service): string
    {
        /** @var Collection<Matches> $match */
        $match = $service->show($matchId);
        if ($match->isNotEmpty()) {
            return $this->jsonResponse(
                $match->toArray(),
                Response::HTTP_OK,
                [],
                true
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $matchId
            ],
            true,
        );
    }

    public function edit($id)
    {}

    public function update(MatchesRequest $request,int $matchId, MatchesService $service): string
    {
        /** @var Collection<Matches> $match */
        $match = $service->update($matchId, $request->post());
        if ($match->isNotEmpty()) {
            dispatch(new ReindexViewsJob($match->first()->id));
            return $this->jsonResponse(
                $match->toArray(),
                Response::HTTP_OK,
                [],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $matchId
            ],
            true,
        );
    }

    public function destroy(Request $request, int $formId, MatchesService $service): string
    {
        if ($service->delete($formId)) {
            dispatch(new ReindexViewsJob($formId));
            return $this->jsonResponse(
                [],
                Response::HTTP_OK,
                [
                    'message' => 'TRNS_ITEM_DELETED_WITH_ID_' . $formId
                ],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            ['message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $formId],
            true,
        );
    }
}
