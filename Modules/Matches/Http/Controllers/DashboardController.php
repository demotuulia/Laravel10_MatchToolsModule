<?php

namespace Modules\Matches\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Services\DashboardService;
use Modules\Matches\Services\SearchService;

class DashboardController extends BaseController
{

    public function dashboard(Request $request, DashboardService $service): string
    {
        $formId = $request->get('form_id') ?? null;
        $stats = $service->dashboard($formId);
        return $this->jsonResponse(
            [
                'statistics' => $stats
            ],
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
