<?php

use LaravelRU\User\Forms\LoginForm;
use LaravelRU\User\Forms\RegistrationForm;
use LaravelRU\User\Models\Confirmation;
use LaravelRU\User\Models\User;
use LaravelRU\User\Models\UserInfo;
use LaravelRU\User\Models\UserSocialNetwork;

class AuthController extends BaseController {

	/**
	 * @var RegistrationForm
	 */
	private $registrationForm;

	/**
	 * @var LoginForm
	 */
	private $loginForm;

	public function __construct(RegistrationForm $registrationForm, LoginForm $loginForm)
	{
		$this->registrationForm = $registrationForm;
		$this->loginForm = $loginForm;
	}

	public function registration()
	{
		$jsToken = Str::quickRandom(10);
		Session::set('jsToken', $jsToken);

		return View::make('auth.registration', compact('jsToken'));
	}

	public function submitRegistration()
	{
		$input = Input::only('username', 'email', 'password', 'jsToken');

		$this->registrationForm->validate($input);

		unset($input['jsToken']);

		$user = User::create($input);
		$user->info()->save(new UserInfo);
		$user->social()->save(new UserSocialNetwork);

		$confirmationString = Str::quickRandom(20);

		$userConfirmation = new Confirmation();
		$userConfirmation->code = $confirmationString;

		$user->confirmation()->save($userConfirmation);

		$email = $input['email'];
		$password = $input['password'];
		Mail::queue('emails/auth/register', ['confirmationString' => $confirmationString], function ($message) use ($email)
		{
			$message->from('postmaster@sharedstation.net');
			$message->to($email);
			$message->subject('Подтверждение регистрации');
		});

		return Redirect::route('auth.registration.pre-confirmation');
	}

	public function preConfirmation()
	{
		return View::make('auth.pre-confirmation');
	}

	public function checkConfirmation($code)
	{
		$userConfirmation = Confirmation::where('code', $code)->first();

		if ($userConfirmation)
		{
			$user = $userConfirmation->user;
			$user->is_confirmed = 1;
			$user->save();
			$userConfirmation->delete();

			Auth::login($user);

			return View::make('auth.confirmation-success');
		}

		return View::make('auth.confirmation-error');
	}

	public function login()
	{
		return View::make('auth.login');
	}

	public function submitLogin()
	{
		$login = Input::get('login');
		$password = Input::get('password');

		$this->loginForm->validate([
			'login' => $login,
			'password' => $password,
		]);

		$loginBy = strpos($login, '@') > 1 ? 'email' : 'username';

		$success = Auth::attempt([
			$loginBy => $login,
			'password' => $password,
		], true, true);

		if ( ! $success)
		{
			return Redirect::route('auth.login')
				->withErrors(['wrong_input' => 'Неправильный логин, email или пароль.'])
				->onlyInput('login');
		}

		return Redirect::intended();
	}

	public function logout()
	{
		Auth::logout();

		return Redirect::route('home');
	}

}
