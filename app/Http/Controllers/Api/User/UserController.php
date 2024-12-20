<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\Response;
use Illuminate\Support\Facades\DB ;
use Illuminate\Support\Facades\Auth ;


class UserController extends Controller
{
    public function info(): JsonResponse
    {
        DB::beginTransaction();
        try {
        $userId = Auth::guard('sanctum')->user()->id;

        // Retrieve user with relationship 'userInfo'
        $dataInfo = User::with('userInfo')->where('id', $userId)->first();
    

        if ($dataInfo) { 
          
            DB::commit();
            $msg = 'Data Found Success.';
            return Response::successResponse($msg,$dataInfo);


        } else {
           

            $msg = 'Requested Data Not Found.';
            return Response::failedResponse($msg,$dataInfo);
        }
      } catch (\Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    public function updateInfo(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userId = Auth::guard('sanctum')->user()->id;
    
            // Fetch user with userInfo relation
            $userInfo = User::with('userInfo')->where('id', $userId)->first();
    
            if (!$userInfo) {
                $msg = 'User info not found.';
                return Response::notFoundResponse($msg);
            }
            if ($request->name_key === 'yes') {
                $userInfo->name = $request->name;
            }
         
            $userInfoDetail = $userInfo->userInfo()->firstOrNew();
    
            if ($request->phone_key === 'yes') {
                $userInfoDetail->user_contact = $request->user_contact;
            }
    
            if ($request->contact_key === 'yes') {
                $userInfoDetail->company_contact = $request->company_contact;
            }
    
            if ($request->company_key === 'yes') {
                $userInfoDetail->company_name = $request->company_name;
            }
    
            if ($request->compnay_reg_number_key === 'yes') {
                $userInfoDetail->compnay_reg_number = $request->compnay_reg_number;
            }
    
            if ($request->industry_type_key === 'yes') {
                $userInfoDetail->industry_type = $request->industry_type;
            }
    
            // Handle image upload
            if ($request->hasFile('image') && $request->image_key === 'yes') {
                $fileName = 'user-' . time() . '.' . $request->image->extension();
                $request->image->move(public_path('images/'), $fileName);
    
                // Delete old image if it exists
                if ($userInfoDetail->image) {
                    $oldImagePath = public_path('images/') . $userInfoDetail->image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
    
                $userInfoDetail->image = $fileName;
            }
    
            // Save userInfoDetail
            $userInfoDetail->user_id = $userInfo->id; // Ensure relation is set
            $userInfoDetail->save();
            $userInfo->save();
    
            // Reload userInfo to reflect updated data
            $userInfo->load('userInfo');
    
            DB::commit();
    
            $msg = 'Updated successfully.';
            return Response::successResponse($msg, $userInfo);
    
        } catch (\Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    public function logout(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = Auth::guard('sanctum')->user(); // Get the authenticated user
            if ($user) {
                // Revoke the current token
                $user->currentAccessToken()->delete();


                $msg = 'Logout successful.';
                return Response::successResponse($msg);
            }
    
            
            $msg = 'No authenticated user found.';
            return Response::successResponse($msg);
            
            // Unauthorized
        } catch (\Exception $err) {
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    
    
    
}
