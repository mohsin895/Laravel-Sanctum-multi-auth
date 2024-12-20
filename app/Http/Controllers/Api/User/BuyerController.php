<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Response;
use App\Models\Buyer;
use App\Models\Rating;

class BuyerController extends Controller
{
    public function dataInfoAddOrUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'dataId' => 'required',
                'buyer_email' => 'required|email',
                'buyer_name' => 'required',
              
            ]);
          
            $dataInfo = Buyer::firstOrNew(['product_id' => $request->dataId]);
            $dataInfo->buyer_name = $validatedData['buyer_name'];
            $dataInfo->buyer_email =$validatedData ['buyer_email'];
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
