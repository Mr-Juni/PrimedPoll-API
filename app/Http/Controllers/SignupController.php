<?php
namespace App\Http\Controllers;

use App\User;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class SignupController extends Controller
{
		public function register(Request $request)
		
		{
			$this->validateRequest($request);

			$verifycode = (str_random(6));

			//start temporay transaction
			DB::beginTransaction();

			try {
				
				$user = User::create([
				'email' => $request->input('email'),
				'password' => Hash::make($request->get('password')),
					'verifycode' => $verifycode
				]);

				Mail::to($user->email)->send(new VerifyEmail($user));

				$msg['success'] = "Thanks for signing up! A Verification Mail has been Sent to $user->email";
				
				//if operation was successful save changes to database
				DB::commit();

				return response()->json($msg, 200);



			}catch(\Exception $e) {


				$msg['error'] = "Account Not created, Try Again!". $e;
				return response()->json($msg, 422);

				//else rollback all changes
				DB::rollBack();

			}

			
		}	
	
    public function validateRequest(Request $request){
		$rules = [
			'email' => 'required|email|unique:users',
    	'password' => 'required|min:6|confirmed',
		];
		$messages = [
			'required' => ':attribute is required',
			'email' => ':attribute not a valid format',
	];
		$this->validate($request, $rules, $messages);
		}
		
}