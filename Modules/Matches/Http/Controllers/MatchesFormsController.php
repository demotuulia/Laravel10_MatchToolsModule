<?php

namespace Modules\Matches\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Matches\Http\Requests\BaseApiRequest\MatchesFormRequest;
use Modules\Matches\Jobs\ReindexViewsJob;
use Modules\Matches\Services\MatchesFormService;

class MatchesFormsController extends BaseController
{

    public function index(Request $request, MatchesFormService $service): string
    {
        $order = $request->get('order_by') ?? null;
        $search = $request->get('search') ?? null;

        $data = $service->search($search, $order);
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

    public function store(MatchesFormRequest $request, MatchesFormService $service): string
    {
        $form = $service->store($request->post())->toArray();
        if (isset($form[0]['matches'])) {
            dispatch(new ReindexViewsJob($form[0]['id']));
        }

        return $this->jsonResponse(
            $form,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    public function show(Request $request, int $formId, MatchesFormService $service): string
    {
        $form = $service->show($formId);
        if (!empty($form)) {
            return $this->jsonResponse(
                $form,
                Response::HTTP_OK,
                [],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $formId
            ],
            true,
        );
    }

    public function edit($id)
    {
    }

    public function update(MatchesFormRequest $request, int $formId, MatchesFormService $service): string
    {
        $form = $service->update($formId, $request->post());

        if (!empty($form)) {
            if (isset($form[0]['matches'])) {
                dispatch(new ReindexViewsJob($form[0]['id']));
            }
            return $this->jsonResponse(
                $form,
                Response::HTTP_OK,
                [],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $formId
            ],
            true,
        );
    }

    public function destroy(Request $request, int $formId, MatchesFormService $service): string
    {
        if ($service->delete($formId)) {
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

