<html>
	<head>
		<title>Welcome to CodeIgniter</title>

		<link rel="shortcut icon" type="image/png" href="/favicon.ico"/>
	</head>
	<body>

		<style {csp-style-nonce}>
			div.logo {
				height: 200px;
				width: 155px;
				display: inline-block;
				opacity: 0.12;
				position: absolute;
				z-index: 0;
				top: 2rem;
				left: 50%;
				margin-left: -73px;
			}
			body {
				height: 100%;
				background: #fafafa;
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
				color: #777;
				font-weight: 300;
			}
			h1 {
				font-weight: lighter;
				letter-spacing: 0.8rem;
				font-size: 3rem;
				margin-top: 145px;
				margin-bottom: 0;
				color: #222;
				position: relative;
				z-index: 1;
			}
			.wrap {
				max-width: 1024px;
				margin: 5rem auto;
				padding: 2rem;
				background: #fff;
				text-align: center;
				border: 1px solid #efefef;
				border-radius: 0.5rem;
				position: relative;
			}
			.version {
				margin-top: 0;
				color: #999;
			}
			.guide {
				margin-top: 3rem;
				text-align: left;
			}
			pre {
				white-space: normal;
				margin-top: 1.5rem;
			}
			code {
				background: #fafafa;
				border: 1px solid #efefef;
				padding: 0.5rem 1rem;
				border-radius: 5px;
				display: block;
			}
			p {
				margin-top: 1.5rem;
			}
			.footer {
				margin-top: 2rem;
				border-top: 1px solid #efefef;
				padding: 1em 2em 0 2em;
				font-size: 85%;
				color: #999;
			}
			a:active,
			a:link,
			a:visited {
				color: #dd4814;
			}
		</style>

		<div class="wrap">
<?php
$this->session = \Config\Services::session();
//var_dump($this->session->get());
//var_dump($this->session->get('access_token'));
var_dump($list);
?>

<!--			<div>-->
<!--                <input type="text" name="email" value="" placeholder="email">-->
<!--                <input type="text" name="password" value="" placeholder="password">-->
<!--                <button>등록</button>-->
<!--            </div>-->
<!--            <div style="padding: 50px;">-->
<!--                <a href="/home/oauthLogin"><img height="30" src="http://static.nid.naver.com/oauth/small_g_in.PNG"/></a>-->
<!--            </div>-->
<!--            </div>-->
<!---->
<!--			<div class="footer">-->
<!--                <p class="version">version --><?//= CodeIgniter\CodeIgniter::CI_VERSION ?><!--</p>-->
<!--				Page rendered in {elapsed_time} seconds. Environment: --><?//= ENVIRONMENT ?>
<!--			</div>-->
<!--        <a href="/home/naverUnlink">연동해제</a>-->
<!--        <a class='login' href='--><?php //echo $authUrl; ?><!--'><img class='login' src="images/sign-in-with-google.png" width="250px" size="54px" /></a>-->
<!--		</div>-->

	</body>
</html>
