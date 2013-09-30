<?php
/**
 * @package nutshell-plugin
 * @author @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\mvc
{

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Http 
	{
		const DEFAULT_PROTOCOL	= 'HTTP/1.1';
		
		const FASTCGI_PROTOCOL	= 'Status:';
		
		/**
		 * 
		 * This means that the server has received the request headers, and that the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request). 
		 * If the request body is large, sending it to a server when a request has already been rejected based upon inappropriate headers is inefficient. 
		 * To have a server check if the request could be accepted based on the request's headers alone, a client must send Expect: 100-continue as a header in its initial request and check if a 100 Continue status code is received in response before continuing (or receive 417 Expectation Failed and not continue)
		 */
		const INFO_100_CONTINUE = "100 Continue";
		
		/**
		 * This means the requester has asked the server to switch protocols and the server is acknowledging that it will do so.
		 */
		const INFO_101_SWITCHING_PROTOCOLS = "101 Switching Protocols";
		
		/**
		 * 
		 * As a WebDAV request may contain many sub-requests involving file operations, it may take a long time to complete the request. 
		 * This code indicates that the server has received and is processing the request, but no response is available yet. 
		 * This prevents the client from timing out and assuming the request was lost.
		 * (WebDav)
		 */
		const INFO_102_PROCESSING = "102 Processing";
		
		/**
		 * This code is used in the Resumable HTTP Requests Proposal to resume aborted PUT or POST requests.
		 */
		const INFO_103_PROCESSING = "103 Checkpoint";
		
		/**
		 * Standard response for successful HTTP requests. 
		 * The actual response will depend on the request method used. 
		 * In a GET request, the response will contain an entity corresponding to the requested resource. 
		 * In a POST request the response will contain an entity describing or containing the result of the action.
		 */
		const SUCCESS_200_OK = "200 OK";
		
		/**
		 * The request has been fulfilled and resulted in a new resource being created.
		 */
		const SUCCESS_201_CREATED = "201 Created";
		
		/**
		 * The request has been accepted for processing, but the processing has not been completed. 
		 * The request might or might not eventually be acted upon, as it might be disallowed when processing actually takes place.
		 */
		const SUCCESS_202_ACCEPTED = "202 Accepted";
		
		/**
		 * The server successfully processed the request, but is returning information that may be from another source.
		 */
		const SUCCESS_203_NON_AUTHORITATIVE_INFORMATION = "203 Non-Authoritative Information";
		
		/**
		 * The server successfully processed the request, but is not returning any content.
		 */
		const SUCCESS_204_NO_CONTENT = "204 No Content";
		
		/**
		 * The server successfully processed the request, but is not returning any content. 
		 * Unlike a 204 response, this response requires that the requester reset the document view.
		 */
		const SUCCESS_205_RESET_CONTENT = "205 Reset Content";
		
		/**
		 * The server is delivering only part of the resource due to a range header sent by the client. 
		 * The range header is used by tools like wget to enable resuming of interrupted downloads, or split a download into multiple simultaneous streams.
		 */
		const SUCCESS_206_PARTIAL_CONTENT = "206 Partial Content";
		
		/**
		 * The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.
		 * (WebDav)
		 */
		const SUCCESS_207_MULTI_STATUS = "207 Multi-Status";
		
		/**
		 * The server has fulfilled a GET request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.
		 */
		const SUCCESS_226_IM_USED = "226 IM Used";
		
		/**
		 * Indicates multiple options for the resource that the client may follow. 
		 * It, for instance, could be used to present different format options for video, list files with different extensions, or word sense disambiguation.
		 */
		const REDIRECT_300_MULTIPLE_CHOICES = "300 Multiple Choices";
		
		/**
		 * This and all future requests should be directed to the given URI.
		 */
		const REDIRECT_301_MOVED_PERMANENTLY = "301 Moved Permanently";
		
		/**
		 * This is an example of industrial practice contradicting the standard.
		 * HTTP/1.0 specification (RFC 1945) required the client to perform a temporary redirect (the original describing phrase was "Moved Temporarily"), but popular browsers implemented 302 with the functionality of a 303 See Other. 
		 * Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours.
		 * However, some Web applications and frameworks use the 302 status code as if it were the 303.
		 */
		const REDIRECT_302_FOUND = "302 Found";
		
		/**
		 * The response to the request can be found under another URI using a GET method. 
		 * When received in response to a POST (or PUT/DELETE), it should be assumed that the server has received the data and the redirect should be issued with a separate GET message.
		 */
		const REDIRECT_303_SEE_OTHER = "303 See Other";
		
		/**
		 * Indicates the resource has not been modified since last requested.
		 * Typically, the HTTP client provides a header like the If-Modified-Since header to provide a time against which to compare. 
		 * Using this saves bandwidth and reprocessing on both the server and client, as only the header data must be sent and received in comparison to the entirety of the page being re-processed by the server, then sent again using more bandwidth of the server and client.
		 */
		const REDIRECT_304_NOT_MODIFIED = "304 Not Modified";
		
		/**
		 * Many HTTP clients (such as Mozilla and Internet Explorer) do not correctly handle responses with this status code, primarily for security reasons.
		 */
		const REDIRECT_305_USE_PROXY = "305 Use Proxy";
		
		/**
		 * No longer used. Originally meant "Subsequent requests should use the specified proxy."
		 */
		const REDIRECT_306_SWITCH_PROXY = "306 Switch Proxy";
		
		/**
		 * In this occasion, the request should be repeated with another URI, but future requests can still use the original URI. 
		 * In contrast to 303, the request method should not be changed when reissuing the original request. 
		 * For instance, a POST request must be repeated using another POST request.
		 */
		const REDIRECT_307_TEMPORARY_REDIRECT = "307 Temporary Redirect";
		
		/**
		 * This code is used in the Resumable HTTP Requests Proposal to resume aborted PUT or POST requests.
		 */
		const REDIRECT_308_RESUME_INCOMPLETE = "308 Resume Incomplete";
		
		/**
		 * The request cannot be fulfilled due to bad syntax.
		 */
		const ERROR_400_BAD_REQUEST = "400 Bad Request";
		
		/**
		* Similar to 403 Forbidden, but specifically for use when authentication is possible but has failed or not yet been provided.
		* The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource. 
		* See Basic access authentication and Digest access authentication.
		*/
		const ERROR_401_UNAUTHORIZED = "401 Unauthorized";
		
		/**
		* Reserved for future use. 
		* The original intention was that this code might be used as part of some form of digital cash or micropayment scheme, but that has not happened, and this code is not usually used. 
		* As an example of its use, however, Apple's MobileMe service generates a 402 error ("httpStatusCode:402" in the Mac OS X Console log) if the MobileMe account is delinquent.
		*/
		const ERROR_402_PAYMENT_REQUIRED = "402 Payment Required";
		
		/**
		* The request was a legal request, but the server is refusing to respond to it.
		* Unlike a 401 Unauthorized response, authenticating will make no difference.
		*/
		const ERROR_403_FORBIDDEN = "403 Forbidden";
		
		/**
		* The requested resource could not be found but may be available again in the future.
		* Subsequent requests by the client are permissible.
		*/
		const ERROR_404_NOT_FOUND = "404 Not Found";
		
		/**
		* A request was made of a resource using a request method not supported by that resource; for example, using GET on a form which requires data to be presented via POST, or using PUT on a read-only resource.
		*/
		const ERROR_405_METHOD_NOT_ALLOWED = "405 Method Not Allowed";
		
		/**
		* The requested resource is only capable of generating content not acceptable according to the Accept headers sent in the request.
		*/
		const ERROR_406_NOT_ACCEPTABLE = "406 Not Acceptable";
		
		/**
		* The client must first authenticate itself with the proxy.
		*/
		const ERROR_407_PROXY_AUTH_REQUIRED = "407 Proxy Authentication Required";
		
		/**
		* The server timed out waiting for the request.
		* According to W3 HTTP specifications: 
		* "The client did not produce a request within the time that the server was prepared to wait. 
		* The client MAY repeat the request without modifications at any later time."
		*/
		const ERROR_408_REQUEST_TIMEOUT = "408 Request Timeout";
		
		/**
		* Indicates that the request could not be processed because of conflict in the request, such as an edit conflict.
		*/
		const ERROR_409_CONFLICT = "409 Conflict";
		
		/**
		* Indicates that the resource requested is no longer available and will not be available again.
		* This should be used when a resource has been intentionally removed and the resource should be purged. 
		* Upon receiving a 410 status code, the client should not request the resource again in the future. 
		* Clients such as search engines should remove the resource from their indices. 
		* Most use cases do not require clients and search engines to purge the resource, and a "404 Not Found" may be used instead.
		*/
		const ERROR_410_GONE = "410 Gone";
		
		/**
		* The request did not specify the length of its content, which is required by the requested resource.
		*/
		const ERROR_411_LENGTH_REQUIRED = "411 Length Required";
		
		/**
		* The server does not meet one of the preconditions that the requester put on the request.
		*/
		const ERROR_412_PRECONDITION_FAILED = "412 Precondition Failed";
		
		/**
		* The request is larger than the server is willing or able to process.[
		*/
		const ERROR_413_REQUEST_ENTITY_TOO_LARGE = "413 Request Entity Too Large";
		
		/**
		* The URI provided was too long for the server to process.
		*/
		const ERROR_414_REQUEST_URI_TOO_LONG = "414 Request-URI Too Long";
		
		/**
		* The request entity has a media type which the server or resource does not support.
		* For example, the client uploads an image as image/svg+xml, but the server requires that images use a different format.
		*/
		const ERROR_415_UNSUPPORTED_MEDIA_TYPE = "415 Unsupported Media Type";
		
		/**
		* The client has asked for a portion of the file, but the server cannot supply that portion.
		* For example, if the client asked for a part of the file that lies beyond the end of the file.
		*/
		const ERROR_416_REQUESTED_RANGE_NOT_SATISFIABLE = "416 Requested Range Not Satisfiable";
		
		/**
		* The server cannot meet the requirements of the Expect request-header field.
		*/
		const ERROR_417_EXPECTATION_FAILED = "417 Expectation Failed";
		
		/**
		* The request was well-formed but was unable to be followed due to semantic errors.
		* (WebDav)
		*/
		const ERROR_422_UNPROCESSABLE_ENTITY = "422 Unprocessable Entity";
		
		/**
		* The resource that is being accessed is locked.
		* (WebDav)
		*/
		const ERROR_423_LOCKED = "423 Locked";
		
		/**
		* The request failed due to failure of a previous request (e.g. a PROPPATCH).
		* (WebDav)
		*/
		const ERROR_424_FAILED_DEPENDENCY = "424 Failed Dependency";
		
		/**
		* Defined in drafts of "WebDAV Advanced Collections Protocol", but not present in "Web Distributed Authoring and Versioning (WebDAV) Ordered Collections Protocol".
		* (WebDav)
		*/
		const ERROR_425_UNORDERED_COLLECTION = "425 Unordered Collection";
		
		/**
		* The client should switch to a different protocol such as TLS/1.0.
		*/
		const ERROR_426_UPGRADE_REQUIRED = "426 Upgrade Required";
		
		
		/**
		 * A generic error message, given when no more specific message is suitable.
		 */
		const S_ERROR_500_INTERNAL_SERVER_ERROR = "500 Internal Server Error";
		
		/**
		* The server either does not recognise the request method, or it lacks the ability to fulfill the request.
		*/
		const S_ERROR_501_NOT_IMPLEMENTED = "501 Not Implemented";
		
		/**
		* The server was acting as a gateway or proxy and received an invalid response from the upstream server.
		*/
		const S_ERROR_502_BAD_GATEWAY = "502 Bad Gateway";
		
		/**
		* The server is currently unavailable (because it is overloaded or down for maintenance).
		* Generally, this is a temporary state.
		*/
		const S_ERROR_503_SERVICE_UNAVAILABLE = "503 Service Unavailable";
		
		/**
		* The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
		*/
		const S_ERROR_504_GATEWAY_TIMEOUT = "504 Gateway Timeout";
		
		/**
		* The server does not support the HTTP protocol version used in the request.
		*/
		const S_ERROR_505_HTTP_VERSION_NOT_SUPPORTED = "505 HTTP Version Not Supported";
		
		/**
		* Transparent content negotiation for the request results in a circular reference.
		*/
		const S_ERROR_506_VARIANT_ALSO_NEGOTIATES = "506 Variant Also Negotiates";
		
		/**
		* The server is unable to store the representation needed to complete the request.
		*/
		const S_ERROR_507_INSUFFICIENT_STORAGE = "507 Insufficient Storage";
		
		/**
		* This status code, while used by many servers, is not specified in any RFCs.
		*/
		const S_ERROR_509_BANDWIDTH_LIMIT_EXCEEDED = "509 Bandwidth Limit Exceeded";
		
		/**
		* Further extensions to the request are required for the server to fulfill it.
		*/
		const S_ERROR_510_NOT_EXTENDED = "510 Not Extended";
		
		
	}
}
?>