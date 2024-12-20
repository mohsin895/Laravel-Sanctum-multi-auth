<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Response;
use App\Models\Rating;

class RatingController extends Controller
{
    public function dataInfoAddOrUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Use firstOrNew to fetch the record or create a new one
            $dataInfo = Rating::firstOrNew(['product_id' => $request->dataId]);
            $dataInfo->rating = $request->rating;

            // Save the model
            $dataInfo->save();

            DB::commit();

            $msg = $dataInfo->wasRecentlyCreated 
                ? 'Successfully added data.' 
                : 'Successfully updated data.';
                
            return Response::successResponse($msg, $dataInfo);
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
}
