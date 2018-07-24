<?php

require_once '../daemon/api/DBApi.php';


// get subdomain from request
$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];

/*
// check if subdomain registered
$dbApi = DBApi::getInstance();
if ($subdomain != 'apilotus' && $subdomain != 'www') {
	$subdomainId = $dbApi->checkIfSubdomainRegistered($subdomain);
	if ($subdomainId > 0) {
		header("Location: ./admin");
		return;
	} else {
		//header("Location: https://www.apilotus.com");
		//return;
	}
}
*/

$tab_name = "Home";

?>


<!doctype html>
<html>
	<head>
		<title>API Lotus</title>
		<meta charset="utf-8" />
	    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	    <!--
	    <meta name="viewport" content="width=device-width, initial-scale=1" />
		-->

		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Varela+Round" />
	    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	    <link href="../bootstrap/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	    <!--
	    <link href="./bootstrap/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
		-->
	    <link href="../css/limelightcrm.css" rel="stylesheet" type="text/css" />
		<link rel="icon" href="./images/favicon.png" type="image/x-icon">

		<!-- User Heat Tag -->
		<!--
		<script type="text/javascript">
		(function(add, cla){window['UserHeatTag']=cla;window[cla]=window[cla]||function(){(window[cla].q=window[cla].q||[]).push(arguments)},window[cla].l=1*new Date();var ul=document.createElement('script');var tag = document.getElementsByTagName('script')[0];ul.async=1;ul.src=add;tag.parentNode.insertBefore(ul,tag);})('//uh.nakanohito.jp/uhj2/uh.js', '_uhtracker');_uhtracker({id:'uhcm1oxV2t'});
		</script>
		-->
		<!-- End User Heat Tag -->

		<!--
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="https://js.stripe.com/v3/"></script>
	-->
	</head>
	<body style="background-color: white">
		<div id="wrap_body">
			<!--
			<div id="lp_top_banner">
				<div class="lp_content" style="text-align: right; padding-top: 6px">
					<a href="#" target="_blank" style="text-decoration: none; color: #eee; font-weight: bold; font-size: 15px"><span style="color:#999999">contact us at </span>support@apilotus.com</a>
				</div>
			</div>
			-->
			<div id="lp_nav_wrapper">
				<div class="lp_content">
					<div class="row ns">
						<div class="col-xs-3 ns">
							<img src="../images/lp_logo.png" style="height: 55px; width: auto; margin-top: 12px">
						</div>
						<div class="col-xs-9 ns">
							<ul class="nav navbar-nav" style="float: right; vertical-align: middle; padding-top: 15px;">
		                        <li class="scroll active"><a class="lp_tab_label" href="#section1">Home</a></li> 
		                        <li class="scroll"><a class="lp_tab_label" href="#section2">Services</a></li>
		                        <li class="scroll"><a class="lp_tab_label" href="#section3">Who We</a></li>
		                        <li class="scroll"><a class="lp_tab_label" href="#section4">Pricing</a></li>
		                        <li class="scroll"><a class="lp_tab_label" href="#section5">Gallery</a></li>
		                        <li class="scroll"><a class="lp_tab_label" href="#section6">Contact</a></li>
		                        <li class="scroll"><a class="lp_signup_button" href="#" target="_blank">SIGN UP</a></li>
		                    </ul>
							<!--
							<div style="padding-top: 20px">
								<a class="lp_signup_button" href="#" target="_blank">SIGN UP</a>
							</div>
						-->
						</div>
					</div>
				</div>
			</div>
			<section id="section1">
				<div id="lp_slider" style="height: 570px; text-align: center; padding-top: 200px;">
				Top Image
					<!--
					<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
						<ol class="carousel-indicators">
							<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
							<li data-target="#carousel-example-generic" data-slide-to="1"></li>
						</ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
								<img src="../images/lp_carousel1.png" style="width:100%; height: 570px;">
								<div class="carousel-caption">Carousel 1</div>
							</div>
							<div class="item">
								<img src="../images/lp_carousel2.png" style="width:100%; height: 570px;">
								<div class="carousel-caption">Carousel 2</div>
							</div>
						</div>
						<a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
							<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
							<span class="sr-only">Previous</span>
						</a>
						<a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
							<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
							<span class="sr-only">Next</span>
						</a>
					</div>
				-->
				</div>
			</section>
			<section id="section2">
				<div id="lp_detail1" style="padding-bottom: 70px">
					<div class="lp_content" style="text-align: center;">
						<div style="padding-bottom: 20px">
							<h2 style="color: #202020">Full-Stack Visibility and Actionable Insights for Better Software and Better Customer Experiences</h2>
						</div>
						<div style="padding-bottom: 60px">
							<h4 style="color: #202020">Visualize every dimension of the customer experience with contextual insights and interactive applications, and optimize response orchestration, continuous development and delivery.</h4>
						</div>
						<div class="row ns">
							<div class="col-xs-4">
								<div><img src="../images/lp_discover1.png" style="width: 100%; height: auto;"></div>
								<div><h3>Event Intelligence</h3></div>
								<div>Understand the health and common context of disruptions across your entire infrastructure with actionable, time-series visualizations of correlated events.</div>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">LEARN MORE</a></div>
							</div>
							<div class="col-xs-4">
								<div><img src="../images/lp_discover2.png" style="width: 100%; height: auto;"></div>
								<div><h3>Response Orchestration</h3></div>
								<div>All teams get the same visibility for technical and business response orchestration, enabling better collaboration and rapid resolution.</div>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">LEARN MORE</a></div>
							</div>
							<div class="col-xs-4">
								<div><img src="../images/lp_discover3.png" style="width: 100%; height: auto;"></div>
								<div><h3>Continuous Learning and Delivery</h3></div>
								<div>Discover patterns in performance during build and in production for continuous delivery. View post-mortem reports to analyze system efficiency and employee agility.</div>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">LEARN MORE</a></div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<div id="lp_detail2">
			</div>			
			<section id="section3">
				<div id="lp_detail3">
					<div><h2>Get Started in Minutes</h2></div>
					<div style="padding-top: 15px; padding-bottom: 30px">The industry’s largest ecosystem of native integrations with monitoring, collaboration and ticketing tools.</div>
					<div class="row ns">
						<div class="col-xs-2 col-xs-offset-3">
							<img src="../images/lp_sponser1.png">
						</div>
						<div class="col-xs-2">
							<img src="../images/lp_sponser2.png">
						</div>
						<div class="col-xs-2">
							<img src="../images/lp_sponser3.png">
						</div>
					</div>
					<div class="row ns">
						<div class="col-xs-2 col-xs-offset-3">
							<img src="../images/lp_sponser4.png">
						</div>
						<div class="col-xs-2">
							<img src="../images/lp_sponser5.png">
						</div>
						<div class="col-xs-2">
							<img src="../images/lp_sponser6.png">
						</div>
					</div>
				</div>
			</section>
			<section id="section4">
				<div id="lp_detail4">
					<div class="lp_content">
						<div><h2 style="padding-bottom: 70px">Simplifying Incident Resolution for Over 9,500 Organizations</h2></div>
						<div class="row ns">
							<div class="col-xs-4">
								<div class="lp_quote">
									<span>“Uptime is critical and PagerDuty has helped our IT and engineering teams solve technical issues before they get out of hand.”</span>
								</div>
							</div>
							<div class="col-xs-4">
								<div class="lp_quote">
									<span>"PagerDuty connects to your monitoring systems so you can collect events, surface what's important, and resolve critical issues to proactively manage your uptime."</span>
								</div>
							</div>
							<div class="col-xs-4">
								<div class="lp_quote">
									<span>“If we didn't get PagerDuty, we wouldn't have a business.”</span>
								</div>
							</div>
						</div>
						<div class="row ns" style="position: relative; top:-4px">
							<div class="col-xs-2">
								<img src="../images/lp_quote_mark.png">
							</div>
							<div class="col-xs-2 col-xs-offset-2">
								<img src="../images/lp_quote_mark.png">
							</div>
							<div class="col-xs-2 col-xs-offset-2">
								<img src="../images/lp_quote_mark.png">
							</div>
						</div>
						<div style="width: 100px; height: 25px;"></div>
						<div class="row ns" style="text-align: left;">
							<div class="col-xs-4">
								<div style="float: left;">
									<img src="../images/lp_quoter1.png" style="width: 100px; height: auto;">
								</div>
								<div style="float: left;">
									<strong>Hav Mustamandy</strong></br>
									Director of Systems Operations
								</div>
							</div>
							<div class="col-xs-4">
								<div style="float: left;">
									<img src="../images/lp_quoter2.png" style="width: 100px; height: auto;">
								</div>
								<div style="float: left;">
									<strong>Todd McKinnon</strong></br>
									CEO & Co-founder
								</div>
							</div>
							<div class="col-xs-4">
								<div style="float: left;">
									<img src="../images/lp_quoter3.png" style="width: 100px; height: auto;">
								</div>
								<div style="float: left;">
									<strong>Martin Rhoads</strong></br>
									Site Reliability Engineer
								</div>
							</div>
						</div>
					</div>				
				</div>
			</section>
			<section id="section5">
				<div id="lp_detail5">
					<div class="lp_content">
						<div><h2>Get PagerDuty Knowledge</h2></div>
						<div style="padding-top: 10px; padding-bottom: 50px">The latest information, news, and resources on how to keep your operations running.</div>
						<div class="row ns">
							<div class="col-xs-3">
								<img src="../images/lp_guide1.png" style="width: 100%; height: auto;">
							</div>
							<div class="col-xs-3">
								<img src="../images/lp_guide2.png" style="width: 100%; height: auto;">
							</div>
							<div class="col-xs-3">
								<img src="../images/lp_guide3.png" style="width: 100%; height: auto;">
							</div>
							<div class="col-xs-3">
								<img src="../images/lp_guide4.png" style="width: 100%; height: auto;">
							</div>
						</div>
						<div style="width: 100%; height: 20px"></div>
						<div class="row ns">
							<div class="col-xs-3">
								<h4>A Developer’s Guide to Managing Your Code</h4>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">READ MORE</a></div>
							</div>
							<div class="col-xs-3">
								<h4>Discover tried and true best practices for being on-call</h4>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">READ MORE</a></div>
							</div>
							<div class="col-xs-3">
								<h4>State of Digital Operations 2017 Survey Report</h4>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">READ MORE</a></div>
							</div>
							<div class="col-xs-3">
								<h4>The latest trends in DevOps, ITOps, and more!</h4>
								<div style="padding-top: 50px"><a class="lp_learnmore_button" href="#" target="_blank">READ MORE</a></div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<section id="section6">
				<div id="lp_detail6">
					<div class="row ns">
						<div class="col-xs-7" style="text-align: right;">
							<h2><span style="color: #000000;">Start Your <span style="color: #00b9ab;">FREE</span> Trial Today</span></h2>
						</div>
						<div class="col-xs-5" style="text-align: left; padding-top: 25px">
							<a class="lp_signup_button" href="#" target="_blank" style="padding:17px 35px 17px 35px;">SIGN UP</a>
						</div>
					</div>				
				</div>
			</section>
			<div id="lp_footer">
				<span>© 2017 API Lotus Inc. All Rights Reserved.</span>
			</div>
		</div>	<!-- wrap_body -->
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../js/landingpage/index.min.js"></script>
	</body>
</html>