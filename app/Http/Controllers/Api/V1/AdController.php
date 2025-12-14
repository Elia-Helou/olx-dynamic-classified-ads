<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Http\Resources\AdCollection;
use App\Http\Resources\AdResource;
use App\Services\AdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function __construct(private AdService $adService)
    {
    }

    public function store(StoreAdRequest $request): JsonResponse
    {
        $result = $this->adService->create($request->validated(), Auth::user());

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success(new AdResource($result['data']), $result['message'], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $result = $this->adService->getUserAds(Auth::user(), $perPage);

        if (!$result['success']) {
            return $this->error($result['message'], 500);
        }

        return $this->success(new AdCollection($result['data']), $result['message']);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $result = $this->adService->getAd($id);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Ad not found' ? 404 : 500;
            return $this->error($result['message'], $statusCode);
        }

        return $this->success(new AdResource($result['data']), $result['message']);
    }
}
