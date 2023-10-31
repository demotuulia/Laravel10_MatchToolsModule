<?php

namespace Modules\Matches\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Services\SearchService;

class SearchController extends BaseController
{

    public function search(Request $request, SearchService $searchService): string
    {
        $page = $request->get('page') ?? 0;
        $pageSize = $request->get('page_size') ?? 15;
        $exact  = $request->get('exact') ?? false;
        $profileId = $request->get('profile_id')
            ?? MatchesProfile::where('user_id', Auth::user()->id)->first()->id;
        $result = $searchService->search(
            $profileId,
            $exact,
            $page,
            $pageSize,
        );

        $result['data'] = $result['data']->toArray();
        $result['data']= array_values( $result['data']);
        $data = empty($result['data'])
            ? []
            : array_map(
                function ($data) {
                    return json_decode($data)[0];
                },
                $result['data']
            );

        return $this->jsonResponse(
            $data,
            Response::HTTP_OK,
            $result,
            true,
        );

    }
}
