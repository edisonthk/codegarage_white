<?php namespace App\Http\Controllers;

use Response;
use Config;
use Auth;
use App\Model\Account;
use App\Edisonthk\Exception\OAuthAccessDenied;
use App\Edisonthk\Exception\UnknownOAuthError;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;


class AccountController extends BaseController {


	private $accountServices;
	private $workbook;

	public function __construct(
		\App\Edisonthk\AccountService $accountServices,
		\App\Edisonthk\WorkbookService $workbook
	) {

		$this->accountServices = $accountServices;
		$this->workbook = $workbook;
	}


	public function getUserinfo()
	{
      $user = $this->accountServices->getLoginedUserInfo();
      if(is_null($user)) {
          return Response::json(null, 403);
      }

      return Response::json($user, 200);
	}

	public function getOauth2callback(Request $request)
	{
        try {
            $input = $this->accountServices->handleOauth2callback($request);

            $user = $this->accountServices->getAccountByEmail($input["email"]);
            if(!is_null($user)) {
                // user already login
                Auth::login($user, true);
                return redirect(Config::get("app.app_url"));
            }

            $input["profile_image"] = $input["picture"];
            $input["google_id"] = $input["id"];
            $account = $this->accountServices->generate($input);

						$this->workbook->create($account->name, "", $account->id);

            return redirect(action("AuthController@getErrorAuth")."?type=".AuthController::TYPE_REGISTER."&email=".$account->email);

        } catch( OAuthAccessDenied $e ) {
            return redirect(action("AuthController@getErrorAuth")."?type=".AuthController::TYPE_OAUTH_DENIED_ACCESS);
        } catch( UnknownOAuthError $e ) {
            return redirect(action("AuthController@getErrorAuth")."?type=".AuthController::TYPE_UNKNOWN_OAUTH_ERROR."&email=".$user->email);
        }
	}


}
