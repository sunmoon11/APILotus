<?php
require_once './daemon/api/DBApi.php';

// get subdomain from request
//$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];

$domain = $_SERVER['HTTP_HOST'];
if ('www.apilotus.com' == $domain) {
    header("Location: https://dash.apilotus.com/");
    return;
}

$subdomain = 'primary';

// check if subdomain registered

$dbApi = DBApi::getInstance();
if ($subdomain != 'apilotus' && $subdomain != 'www') {
	$subdomainId = $dbApi->checkIfSubdomainRegistered($subdomain);
	if ($subdomainId > 0) {
		header("Location: ./admin");
		return;
	} else {
		header("Location: https://www.apilotus.com");
		return;
	}
}

$tab_name = "Home";

?>


<!doctype html>
<html>
	<?php include('./landingpage/header.php'); ?>	
	<body style="background-color: white">
		<?php include('./landingpage/body_up.php'); ?>
			<section id="home">
				<div style="position: relative; width:100%; max-height: 780px; height: auto; overflow: hidden; padding-top: 80px; vertical-align: middle;">
					<img src="./images/lp_home.png" style="width: 100%; height: auto;">
					<div style="position: absolute; width: 100%; height: 100%; left:0; top: 80px;">
						<div class="lp_main_title">ALL-IN-ONE CRM ALERT SYSTEM</div>
					</div>
				</div>
			</section>
			<section id="services">
				<div class="lp_services_bg">
					<div class="lp_content" style="text-align: center;">
						<div style="padding-top: 70px">
							<span style="font-size: 40px; color: #00b9ab">SERVICES</span>
						</div>
						<div style="width: 100px; height: 3px; background-color: #00b9ab; margin: 0 auto"></div>
						<div style="padding-top: 30px; padding-bottom: 60px">
							<span style="font-size: 20px; color: #585858">Automated API Alert System <br><br> Receive automated alerts via E-Mail, SMS, & Telegram, 24/7, 365 days a year.</span>
						</div>
						<div class="row ns">
							<div class="col-xs-4">
								<div class="lp_services_panel">
									<div>
										<img src="./images/lp_services_1.png" style="width: 100%; height: auto;">
									</div>
									<div style="padding-top: 25px; background-color: white">
										<span style="font-size: 23px; color: #00b9ab">CRM<br/>GOAL PROGRESS</span>
									</div>
									<div style="padding: 15px 15px 30px 15px; background-color: white; text-align: left">
										<span style="font-size: 18px; color: #585858;">Automated sales progress every week, never manually pull numbers again.</span>
									</div>
									<!--
									<div style="padding-top: 40px; padding-bottom: 50px; background-color: white">
										<a class="lp_readmore_button" href="./goal_readmore.php">Read More</a>
									</div>
									-->
								</div>
							</div>
							<div class="col-xs-4">
								<div class="lp_services_panel">
									<div>
										<img src="./images/lp_services_2.png" style="width: 100%; height: auto;">
									</div>
									<div style="padding-top: 25px; background-color: white">
                                        <span style="font-size: 23px; color: #00b9ab">24/7 ALERTS<br/><span style="font-size: 19px; color: #00b9ab">(SMS, EMAIL, TELEGRAM)</span></span>
									</div>
									<div style="padding: 15px 15px 30px 15px; background-color: white; text-align: left">
										<span style="font-size: 18px; color: #585858">Automated alerts to your telegram, phone, and email.<br> CRM sales progress, approaching cap goal, rebill alerts, decline percentage alerts, and much more!</span>
									</div>
									<!--
									<div style="padding-top: 40px; padding-bottom: 50px; background-color: white">
										<a class="lp_readmore_button" href="./alert_readmore.php">Read More</a>
									</div>
									-->
								</div>
							</div>
							<div class="col-xs-4">
								<div class="lp_services_panel">
									<div>
										<img src="./images/lp_services_3.png" style="width: 100%; height: auto;">
									</div>
									<div style="padding-top: 25px; background-color: white">
										<span style="font-size: 23px; color: #00b9ab">RETENTION AND<br/>AFFILIATE QUICK PULL</span>
									</div>
									<div style="padding: 15px 15px 30px 15px; background-color: white; text-align: left">
										<span style="font-size: 18px; color: #585858;">Quick access to retention and affiliate view by multiple CRMs.</span>
									</div>
									<!--
									<div style="padding-top: 40px; padding-bottom: 50px; background-color: white">
										<a class="lp_readmore_button" href="./pull_readmore.php">Read More</a>
									</div>
									-->
								</div>
							</div>
						</div>
					</div>
				</div>			
			</section>
			<section id="pricing">
				<div class="lp_pricing_bg">
					<div class="lp_content" style="text-align: center;">
						<div style="padding-top: 70px; padding-bottom: 30px">
							<span style="font-size: 40px; color: #00b9ab">Pricing</span>
						</div>
						<!--
						<div style="width: 100px; height: 3px; background-color: #00b9ab; margin: 0 auto"></div>
						<div style="padding-top: 30px; padding-bottom: 60px">
							<span style="font-size: 20px; color: #585858">15-day free trial. No credit card requried.</span>
						</div>
					-->
						<div class="lp_pricing_panel">
							<div style="padding-top: 25px; background-color: white">
								<span style="font-size: 23px; color: #585858">PREMIUM LEVEL</span>
							</div>
							<div style="padding-top: 10px; background-color: white">
								<span style="font-size: 23px; color: #00b9ab">$ </span>
								<span style="font-size: 60px; color: #00b9ab">497 </span>
								<span style="font-size: 23px; color: #00b9ab">Per Month</span>
							</div>
							<div style="padding-top: 30px; padding-bottom: 50px; background-color: white">
								<a class="lp_readmore_button" href="./pricing.php#">Start Free Trial</a>
							</div>
							<div style="padding: 10px; background-color: #999">
								<span style="font-size: 18px; color: white;">Get these amazing features:</span>
							</div>
							<div style="padding: 15px 10px 0px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">Sales Page</span>
							</div>
							<div style="padding: 10px 10px 0px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">Retention</span>
							</div>
							<div style="padding: 10px 10px 0px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">Campaign Labeling</span>
							</div>
							<div style="padding: 10px 10px 0px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">20 CRM Accounts</span>
							</div>
							<div style="padding: 10px 10px 0px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">5 User Accounts</span>
							</div>
							<div style="padding: 10px 10px 30px 20px; background-color: white">
								<span style="font-size: 18px; color: #585858;">24/7 Alerts (SMS, Email, Telegram)</span>
							</div>
						</div>
					</div>
				</div>
			</section>
