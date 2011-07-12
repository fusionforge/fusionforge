<?php

class oslcException extends Exception
{
	protected $code = 0;
	protected $exception_details = "OSLC Custom Exception";

	public function __construct($message, Exception $previous = null)
	{
		parent::__construct($message, $this->code, $previous);

	}

	public function getExceptionDetails()
	{
		return $this->exception_details;
	}
}

class ForbiddenException extends oslcException
{
	protected $code = 403;
	protected $exception_details = "Server has rejected request, as response content size many not be optimal";
}

class NotAcceptableForCRCollectionException extends oslcException
{
	protected $code = 406;
	protected $exception_details = "Server can not fulfill the request due to it's Accept headers";
}

class BadRequestException extends oslcException
{
	protected $code = 400;
	protected $exception_details = "Some of the parameters may not be properly structured. For e.g. malformed query parameters, malformed content (malformed XML)";
}

class NotFoundException extends oslcException
{
	protected $code = 404;
	protected $exception_details = "The request URL does not represent a URL on the server";
}

class ConflictException extends oslcException
{
	protected $code = 409;
	protected $exception_details = "Request content specifies a property that the resource doesn't support or has an invalid value";
}

class UnsupportedMediaTypeException extends oslcException
{
	protected $code = 415;
	protected $exception_details = "The Content-type of the body of the request is not known to the service provider";
}

class NotAcceptableException extends oslcException
{
	protected $code = 405;
	protected $exception_details = "Server can not fulfill the request due to it's Accept headers";
}

class GoneException extends oslcException
{
	protected $code = 410;
	protected $exception_details = "The resource no longer exists in the system ";
}

class PreconditionFailedException extends oslcException
{
	protected $code = 412;
	protected $exception_details = "The ETag supplied in the If-Match request header value did not match that of the resource being modified";
}

?>
