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
		view('home', [
			'phpver' => phpversion(),
			'status' => 'ready'
		]);
	}
}