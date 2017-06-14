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
			'status' => 'ready'
		]);
	}
}