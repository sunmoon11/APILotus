<?php

include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];
$subDomain = $_SESSION['sub_domain'];


if (!isset($user) || $user == '' || !isset($userRole) || $userRole == '' || $userRole == 0)
{
    header("Location: ./login.php");
    return;
}
// session timeout
$now = time();
if ($now - $_SESSION['last_activity'] > 9660)
{
    session_unset();
    session_destroy();
    header("Location: ./login.php");
    return;
}
$_SESSION['last_activity'] = time();
if (isset($_COOKIE[session_name()]))
    setcookie(session_name(), $_COOKIE[session_name()], time() + 9660);
if ($_SESSION['last_activity'] - $_SESSION['created'] > 9660)
{
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
session_write_close();
// check client ip
$dbApi = DBApi::getInstance();
$features =  $dbApi->getFeatureEnableList($subDomain);
$features = explode(',', $features);
if (!in_array(2, $features))
{
    header("Location: ./dashboard.php");
    return;
}
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

$user_name = $user;
$tab_name = "Monitor URL Management";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
    <?php include('./monitor_url_management_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="row">        
	 	<div class="col-xs-8">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Monitor URL Management</div>
					<div class="col-xs-2 site_management_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning site_management_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
	  				<div class="col-xs-5">
	  					<button type="button" class="btn btn-default btn-sm btn_url_add" style="margin-right: 10px"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add URL</button>
                        <input type="file" class="input_csv_picker" style="display: none">
                        <button type="button" class="btn btn-default btn-sm btn_url_import" style="margin-right: 10px"><span class="glyphicon glyphicon-import" aria-hidden="true"></span>&nbsp;Import CSV</button>
						<button type="button" class="btn btn-default btn-sm btn_url_setting"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span>&nbsp;Setting</button>
	  				</div>
					<div class="col-xs-7" style="text-align:right; padding-right: 30px">
						<div class="btn-group monitor_pagination" role="group">
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-sm dropdown-toggle count_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:60px">
								10 <span class="caret"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right count_dropdown_menu" role="menu" style="width: 80px !important; min-width: 80px !important">
								<li><a href="#">10</a></li>
								<li><a href="#">20</a></li>
								<li><a href="#">50</a></li>
								<li><a href="#">100</a></li>
								<li><a href="#">500</a></li>
								<li><a href="#">1000</a></li>
							</ul>
						</div>
					</div>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<thead>
						<tr>
							<th>#</th>
							<th>Site Name</th>
							<th>Site URL</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody class="table_url_body">
					</tbody>
				</table>
			</div>
 		</div>
 		<div class="col-xs-4">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-12" style="padding-left: 0"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" style="color: #3d3d3d !important"></span> HTTP response status codes</div>
				</div>
	  			<div class="row crm_board_row">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">
						<button type="button" id="btn_code_100" class="btn btn-default btn-sm btn_http_code" style="width: 70px; margin-right: 10px">1xx</button>
						<button type="button" id="btn_code_200" class="btn btn-default btn-sm btn_http_code active" style="width: 70px; margin-right: 10px">2xx</button>
						<button type="button" id="btn_code_300" class="btn btn-default btn-sm btn_http_code" style="width: 70px; margin-right: 10px">3xx</button>
						<button type="button" id="btn_code_400" class="btn btn-default btn-sm btn_http_code" style="width: 70px; margin-right: 10px">4xx</button>
						<button type="button" id="btn_code_500" class="btn btn-default btn-sm btn_http_code" style="width: 70px">5xx</button>
					</div>
				</div>
				<div id="code_type_100" class="code_content" style="display: none;">
					<div class="code_title"><h4 style="margin-bottom: 5px"><b>Information responses</b></h4></div>
					<div class="code_number"><button type="button" id="btn_expand_100" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span></button> 100 Continue</div>
					<div id="code_expand_100" class="code_description">This interim response indicates that everything so far is OK and that the client should continue with the request or ignore it if it is already finished.</div>
					<div class="code_number"><button type="button" id="btn_expand_101" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 101 Switching Protocol</div>
					<div id="code_expand_101" class="code_description" style="display: none;">This code is sent in response to an Upgrade request header by the client, and indicates the protocol the server is switching too.</div>
					<div class="code_number"><button type="button" id="btn_expand_102" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 102 Processing</div>
					<div id="code_expand_102" class="code_description" style="display: none;">This code indicates that the server has received and is processing the request, but no response is available yet.</div>
				</div>
				<div id="code_type_200" class="code_content">
					<div class="code_title"><h4 style="margin-bottom: 5px"><b>Successful responses</b></h4></div>
					<div class="code_number"><button type="button" id="btn_expand_200" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span></button> 200 OK</div>
					<div id="code_expand_200" class="code_description">The request has succeeded. The meaning of a success varies depending on the HTTP method:<br/>GET: The resource has been fetched and is transmitted in the message body.<br/>HEAD: The entity headers are in the message body.<br/>POST: The resource describing the result of the action is transmitted in the message body.<br/>TRACE: The message body contains the request message as received by the server.</div>
					<div class="code_number"><button type="button" id="btn_expand_201" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 201 Created</div>
					<div id="code_expand_201" class="code_description" style="display: none;">The request has succeeded and a new resource has been created as a result of it. This is typically the response sent after a PUT request.</div>
					<div class="code_number"><button type="button" id="btn_expand_202" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 202 Accepted</div>
					<div id="code_expand_202" class="code_description" style="display: none;">The request has been received but not yet acted upon. It is non-committal, meaning that there is no way in HTTP to later send an asynchronous response indicating the outcome of processing the request. It is intended for cases where another process or server handles the request, or for batch processing.</div>
					<div class="code_number"><button type="button" id="btn_expand_203" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 203 Non-Authoritative Information</div>
					<div id="code_expand_203" class="code_description" style="display: none;">This response code means returned meta-information set is not exact set as available from the origin server, but collected from a local or a third party copy. Except this condition, 200 OK response should be preferred instead of this response.</div>
					<div class="code_number"><button type="button" id="btn_expand_204" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 204 No Content</div>
					<div id="code_expand_204" class="code_description" style="display: none;">There is no content to send for this request, but the headers may be useful. The user-agent may update its cached headers for this resource with the new ones.</div>
					<div class="code_number"><button type="button" id="btn_expand_205" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 205 Reset Content</div>
					<div id="code_expand_205" class="code_description" style="display: none;">This response code is sent after accomplishing request to tell user agent reset document view which sent this request.</div>
					<div class="code_number"><button type="button" id="btn_expand_206" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 206 Partial Content</div>
					<div id="code_expand_206" class="code_description" style="display: none;">This response code is used because of range header sent by the client to separate download into multiple streams.</div>
					<div class="code_number"><button type="button" id="btn_expand_207" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 207 Multi-Status</div>
					<div id="code_expand_207" class="code_description" style="display: none;">A Multi-Status response conveys information about multiple resources in situations where multiple status codes might be appropriate.</div>
					<div class="code_number"><button type="button" id="btn_expand_208" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 208 Multi-Status</div>
					<div id="code_expand_208" class="code_description" style="display: none;">Used inside a DAV: propstat response element to avoid enumerating the internal members of multiple bindings to the same collection repeatedly.</div>
					<div class="code_number"><button type="button" id="btn_expand_226" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 226 IM Used (HTTP Delta encoding)</div>
					<div id="code_expand_226" class="code_description" style="display: none;">The server has fulfilled a GET request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.</div>
				</div>
				<div id="code_type_300" class="code_content" style="display: none;">
					<div class="code_title"><h4 style="margin-bottom: 5px"><b>Redirection messages</b></h4></div>
					<div class="code_number"><button type="button" id="btn_expand_300" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span></button> 300 Multiple Choice</div>
					<div id="code_expand_300" class="code_description">The request has more than one possible responses. User-agent or user should choose one of them. There is no standardized way to choose one of the responses.</div>
					<div class="code_number"><button type="button" id="btn_expand_301" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 301 Moved Permanently</div>
					<div id="code_expand_301" class="code_description" style="display: none;">This response code means that URI of requested resource has been changed. Probably, new URI would be given in the response.</div>
					<div class="code_number"><button type="button" id="btn_expand_302" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 302 Found</div>
					<div id="code_expand_302" class="code_description" style="display: none;">This response code means that URI of requested resource has been changed temporarily. New changes in the URI might be made in the future. Therefore, this same URI should be used by the client in future requests.</div>
					<div class="code_number"><button type="button" id="btn_expand_303" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 303 See Other</div>
					<div id="code_expand_303" class="code_description" style="display: none;">Server sent this response to directing client to get requested resource to another URI with an GET request.</div>
					<div class="code_number"><button type="button" id="btn_expand_304" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 304 Not Modified</div>
					<div id="code_expand_304" class="code_description" style="display: none;">This is used for caching purposes. It is telling to client that response has not been modified. So, client can continue to use same cached version of response.</div>
					<div class="code_number"><button type="button" id="btn_expand_305" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 305 Use Proxy</div>
					<div id="code_expand_305" class="code_description" style="display: none;">Was defined in a previous version of the HTTP specification to indicate that a requested response must be accessed by a proxy. It has been deprecated due to security concerns regarding in-band configuration of a proxy.</div>
					<div class="code_number"><button type="button" id="btn_expand_306" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 306 unused</div>
					<div id="code_expand_306" class="code_description" style="display: none;">This response code is no longer used, it is just reserved currently. It was used in a previous version of the HTTP 1.1 specification.</div>
					<div class="code_number"><button type="button" id="btn_expand_307" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 307 Temporary Redirect</div>
					<div id="code_expand_307" class="code_description" style="display: none;">Server sent this response to directing client to get requested resource to another URI with same method that used prior request. This has the same semantic than the 302 Found HTTP response code, with the exception that the user agent must not change the HTTP method used: if a POST was used in the first request, a POST must be used in the second request.</div>
					<div class="code_number"><button type="button" id="btn_expand_308" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 308 Permanent Redirect</div>
					<div id="code_expand_308" class="code_description" style="display: none;">This means that the resource is now permanently located at another URI, specified by the Location: HTTP Response header. This has the same semantics as the 301 Moved Permanently HTTP response code, with the exception that the user agent must not change the HTTP method used: if a POST was used in the first request, a POST must be used in the second request.</div>
				</div>
				<div id="code_type_400" class="code_content" style="display: none;">
					<div class="code_title"><h4 style="margin-bottom: 5px"><b>Client error responses</b></h4></div>
					<div class="code_number"><button type="button" id="btn_expand_400" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span></button> 400 Bad Request</div>
					<div id="code_expand_400" class="code_description">This response means that server could not understand the request due to invalid syntax.</div>
					<div class="code_number"><button type="button" id="btn_expand_401" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 401 Unauthorized</div>
					<div id="code_expand_401" class="code_description" style="display: none;">Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated". That is, the client must authenticate itself to get the requested response.</div>
					<div class="code_number"><button type="button" id="btn_expand_402" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 402 Payment Required</div>
					<div id="code_expand_402" class="code_description" style="display: none;">This response code is reserved for future use. Initial aim for creating this code was using it for digital payment systems however this is not used currently.</div>
					<div class="code_number"><button type="button" id="btn_expand_403" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 403 Forbidden</div>
					<div id="code_expand_403" class="code_description" style="display: none;">The client does not have access rights to the content, i.e. they are unauthorized, so server is rejecting to give proper response. Unlike 401, the client's identity is known to the server.</div>
					<div class="code_number"><button type="button" id="btn_expand_404" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 404 Not Found</div>
					<div id="code_expand_404" class="code_description" style="display: none;">The server can not find requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 to hide the existence of a resource from an unauthorized client. This response code is probably the most famous one due to its frequent occurence on the web.</div>
					<div class="code_number"><button type="button" id="btn_expand_405" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 405 Method Not Allowed</div>
					<div id="code_expand_405" class="code_description" style="display: none;">The request method is known by the server but has been disabled and cannot be used. For example, an API may forbid DELETE-ing a resource. The two mandatory methods, GET and HEAD, must never be disabled and should not return this error code.</div>
					<div class="code_number"><button type="button" id="btn_expand_406" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 406 Not Acceptable</div>
					<div id="code_expand_406" class="code_description" style="display: none;">This response is sent when the web server, after performing server-driven content negotiation, doesn't find any content following the criteria given by the user agent.</div>
					<div class="code_number"><button type="button" id="btn_expand_407" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 407 Proxy Authentication Required</div>
					<div id="code_expand_407" class="code_description" style="display: none;">This is similar to 401 but authentication is needed to be done by a proxy.</div>
					<div class="code_number"><button type="button" id="btn_expand_408" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 408 Request Timeout</div>
					<div id="code_expand_408" class="code_description" style="display: none;">This response is sent on an idle connection by some servers, even without any previous request by the client. It means that the server would like to shut down this unused connection. This response is used much more since some browsers, like Chrome, Firefox 27+, or IE9, use HTTP pre-connection mechanisms to speed up surfing. Also note that some servers merely shut down the connection without sending this message.</div>
					<div class="code_number"><button type="button" id="btn_expand_409" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 409 Conflict</div>
					<div id="code_expand_409" class="code_description" style="display: none;">This response is sent when a request conflicts with the current state of the server.</div>
					<div class="code_number"><button type="button" id="btn_expand_410" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 410 Gone</div>
					<div id="code_expand_410" class="code_description" style="display: none;">This response would be sent when the requested content has been permenantly deleted from server, with no forwarding address. Clients are expected to remove their caches and links to the resource. The HTTP specification intends this status code to be used for "limited-time, promotional services". APIs should not feel compelled to indicate resources that have been deleted with this status code.</div>
					<div class="code_number"><button type="button" id="btn_expand_411" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 411 Length Required</div>
					<div id="code_expand_411" class="code_description" style="display: none;">Server rejected the request because the Content-Length header field is not defined and the server requires it.</div>
					<div class="code_number"><button type="button" id="btn_expand_412" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 412 Precondition Failed</div>
					<div id="code_expand_412" class="code_description" style="display: none;">The client has indicated preconditions in its headers which the server does not meet.</div>
					<div class="code_number"><button type="button" id="btn_expand_413" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 413 Payload Too Large</div>
					<div id="code_expand_413" class="code_description" style="display: none;">Request entity is larger than limits defined by server; the server might close the connection or return an Retry-After header field.</div>
					<div class="code_number"><button type="button" id="btn_expand_414" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 414 URI Too Long</div>
					<div id="code_expand_414" class="code_description" style="display: none;">The URI requested by the client is longer than the server is willing to interpret.</div>
					<div class="code_number"><button type="button" id="btn_expand_415" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 415 Unsupported Media Type</div>
					<div id="code_expand_415" class="code_description" style="display: none;">The media format of the requested data is not supported by the server, so the server is rejecting the request.</div>
					<div class="code_number"><button type="button" id="btn_expand_416" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 416 Requested Range Not Satisfiable</div>
					<div id="code_expand_416" class="code_description" style="display: none;">The range specified by the Range header field in the request can't be fulfilled; it's possible that the range is outside the size of the target URI's data.</div>
					<div class="code_number"><button type="button" id="btn_expand_417" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 417 Expectation Failed</div>
					<div id="code_expand_417" class="code_description" style="display: none;">This response code means the expectation indicated by the Expect request header field can't be met by the server.</div>
					<div class="code_number"><button type="button" id="btn_expand_418" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 418 I'm a teapot</div>
					<div id="code_expand_418" class="code_description" style="display: none;">The server refuses the attempt to brew coffee with a teapot.</div>
					<div class="code_number"><button type="button" id="btn_expand_421" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 421 Misdirected Request</div>
					<div id="code_expand_421" class="code_description" style="display: none;">The request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI.</div>
					<div class="code_number"><button type="button" id="btn_expand_422" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 422 Unprocessable Entity</div>
					<div id="code_expand_422" class="code_description" style="display: none;">The request was well-formed but was unable to be followed due to semantic errors.</div>
					<div class="code_number"><button type="button" id="btn_expand_423" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 423 Locked</div>
					<div id="code_expand_423" class="code_description" style="display: none;">The resource that is being accessed is locked.</div>
					<div class="code_number"><button type="button" id="btn_expand_424" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 424 Failed Dependency</div>
					<div id="code_expand_424" class="code_description" style="display: none;">The request failed due to failure of a previous request.</div>
					<div class="code_number"><button type="button" id="btn_expand_426" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 426 Upgrade Required</div>
					<div id="code_expand_426" class="code_description" style="display: none;">The server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server sends an Upgrade header in a 426 response to indicate the required protocol(s).</div>
					<div class="code_number"><button type="button" id="btn_expand_428" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 428 Precondition Required</div>
					<div id="code_expand_428" class="code_description" style="display: none;">The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.</div>
					<div class="code_number"><button type="button" id="btn_expand_429" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 429 Too Many Requests</div>
					<div id="code_expand_429" class="code_description" style="display: none;">The user has sent too many requests in a given amount of time ("rate limiting").</div>
					<div class="code_number"><button type="button" id="btn_expand_431" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 431 Request Header Fields Too Large</div>
					<div id="code_expand_431" class="code_description" style="display: none;">The server is unwilling to process the request because its header fields are too large. The request MAY be resubmitted after reducing the size of the request header fields.</div>
					<div class="code_number"><button type="button" id="btn_expand_451" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 451 Unavailable For Legal Reasons</div>
					<div id="code_expand_451" class="code_description" style="display: none;">The user requests an illegal resource, such as a web page censored by a government.</div>
				</div>
				<div id="code_type_500" class="code_content" style="display: none;">
					<div class="code_title"><h4 style="margin-bottom: 5px"><b>Server error responses</b></h4></div>
					<div class="code_number"><button type="button" id="btn_expand_500" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span></button> 500 Internal Server Error</div>
					<div id="code_expand_500" class="code_description">The server has encountered a situation it doesn't know how to handle.</div>
					<div class="code_number"><button type="button" id="btn_expand_501" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 501 Not Implemented</div>
					<div id="code_expand_501" class="code_description" style="display: none;">The request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD.</div>
					<div class="code_number"><button type="button" id="btn_expand_502" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 502 Bad Gateway</div>
					<div id="code_expand_502" class="code_description" style="display: none;">This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response.</div>
					<div class="code_number"><button type="button" id="btn_expand_503" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 503 Service Unavailable</div>
					<div id="code_expand_503" class="code_description" style="display: none;">The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This responses should be used for temporary conditions and the Retry-After: HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached.</div>
					<div class="code_number"><button type="button" id="btn_expand_504" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 504 Gateway Timeout</div>
					<div id="code_expand_504" class="code_description" style="display: none;">This error response is given when the server is acting as a gateway and cannot get a response in time.</div>
					<div class="code_number"><button type="button" id="btn_expand_505" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 505 HTTP Version Not Supported</div>
					<div id="code_expand_505" class="code_description" style="display: none;">The HTTP version used in the request is not supported by the server.</div>
					<div class="code_number"><button type="button" id="btn_expand_506" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 506 Variant Also Negotiates</div>
					<div id="code_expand_506" class="code_description" style="display: none;">The server has an internal configuration error: transparent content negotiation for the request results in a circular reference.</div>
					<div class="code_number"><button type="button" id="btn_expand_507" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 507 Insufficient Storage</div>
					<div id="code_expand_507" class="code_description" style="display: none;">The server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process.</div>
					<div class="code_number"><button type="button" id="btn_expand_508" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 508 Loop Detected</div>
					<div id="code_expand_508" class="code_description" style="display: none;">The server detected an infinite loop while processing the request.</div>
					<div class="code_number"><button type="button" id="btn_expand_510" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 510 Not Extended</div>
					<div id="code_expand_510" class="code_description" style="display: none;">Further extensions to the request are required for the server to fulfill it.</div>
					<div class="code_number"><button type="button" id="btn_expand_511" class="btn btn-link btn-sm btn_code_expand"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button> 511 Network Authentication Required</div>
					<div id="code_expand_511" class="code_description" style="display: none;">The 511 status code indicates that the client needs to authenticate to gain network access.</div>
				</div>
			</div>
 		</div>
	 </div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
