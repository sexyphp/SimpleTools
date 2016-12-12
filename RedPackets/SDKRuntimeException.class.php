<?php
// +----------------------------------------------------------------------
// | SimpleTools for PHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.sexyphp.com
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lianger <breeze323136@126.com>
// +----------------------------------------------------------------------
class  SDKRuntimeException extends Exception {

	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>