<!--				<div class="lp_customers_bg">-->
<!--					<div class="lp_content">-->
<!--						<div class="row ns">-->
<!--							<div class="col-xs-5" style="margin-top: 30px; border-right: 2px solid #cccccc">-->
<!--								<div style="padding-top: 20px"><span style="font-size: 30px; color: white">WHAT OUR</span></div>-->
<!--								<div><span style="font-size: 35px; color: #00b9ab">CUSTOMERS SAY</span></div>-->
<!--								<div style="padding-top: 20px; padding-bottom: 20px"><span style="font-size: 20px; color: white">Lorem Ipsum is simply dummy text of the printing and typesetting industry</span></div>-->
<!--								<div style="padding-top: 30px; padding-bottom: 20px; border-top: 2px solid #cccccc">-->
<!--									<button type="button" class="btn btn-default btn-sm" style="width: 50px"><i class="fa fa-chevron-left" aria-hidden="true" style="font-size: 20px; vertical-align: middle;"></i></button>-->
<!--									<button type="button" class="btn btn-default btn-sm" style="width: 50px; margin-left: 10px"><i class="fa fa-chevron-right" aria-hidden="true" style="font-size: 20px; vertical-align: middle;"></i></button>-->
<!--								</div>-->
<!--							</div>-->
<!--							<div class="col-xs-7" style="padding-left:50px; padding-top: 70px">-->
<!--								<div style="padding-top: 20px; padding-bottom: 20px">-->
<!--									<i class="fa fa-quote-left" aria-hidden="true" style="font-size: 20px; color: #00b9ab"></i>&nbsp;&nbsp;-->
<!--									<span style="font-size: 20px; font-style: italic; color: white">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type.</span>-->
<!--									&nbsp;&nbsp;<i class="fa fa-quote-right" aria-hidden="true" style="font-size: 20px; color: #00b9ab"></i>-->
<!--								</div>-->
<!--								<div style="padding-top: 10px">-->
<!--									<table>-->
<!--										<tbody>-->
<!--											<tr>-->
<!--												<td style="padding-right: 20px"><img src="./images/lp_customer_man.png" style="width: 60px; height: auto;"></td>-->
<!--												<td>-->
<!--													<span style="font-size: 20px; color: #00b9ab">Jonathan Doe</span><br/>-->
<!--													<span style="font-size: 17px; color: #b7b7b7">Project Manager</span>-->
<!--												</td>-->
<!--											</tr>-->
<!--										</tbody>-->
<!--									</table>									-->
<!--								</div>-->
<!--							</div>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
				<div class="lp_feedback_bg">
					<div class="lp_content" style="text-align: center;">
						<div style="padding-top: 70px">
							<span style="font-size: 40px; color: #00b9ab">We’d Love to Hear from You!</span>
						</div>
						<div style="width: 100px; height: 3px; background-color: #00b9ab; margin: 0 auto"></div>
						<div style="padding-top: 30px; padding-bottom: 40px">
							<span style="font-size: 20px; color: #585858">Questions about our products or tools?<br>Contact us in the below box and we will respond within 24-48 hours.</span>
						</div>
						<div class="row ns">
							<div class="col-xs-10" style="text-align: left;"><span id="submit_status"></span></div>
							<div class="col-xs-2 submit_waiting" style="text-align:right"></div>
						</div>
						<div class="row ns">
							<div class="col-xs-6">
								<input type="text" class="form-control client_name" placeholder="Your Name">
							</div>
							<div class="col-xs-6">
								<input type="text" class="form-control client_email" placeholder="Email">
							</div>
						</div>
						<div style="width: 100%; height: 25px"></div>
						<div class="row ns">
							<div class="col-xs-12">
								<textarea class="form-control client_comment" placeholder="Comments" rows="5"></textarea>
							</div>
						</div>
						<div style="padding-top: 60px; padding-bottom: 80px;">
							<a id="submit_now" class="lp_readmore_button" href="javascript:void(0)">Submit Now</a>
						</div>
					</div>
				</div>
