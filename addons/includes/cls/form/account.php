<?php
namespace addons\includes\cls\form;

class account extends \lib\form
{
	public function __construct($function=null)
	{
		if ($function && method_exists($this, $function))
		{
			$this->$function();
		}
		else
		{
			// if(DEBUG)
			// 	var_dump('Please pass correct function name as parameter');
			return;
		}
	}

	private function login()
	{
		$this->mobile	 = $this->make('#mobile')->label(null)->desc(T_("Enter your registered mobile"))
							 ->value(((isset($_COOKIE["mobile"]))?htmlspecialchars('+'.$_COOKIE["mobile"]):null))->autocomplete('off');
		$this->password = $this->make('#password')->name('password')->label(null)->pl(T_('Password'))->desc(T_("Enter your password"));
		$this->remember_me = $this->make('checkbox')->name('remember_me')->label(T_('Remember me'));
		$this->submit	 = $this->make('submit')->value(T_('Login'))->title(T_('Login'))->class('button primary row-clear');
	}

	private function signup()
	{
		$this->mobile	 = $this->make('#mobile')->label(null)
							 ->value(((isset($_COOKIE["mobile"]))?htmlspecialchars('+'.$_COOKIE["mobile"]):null));
		$this->password = $this->make('#password')->name('password')->label(null)->pl(T_('Password'));
		$this->submit	 = $this->make('submit')->value(T_('Create an account'))->title(T_('Create an account'))->class('button primary row-clear');
	}

	private function verification()
	{
		if(isset($_SESSION['tmp']['verify_mobile']))
		{
			$this->mobile	= $this->make('#mobile')->label(null)->readonly('readonly')->tabindex('-1')->value($_SESSION['tmp']['verify_mobile']);
		}
		else
		{
			$this->mobile	= $this->make('#mobile')->label(null);

		}
		$this->code		= $this->make('code')->label(null)->pl(T_('Code'))->maxlength(4)->autofocus()->autocomplete('off')
							->required()->pattern('[0-9]{4}')->title(T_('input 4 number'))
							->pos('hint--bottom')->desc(T_("Check your mobile and enter the code"));
		$this->submit	= $this->make('submit')->value(T_('Verification'))->title(T_('Verification'))->class('button primary row-clear');
	}

	private function recovery()
	{
		$this->mobile	= $this->make('#mobile')->label(null)
							->value(((isset($_COOKIE["mobile"]))?htmlspecialchars('+'.$_COOKIE["mobile"]):null));
		$this->submit	= $this->make('submit')->value(T_('Recovery'))->title(T_('Recovery'))->class('button primary row-clear');
	}

	private function changepass()
	{
		if(!isset($_SESSION['tmp']['verify_mobile_referer']))
		{
			$this->old      = $this->make('#password')->name('password-old')->autofocus()->label(null)->pl(T_('Current Password'));
		}
		$this->password = $this->make('#password')->name('password-new')->label(null)->pl(T_('New Password'));
		$this->submit	= $this->make('submit')->value(T_('Change it'))->title(T_('Change my password'))->class('button primary row-clear');
	}
}
?>