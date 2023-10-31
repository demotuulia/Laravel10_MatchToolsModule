<?php

namespace Modules\Matches\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Matches\Http\Requests\BaseApiRequest\MatchesProfileRequest;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Services\MatchesProfileService;

class MatchesProfilesController extends BaseController
{

    public function index(Request $request, MatchesProfileService $service): string
    {
        $order = $request->get('order_by') ?? null;
        $search = $request->get('search') ?? null;
        $userId = $request->get('user_id') ?? null;
        $page = $request->get('page') ?? 0;
        $pageSize = $request->get('page_size') ?? 15;

        $result = $service->search(
            $search,
            $userId,
            $order,
            $page,
            $pageSize,
        );

        $data = $result['data']->toArray();
        $data = array_values($data);
        unset($result['data']);

        if (count($data) == 0) {
            $result['message'] = 'TRNS_NO_ITEMS_FOUND';
        }

        return $this->jsonResponse(
            $data,
            Response::HTTP_OK,
            $result,
            true,
        );
    }

    public function create()
    {
    }

    public function store(MatchesProfileRequest $request, MatchesProfileService $service): string
    {
        return $this->jsonResponse(
            [$service->store($request->post())->toArray()],
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    public function show(Request $request, int $profileId, MatchesProfileService $service): string
    {
        // If no profile id, get the first profile of the user
        if ($profileId == 0) {
            $user = Auth::user();
            $userId = $user->id;
            $profile = MatchesProfile::where('user_id', $userId)->first();
            $profileId = $profile->id;
        }
        $profile = $service->show($profileId);
        if (!is_null($profile)) {
            return $this->jsonResponse(
                [$profile->toArray()],
                Response::HTTP_OK,
                [],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $profileId
            ],
            true,
        );
    }

    public function edit($id)
    {
    }

    public function update(MatchesProfileRequest $request, int $profileId, MatchesProfileService $service): string
    {
        $profile = $service->update($profileId, $request->post());

        if (!is_null($profile)) {
            return $this->jsonResponse(
                [$profile->toArray()],
                Response::HTTP_OK,
                [],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            [
                'message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $profileId
            ],
            true,
        );
    }

    public function destroy(Request $request, int $profileId, MatchesProfileService $service): string
    {
        if ($service->delete($profileId)) {
            return $this->jsonResponse(
                [],
                Response::HTTP_OK,
                [
                    'message' => 'TRNS_ITEM_DELETED_WITH_ID_' . $profileId
                ],
                true,
            );
        }

        return $this->jsonResponse(
            [],
            Response::HTTP_NOT_FOUND,
            ['message' => 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $profileId],
            true,
        );
    }
}
