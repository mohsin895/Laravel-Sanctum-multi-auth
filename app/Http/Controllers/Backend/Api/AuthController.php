<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Response;
use App\Mail\EmailVerify;
use App\Mail\ForgetPassword;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserInfo;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB ;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z]/',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'user_contact' => 'nullable|max:20',
                'company_contact' => 'nullable|max:20',
            ]);
    
            $existingUser = User::where('email', trim($validatedData['email']))->first();
    
            if (!$existingUser) {
                $otpNumber = substr(str_shuffle('0123456789'), 0, 6);
                $user = new User();
                $user->name = $validatedData['name'];
                $user->role = 2;
                $user->email = strtolower(trim($validatedData['email']));
                $user->password = Hash::make($validatedData['password']);
                $user->remember_token = Hash::make($otpNumber);
                $user->is_verify = 2;
                $user->status = 2;
                $user->created_at = Carbon::now();
    
                if ($user->save()) {
                    $otp = new Otp();
                    $otp->user_id = $user->id;
                    $otp->otp = Hash::make($otpNumber);
                    $otp->expires_at = Carbon::now()->addMinutes(30);
                    $otp->save();

                    $companyInfo = New UserInfo();
                    $companyInfo->user_id = $user->id;
                    $companyInfo->company_name = $request->company_name;
                    $companyInfo->compnay_reg_number = $request->compnay_reg_number;
                    $companyInfo->	industry_type = $request->industry_type;
                    $companyInfo->	user_contact = $request->user_contact;
                    $companyInfo->	company_contact = $request->	company_contact;
                    $companyInfo->save();

                    try {
              
                        $email    = trim($request->email);
                            $data  =   array(
                                "title"=>"Trrimber Email verify ",
                                "otp"    =>$otpNumber,
                                'email' =>$email,
                            );
          
                  Mail::to($email)->send(new EmailVerify($data));
                  
                    } catch (Exception $err) {
                        DB::rollBack();
                        $msg = 'Mail Can not Send. Please try again.';
                        return Response::errorResponse($err, $msg);
                    }
    
                    DB::commit();
                    $msg = 'Your information has been stored successfully. Please check your email to verify your account.';
                    return Response::successResponse($msg, $user);
                } else {
                    DB::rollBack();
                    $msg = 'Failed to sign up. Please try again.';
                    return Response::failedResponse($msg, $user);
                }
            } else {
                DB::rollBack();
                $msg = 'Customer is already registered.';
                return Response::failedResponse($msg);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
             'email' => 'required|email',
            ]);
    
            $existingUser = User::where('email', trim($validatedData['email']))->first();
    
            if ($existingUser) {
                $otpNumber = substr(str_shuffle('0123456789'), 0, 6);
               
                $existingUser->remember_token = Hash::make($otpNumber);
            
                $existingUser->created_at = Carbon::now();
    
                if ($existingUser->save()) {
                    $otp = new Otp();
                    $otp->user_id = $existingUser->id;
                    $otp->otp = Hash::make($otpNumber);
                    $otp->expires_at = Carbon::now()->addMinutes(30);
                    $otp->save();

                    try {
              
                        $email    = trim($request->email);
                            $data  =   array(
                                "title"=>"Trrimber Email verify ",
                                "otp"    =>$otpNumber,
                                'email' =>$email,
                            );
          
                  Mail::to($email)->send(new EmailVerify($data));
                  
                    } catch (Exception $err) {
                        DB::rollBack();
                        $msg = 'Mail Can not Send. Please try again.';
                        return Response::errorResponse($err, $msg);
                    }
    
                    DB::commit();
                    $msg = ' Please check your email to verify your account.';
                    return Response::successResponse($msg, $existingUser);
                } else {
                    DB::rollBack();
                    $msg = 'Failed to sign up. Please try again.';
                    return Response::failedResponse($msg, $existingUser);
                }
            } else {
                DB::rollBack();
                $msg = 'Email is not Exist.Please registration';
                return Response::failedResponse($msg);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    

    public function verifyOtp(Request $request):JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validate the request
            $validatedData = $request->validate([
                'otp' => 'required|numeric',
                'email' => 'required|email',
            ]);
    
            // Fetch the user
            $existingUser = User::where('email', trim($validatedData['email']))->first();
            if (!$existingUser) {
                $msg = 'User not found.';
                return Response::failedResponse($msg);
              
            }
    
            // Fetch the OTP for the user
            $existingOtp = Otp::where('user_id', $existingUser->id)->orderBy('id','desc')->first();
            if (!$existingOtp) {
                $msg = 'OTP not found.';
                return Response::failedResponse($msg);
               
            }
    
            // Check if the OTP is valid
            $nowTime = Carbon::now();
            if (Hash::check($validatedData['otp'], $existingOtp->otp)) {
                if ($nowTime->lessThanOrEqualTo($existingOtp->expires_at)) {
                    $user = $existingUser;
                    $user->is_verify = 1;
                    $user->status = 1;
                    $user->email_verified_at =$nowTime;
                    if ($user->save()) {
                        $existingOtp->otp = NULL;
                        $existingOtp->save();
                        DB::commit();
                        $msg = 'Your information has been verified successfully.';
                        return Response::successResponse($msg,$user);
                     
                    } else {
                        DB::rollBack();
                        $msg = 'Failed to Verify. Please try again.';
                        return Response::failedResponse($msg,$user);
                      
                    }
                } else {
                    DB::rollBack();
                    $msg = 'OTP has expired.';
                    return Response::failedResponse($msg);
                  
                }
            } else {
                DB::rollBack();
                $msg = 'Otp is Invalid.';
                return Response::failedResponse($msg);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    public function login(Request $request):JsonResponse
    {
        $userData = ['email' => $request->email, 'password' => $request->password];
    
        $userInfo = User::where('email', $request->email)
                        ->where('status', '!=', 0)
                        ->first();
    
        if ($userInfo) {
            if($userInfo->email_verified_at	== null){
                $msg = 'Your Email Email is not verified.Plese verify your email';
                return Response::successResponse($msg);

            }else{
                if ($userInfo->status == 1) {
                    if (Hash::check($request->password, $userInfo->password)) {
                        $token = $userInfo->createToken($userInfo->role)->plainTextToken;
        
                        return response()->json([
                            'status' => true,
                            'token_type' => 'Bearer',
                            'token' => $token,
                            'message' => 'Login successfully',
                           
                            'dataInfo' => [
                                'id' => $userInfo->id,
                                'name' => $userInfo->name,
                                'email' => $userInfo->email,
                                'created_at' => $userInfo->created_at,
                                'role'=> $userInfo->role,
                            ],
                        ]);
                    } else {
                        
                        $msg = 'Wrong credentials. Please enter valid credentials.';
                        return Response::successResponse($msg);
                    }
                } else {
                   
                    $msg = 'Your account has been temporarily deactivated.';
                    return Response::successResponse($msg);
                }

            }
           
        } else {
           
            $msg = 'Invalid credentials.';
            return Response::successResponse($msg);
        }
    }


    public function forgetPassword(Request $request)
    {
   
        DB::beginTransaction();
        try {
          $customerInfo=User::where('email',trim($request->email))->first();
        if(!empty($customerInfo)){
      
            $otp= substr(str_shuffle('0123456789'), 0,4);
            $pass="Your Otp IS:".$otp." For Forget Password";

            $sendOtp = new Otp();
            $sendOtp->user_id = $customerInfo->id;
            $sendOtp->otp = $otp;
            $sendOtp->expires_at = Carbon::now()->addMinutes(30);
            $sendOtp->save();
            $email    = trim($request->email);
            $data  =   array(
                "title"=>"Trrimber ",
                "otp"    =>$pass,
               
                'email' =>$email,
            );
    
          
             Mail::to($email)->send(new ForgetPassword($data));
          


            DB::commit();
            $msg = 'A new Otp has been sent to your email.';
            return Response::successResponse($msg, $customerInfo);

      

     }else{
       
        $msg = 'Customer Information Not Found.';
         return Response::failedResponse($msg);

     }
    } catch (Exception $err) {
        DB::rollBack();
        $msg = 'Something went wrong. Please try again.';
        return Response::errorResponse($err, $msg);
    }
    }
    
    public function verifyOtpForgetPass(Request $request):JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validate the request
            $validatedData = $request->validate([
                'otp' => 'required|integer',
                'email' => 'required|email',
            ]);
    
            // Fetch the user
            $existingUser = User::where('email', trim($validatedData['email']))->first();
            if (!$existingUser) {
                $msg = 'User not found.';
                return Response::failedResponse($msg);
              
            }
    
            // Fetch the OTP for the user
            $existingOtp = Otp::where('otp',$validatedData['otp'])->first();
            if (!$existingOtp) {
                $msg = 'OTP not found.';
                return Response::failedResponse($msg);
               
            }
    
            // Check if the OTP is valid
            $nowTime = Carbon::now();
            if ($validatedData['otp'] == $existingOtp->otp) {
                if ($nowTime->lessThanOrEqualTo($existingOtp->expires_at)) {
                    $user = $existingUser;
                    // $user->is_verify = 1;
                    // $user->status = 1;
    
                    if ($user->save()) {
                        $existingOtp->otp = NULL;
                        $existingOtp->save();
                        DB::commit();
                        $msg = 'Otp verified successfully.';
                        return Response::successResponse($user,$msg);
                     
                    } else {
                        DB::rollBack();
                        $msg = 'Failed to update user status. Please try again.';
                        return Response::failedResponse($user,$msg);
                      
                    }
                } else {
                    DB::rollBack();
                    $msg = 'OTP has expired.';
                    return Response::failedResponse($msg);
                  
                }
            } else {
                DB::rollBack();
                $msg = 'Otp is Invalid.';
                return Response::failedResponse($msg);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    public function resetPass(Request $request):JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validate the request
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required|confirmed|min:8',
            ]);
    
            // Fetch the user
            $existingUser = User::where('email', trim($validatedData['email']))->first();
            if (!$existingUser) {
                $msg = 'User not found.';
                return Response::failedResponse($msg);
              
            }
    
            // Check if the OTP is valid
          
            if ($existingUser) {
               
                  
                    $existingUser->password = Hash::make($validatedData['password']);
                
    
                    if ($existingUser->save()) {
                      
                        DB::commit();
                        $msg = 'Password Reset successfully.';
                        return Response::successResponse($existingUser,$msg);
                     
                    } else {
                        DB::rollBack();
                        $msg = 'Failed to update. Please try again.';
                        return Response::failedResponse($existingUser,$msg);
                      
                    }
                
            } 
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    
}