<!--			<section id="section3">-->
<!--				<div style="position: relative; width:100%; max-height: 600px; height: auto; overflow: hidden">-->
<!--					<img src="./images/lp_tour_bg.png" style="width: 100%; height: auto;">-->
<!--					<div style="position: absolute; width: 100%; height: 100%; left:0; top: 0;">-->
<!--						<div class="lp_content" style="position: relative; text-align: center;">-->
<!--							<div style="padding-top: 100px"><span style="font-size: 33px; font-weight: bold; color: #2f3f3f">TAKE A</span></div>-->
<!--							<div style="padding-top: 10px"><span style="font-size: 65px; font-weight: bold; color: #2f3f3f">QUICK TOUR</span></div>-->
<!--							<div style="padding-top: 10px"><span style="font-size: 33px; font-weight: bold; color: #2f3f3f">OF OUR FEATURES</span></div>-->
<!--							<div style="padding-top: 100px;">-->
<!--								<a class="lp_readmore_button" href="#" target="_blank">Explorer</a>-->
<!--							</div>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</section>-->
<!--			<section id="section4">-->
<!--				<div class="lp_contactus_bg">-->
<!--					<div class="lp_content" style="text-align: center;">-->
<!--						<div style="padding-top: 70px">-->
<!--							<span style="font-size: 40px; color: #00b9ab">GET IN TOUCH</span>-->
<!--						</div>-->
<!--						<div style="width: 100px; height: 3px; background-color: #00b9ab; margin: 0 auto"></div>-->
<!--						<div style="padding-top: 30px; padding-bottom: 40px">-->
<!--						<span style="font-size: 20px; color: #585858">Google Map</span>-->
<!--					</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</section>-->
		<?php include('./landingpage/body_down.php'); ?>
	</body>
</html>