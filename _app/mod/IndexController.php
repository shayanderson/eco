<?php
/**
 * Index controller
 */
class IndexController
{
	/**
	 * Home action
	 */
	public function home()
	{
		view()->set('status', 'ready');
		view('home');
	}
}