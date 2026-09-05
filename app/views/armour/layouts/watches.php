<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<base href="<?=PATH?>/">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="csrf-token" content="<?=h(\app\services\CsrfProtection::token())?>" />
	<script src="/js/csrf.js" defer></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
	<?php
	$robots = $this->meta['robots'] ?? 'noindex, nofollow';
	if ($this->route['controller'] === 'Product' && !empty($product) && $product->hide === 'lock') {
		$robots = 'noindex, nofollow';
	}
	?>
	<meta name="robots" content="<?=h($robots)?>" />
	<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> 
	<link rel="icon" href="images/favicon.svg" type="image/svg" />
    <link rel="shortcut icon" href="images/favicon.svg" type="image/svg" />
    <?=$this->getMeta(); ?>	


<style id='wp-emoji-styles-inline-css'>

	img.wp-smiley, img.emoji {
		display: inline !important;
		border: none !important;
		box-shadow: none !important;
		height: 1em !important;
		width: 1em !important;
		margin: 0 0.07em !important;
		vertical-align: -0.1em !important;
		background: none !important;
		padding: 0 !important;
	}
</style>
<style id='classic-theme-styles-inline-css'>
/**
 * These rules are needed for backwards compatibility.
 * They should match the button element rules in the base theme.json file.
 */
.wp-block-button__link {
	color: #ffffff;
	background-color: #32373c;
	border-radius: 9999px; /* 100% causes an oval, but any explicit but really high value retains the pill shape. */

	/* This needs a low specificity so it won't override the rules from the button element if defined in theme.json. */
	box-shadow: none;
	text-decoration: none;

	/* The extra 2px are added to size solids the same as the outline versions.*/
	padding: calc(0.667em + 2px) calc(1.333em + 2px);

	font-size: 1.125em;
}

.wp-block-file__button {
	background: #32373c;
	color: #ffffff;
	text-decoration: none;
}

</style>
<style id='global-styles-inline-css'>
body{--wp--preset--color--black: #000000;--wp--preset--color--cyan-bluish-gray: #abb8c3;--wp--preset--color--white: #ffffff;--wp--preset--color--pale-pink: #f78da7;--wp--preset--color--vivid-red: #cf2e2e;--wp--preset--color--luminous-vivid-orange: #ff6900;--wp--preset--color--luminous-vivid-amber: #fcb900;--wp--preset--color--light-green-cyan: #7bdcb5;--wp--preset--color--vivid-green-cyan: #00d084;--wp--preset--color--pale-cyan-blue: #8ed1fc;--wp--preset--color--vivid-cyan-blue: #0693e3;--wp--preset--color--vivid-purple: #9b51e0;--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%);--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(135deg,rgb(122,220,180) 0%,rgb(0,208,130) 100%);--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(135deg,rgba(252,185,0,1) 0%,rgba(255,105,0,1) 100%);--wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(135deg,rgba(255,105,0,1) 0%,rgb(207,46,46) 100%);--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(135deg,rgb(238,238,238) 0%,rgb(169,184,195) 100%);--wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(135deg,rgb(74,234,220) 0%,rgb(151,120,209) 20%,rgb(207,42,186) 40%,rgb(238,44,130) 60%,rgb(251,105,98) 80%,rgb(254,248,76) 100%);--wp--preset--gradient--blush-light-purple: linear-gradient(135deg,rgb(255,206,236) 0%,rgb(152,150,240) 100%);--wp--preset--gradient--blush-bordeaux: linear-gradient(135deg,rgb(254,205,165) 0%,rgb(254,45,45) 50%,rgb(107,0,62) 100%);--wp--preset--gradient--luminous-dusk: linear-gradient(135deg,rgb(255,203,112) 0%,rgb(199,81,192) 50%,rgb(65,88,208) 100%);--wp--preset--gradient--pale-ocean: linear-gradient(135deg,rgb(255,245,203) 0%,rgb(182,227,212) 50%,rgb(51,167,181) 100%);--wp--preset--gradient--electric-grass: linear-gradient(135deg,rgb(202,248,128) 0%,rgb(113,206,126) 100%);--wp--preset--gradient--midnight: linear-gradient(135deg,rgb(2,3,129) 0%,rgb(40,116,252) 100%);--wp--preset--font-size--small: 14px;--wp--preset--font-size--medium: 23px;--wp--preset--font-size--large: 26px;--wp--preset--font-size--x-large: 42px;--wp--preset--font-size--normal: 16px;--wp--preset--font-size--huge: 37px;--wp--preset--font-family--inter: "Inter", sans-serif;--wp--preset--font-family--cardo: Cardo;--wp--preset--spacing--20: 0.44rem;--wp--preset--spacing--30: 0.67rem;--wp--preset--spacing--40: 1rem;--wp--preset--spacing--50: 1.5rem;--wp--preset--spacing--60: 2.25rem;--wp--preset--spacing--70: 3.38rem;--wp--preset--spacing--80: 5.06rem;--wp--preset--shadow--natural: 6px 6px 9px rgba(0, 0, 0, 0.2);--wp--preset--shadow--deep: 12px 12px 50px rgba(0, 0, 0, 0.4);--wp--preset--shadow--sharp: 6px 6px 0px rgba(0, 0, 0, 0.2);--wp--preset--shadow--outlined: 6px 6px 0px -3px rgba(255, 255, 255, 1), 6px 6px rgba(0, 0, 0, 1);--wp--preset--shadow--crisp: 6px 6px 0px rgba(0, 0, 0, 1);}:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}body .is-layout-flex{display: flex;}body .is-layout-flex{flex-wrap: wrap;align-items: center;}body .is-layout-flex > *{margin: 0;}body .is-layout-grid{display: grid;}body .is-layout-grid > *{margin: 0;}:where(.wp-block-columns.is-layout-flex){gap: 2em;}:where(.wp-block-columns.is-layout-grid){gap: 2em;}:where(.wp-block-post-template.is-layout-flex){gap: 1.25em;}:where(.wp-block-post-template.is-layout-grid){gap: 1.25em;}.has-black-color{color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-color{color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-color{color: var(--wp--preset--color--white) !important;}.has-pale-pink-color{color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-color{color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-color{color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-color{color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-color{color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-color{color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-color{color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-color{color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-color{color: var(--wp--preset--color--vivid-purple) !important;}.has-black-background-color{background-color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-background-color{background-color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-background-color{background-color: var(--wp--preset--color--white) !important;}.has-pale-pink-background-color{background-color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-background-color{background-color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-background-color{background-color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-background-color{background-color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-background-color{background-color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-background-color{background-color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-background-color{background-color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-background-color{background-color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-background-color{background-color: var(--wp--preset--color--vivid-purple) !important;}.has-black-border-color{border-color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-border-color{border-color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-border-color{border-color: var(--wp--preset--color--white) !important;}.has-pale-pink-border-color{border-color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-border-color{border-color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-border-color{border-color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-border-color{border-color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-border-color{border-color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-border-color{border-color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-border-color{border-color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-border-color{border-color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-border-color{border-color: var(--wp--preset--color--vivid-purple) !important;}.has-vivid-cyan-blue-to-vivid-purple-gradient-background{background: var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple) !important;}.has-light-green-cyan-to-vivid-green-cyan-gradient-background{background: var(--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan) !important;}.has-luminous-vivid-amber-to-luminous-vivid-orange-gradient-background{background: var(--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange) !important;}.has-luminous-vivid-orange-to-vivid-red-gradient-background{background: var(--wp--preset--gradient--luminous-vivid-orange-to-vivid-red) !important;}.has-very-light-gray-to-cyan-bluish-gray-gradient-background{background: var(--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray) !important;}.has-cool-to-warm-spectrum-gradient-background{background: var(--wp--preset--gradient--cool-to-warm-spectrum) !important;}.has-blush-light-purple-gradient-background{background: var(--wp--preset--gradient--blush-light-purple) !important;}.has-blush-bordeaux-gradient-background{background: var(--wp--preset--gradient--blush-bordeaux) !important;}.has-luminous-dusk-gradient-background{background: var(--wp--preset--gradient--luminous-dusk) !important;}.has-pale-ocean-gradient-background{background: var(--wp--preset--gradient--pale-ocean) !important;}.has-electric-grass-gradient-background{background: var(--wp--preset--gradient--electric-grass) !important;}.has-midnight-gradient-background{background: var(--wp--preset--gradient--midnight) !important;}.has-small-font-size{font-size: var(--wp--preset--font-size--small) !important;}.has-medium-font-size{font-size: var(--wp--preset--font-size--medium) !important;}.has-large-font-size{font-size: var(--wp--preset--font-size--large) !important;}.has-x-large-font-size{font-size: var(--wp--preset--font-size--x-large) !important;}
.wp-block-navigation a:where(:not(.wp-element-button)){color: inherit;}
:where(.wp-block-post-template.is-layout-flex){gap: 1.25em;}:where(.wp-block-post-template.is-layout-grid){gap: 1.25em;}
:where(.wp-block-columns.is-layout-flex){gap: 2em;}:where(.wp-block-columns.is-layout-grid){gap: 2em;}
.wp-block-pullquote{font-size: 1.5em;line-height: 1.6;}
</style>
<link rel='stylesheet' id='contact-form-7-css' href='https://advanta-ekb.ru/wp-content/plugins/contact-form-7/includes/css/styles.css?ver=5.9.6' media='all' />
<link rel='stylesheet' id='wpa-css-css' href='https://advanta-ekb.ru/wp-content/plugins/honeypot/includes/css/wpa.css?ver=2.2.02' media='all' />
<style id='woocommerce-inline-inline-css'>
.woocommerce form .form-row .required { visibility: visible; }
</style>
<link rel="stylesheet" href="css/armour/bootstrap.css" />
<link rel="stylesheet" href="public/adminlte/plugins/fontawesome-free/css/all.min.css" />
<link rel='stylesheet' id='storefront-style-css' href='css/armour/style.css' media='all' />
<link rel='stylesheet' id='apphlp-css' href='css/armour/apphlp.css' media='all' />
<link rel='stylesheet' id='libs-style-css' href='css/armour/libs.min.css' media='all' />
<link rel='stylesheet' id='app-style-css' href='css/armour/style.min.css' media='all' />
<link rel='stylesheet' id='mobile-fixes-css' href='/css/armour/mobile-fixes.css?v=<?=filemtime(WWW.'/css/armour/mobile-fixes.css')?>' media='all' />
<link rel="stylesheet" href="public/adminlte/plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="public/adminlte/plugins/select2-bootstrap5-theme/select2-bootstrap-5-theme.min.css" />

<meta name="generator" content="armour-shina.ru">
<style>
                .lmp_load_more_button.br_lmp_button_settings .lmp_button:hover {
                    background-color: #ffffff!important;
                    color: #009900!important;
                }
                .lmp_load_more_button.br_lmp_prev_settings .lmp_button:hover {
                    background-color: #9999ff!important;
                    color: #111111!important;
                }li.product.lazy, .berocket_lgv_additional_data.lazy{opacity:0;}
				
</style>
<noscript><style>.woocommerce-product-gallery{ opacity: 1 !important; }</style></noscript>
	
<style id='wp-fonts-local'>
@font-face{font-family:Inter;font-style:normal;font-weight:300 900;font-display:fallback;src:url('https://advanta-ekb.ru/wp-content/plugins/woocommerce/assets/fonts/Inter-VariableFont_slnt,wght.woff2') format('woff2');font-stretch:normal;}
@font-face{font-family:Cardo;font-style:normal;font-weight:400;font-display:fallback;src:url('https://advanta-ekb.ru/wp-content/plugins/woocommerce/assets/fonts/cardo_normal_400.woff2') format('woff2');}
</style>

<style type='text/css'>
		@font-face{
			font-family:icons;
			src:url(/wp-content/themes/advanta/dist/fonts/icons.eot?0.4.30);
			src:url(/wp-content/themes/advanta/dist/fonts/icons.eot?#iefix) format("embedded-opentype"),
			url(/wp-content/themes/advanta/dist/fonts/icons.woff?0.4.30) format("woff"),
			url(/wp-content/themes/advanta/dist/fonts/icons.ttf?0.4.30) format("truetype");
			font-weight:400;
			font-style:normal;
			font-display:swap;
		}
    </style>
<script>
	window.onload = function() {
		let preloader = document.getElementById('preloader');
		preloader.classList.add('hide-preloader');
		setInterval(function() {
			  preloader.classList.add('preloader-hidden');
		}, 990);
	}
</script>
<script src="js/imask.min.js"></script><!-- telefon -->
</head>
<body class="woocommerce-active">
	<div class="preloader" id="preloader"><img src="images/ring.svg" width="150" height="150" alt=""></div>
	<div id="page" class="hfeed site">    
		<header id="masthead" class="site-header prochie-tovary" role="banner" style="">
			<div class="col-full flex">
				<div class="site-branding">
					<a href="/" class="custom-logo-link" rel="home"><img src="/images/logo_armour.png" class="custom-logo" alt="Шины для спецтехники" data-no-lazy="1" decoding="async" /></a>
				</div>
				<div class="site-search">
					<div class="dgwt-wcas-search-wrapp dgwt-wcas-has-submit woocommerce dgwt-wcas-style-solaris js-dgwt-wcas-layout-classic dgwt-wcas-layout-classic js-dgwt-wcas-mobile-overlay-enabled dgwt-wcas-active" style="position: relative;">
						<form class="dgwt-wcas-search-form searchform" role="search" method="get"  action="search" >
							<div class="dgwt-wcas-sf-wrapp">
								<label class="screen-reader-text" for="typeahead">Поиск товаров</label>
								<input id="typeahead" type="search" class="dgwt-wcas-search-input typeahead" name="s" value="" placeholder="Поиск по названию или артикулу товара" autocomplete="off">
								
								<button type="submit" aria-label="Поиск" class="dgwt-wcas-search-submit hidden">
									<svg class="dgwt-wcas-ico-magnifier" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 51.539 51.361" xml:space="preserve">
										<path d="M51.539,49.356L37.247,35.065c3.273-3.74,5.272-8.623,5.272-13.983c0-11.742-9.518-21.26-21.26-21.26 S0,9.339,0,21.082s9.518,21.26,21.26,21.26c5.361,0,10.244-1.999,13.983-5.272l14.292,14.292L51.539,49.356z M2.835,21.082 c0-10.176,8.249-18.425,18.425-18.425s18.425,8.249,18.425,18.425S31.436,39.507,21.26,39.507S2.835,31.258,2.835,21.082z"></path>
									</svg>
								</button>
							</div>
						</form>
						<div class="dgwt-wcas-suggestions-wrapp woocommerce dgwt-wcas-has-img dgwt-wcas-has-price dgwt-wcas-has-sku search_form_helper hidden" ></div>
					</div>
				</div>
				<div class="widget_text site-phone">
					<div class="textwidget custom-html-widget">
						<div class="grid decstop all-page">
							<div class="width-1-2">
								<p><a href="#" class="btn btn-call show_form" rel="form2">Обратный звонок</a>
								</p>
								<p><a href="#" class="btn btn-send show_form" rel="form3">Написать сообщение</a></p>
							</div>
							<div class="width-1-2 position-relative site-phone__number">
								<p class="font-14">
									
									<a class="contact-link" href="tel:+79250707707">+7(925) <span>070 77 07</span></a><br>
									<a class="contact-link" href="tel:+79250707707">+7(903) <span>540 60 60</span></a>
								</p>
							</div>
						</div>
						<div class="grid mobile all-page">
							<div class="position-relative site-phone__number">
								<p><a href="#" class="btn btn-call show_form" rel="form2" aria-label="Заказать обратный звонок"><i class="fas fa-phone-alt" aria-hidden="true"></i></a></p>
								<p><a href="#" class="btn btn-send show_form" rel="form3" aria-label="Написать сообщение"><i class="fas fa-envelope" aria-hidden="true"></i></a></p>
								<p class="font-14"><button type="button" class="mobile-phone-toggle" aria-label="Показать номер телефона" aria-expanded="false"><i class="fas fa-chevron-down" aria-hidden="true"></i></button></p>
								<div class="hide-phone">
									<p class="font-14"><i class="fas fa-phone-alt" aria-hidden="true"></i><a href="tel:+79250707707">+7(925) <span>070 77 07</span><br><i>многоканальный</i></a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
</div><div class="storefront-primary-navigation"><div class="col-full"><div class="header__layout"></div>
<div class="menu-wrapper container-wrapper">
    <nav class="menu-catalog"><ul id="catalog-menu" class="menu-list"><li id="menu-item-24639" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-24639 menu-item"><a>Каталог</a>
<ul class="sub-menu sub-menu--level_0">
	<li id="menu-item-24640" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24640 menu-item"><a href="/industrialnye-shiny">Индустриальные шины</a>
		<ul class="sub-menu sub-menu--level_1">
			<li id="menu-item-24642" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24642 menu-item">
				<a href="/catalog-vil">Шины для вилочных погрузчиков</a><i class="fas fa-chevron-down"></i>
				<ul class="sub-menu sub-menu--level_2">
					<li id="menu-item-24643" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24643 menu-item"><a href="/pnevmaticheskie-shiny-dlya-vilochnyh-pogruzchikov">Пневматические шины для вилочных погрузчиков</a></li>
					<li id="menu-item-24644" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24644 menu-item"><a href="/celnolitye-shiny-dlya-vilochnyh-pogruzchikov">Цельнолитые шины для вилочных погрузчиков</a></li>
				</ul>
			</li>
			<li id="menu-item-24654" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24654 menu-item">
				<a href="/catalog-mini">Шины для минипогрузчиков</a><i class="fas fa-chevron-down"></i>
				<ul class="sub-menu sub-menu--level_2">
					<li id="menu-item-24655" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24655 menu-item"><a href="/pnevmaticheskie-shiny-dlya-mini-pogruzchika">Пневматические шины для мини погрузчика</a></li>
					<li id="menu-item-24656" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24656 menu-item"><a href="/celnolitye-shiny-dlya-minipogruzchika">Цельнолитые шины для минипогрузчика</a></li>
				</ul>
			</li>
			<li id="menu-item-24653" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24653 menu-item"><a href="/shiny-dlya-ekskavatora-pogruzchika">Шины для экскаватора-погрузчика</a></li>
			<li id="menu-item-24840" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24840 menu-item">
				<a href="/catalog-front">Шины для фронтальных погрузчиков</a><i class="fas fa-chevron-down"></i>
				<ul class="sub-menu sub-menu--level_2">
					<li id="menu-item-24841" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24841 menu-item"><a href="/diagonalnye-shiny-dlya-frontalnyh-pogruzchikov">Диагональные шины для фронтальных погрузчиков</a></li>
					<li id="menu-item-24842" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24842 menu-item"><a href="/radialnye-shiny-dlya-frontalnyh-pogruzchikov">Радиальные шины для фронтальных погрузчиков</a></li>
					<li id="menu-item-24843" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24843 menu-item"><a href="/celnolitye-shiny-dlya-frontalnyh-pogruzchikov">Цельнолитые шины для фронтальных погрузчиков</a></li>
				</ul>
			</li>
			<li id="menu-item-24838" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24838 menu-item"><a href="/shiny-dlya-ekskavatorov">Шины для экскаваторов</a></li>			
			<li id="menu-item-24846" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24846 menu-item"><a href="/catalog-katok">Шины для асфальтоукладчиков и катков</a></li>
			<li id="menu-item-24667" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24667 menu-item"><a href="/catalog-greyder">Шины для грейдеров</a></li>
			<li id="menu-item-24852" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24852 menu-item"><a href="/catalog-kran">Шины для крановой техники</a></li>
			<li id="menu-item-24669" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24669 menu-item"><a href="/catalog-shahta">Шины для шахтной техники</a></li>
			<li id="menu-item-24855" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24855 menu-item"><a href="/shiny-dlya-karernyh-samosvalov">Шины для карьерных самосвалов</a></li>
		</ul>
	</li>
	<li id="menu-item-24673" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24673 menu-item"><a href="/filtra">Фильтры для погрузчиков</a>
		<ul class="sub-menu sub-menu--level_1">
			<li id="menu-item-24674" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24674 menu-item"><a href="/filtry-vozdushnye">Фильтры воздушные</a></li>
			<li id="menu-item-24676" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24676 menu-item"><a href="/filtry-maslyanye">Фильтры масляные</a></li>
			<li id="menu-item-24862" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24862 menu-item"><a href="/filtry-gidravlicheskie">Фильтры гидравлические</a></li>
			<li id="menu-item-24863" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24863 menu-item"><a href="/filtry-toplivnye">Фильтры топливные</a></li>
			<li id="menu-item-24864" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24864 menu-item"><a href="/filtry-kabiny">Фильтры кабины</a></li>
			<li id="menu-item-24862" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24862 menu-item"><a href="/filtry-ohlazhdayuschey-zhidkosti">Фильтры охлаждающей жидкости</a></li>
			<li id="menu-item-24863" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24863 menu-item"><a href="/filtry-sapuna">Фильтры сапуна</a></li>
			<li id="menu-item-24864" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24864 menu-item"><a href="/filtry-osushiteli">Фильтры осушители</a></li>
		</ul>
	</li>
	<li id="menu-item-24687" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24687 menu-item"><a href="/catalog-disk">Диски для погрузчиков</a>
	<ul class="sub-menu sub-menu--level_1">
		<li id="menu-item-24688" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24688 menu-item"><a href="/catalog-sbornie">Сборные диски для вилочных погрузчиков</a></li>
		<li id="menu-item-24689" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24689 menu-item"><a href="/svarnye-diski-dlya-vilochnyh-pogruzchikov">Сварные диски для вилочных погрузчиков</a></li>
		<li id="menu-item-24690" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24690 menu-item"><a href="/catalog-svarnie">Сварные диски для минипогрузчика</a></li>
		<li id="menu-item-24691" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24691 menu-item"><a href="/svarnye-diski-na-pogruzchiki-new-holland">Сварные диски на погрузчик NEW HOLLAND</a></li>
		<li id="menu-item-24692" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24692 menu-item"><a href="/svarnye-diski-na-ekskavator-pogruzchik-jcb">Сварные диски на экскаватор-погрузчик JCB</a></li>
		<li id="menu-item-24693" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24693 menu-item"><a href="/svarnye-diski-na-ekskavator-pogruzchik-terex">Сварные диски на экскаватор-погрузчик Terex</a></li>
		<li id="menu-item-24694" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24694 menu-item"><a href="/diski-na-frontalnyj-pogruzchik">Диски на фронтальный погрузчик</a></li>
	</ul>
	</li>
	<li id="menu-item-24700" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-has-children menu-item-24700 menu-item"><span>Камеры и ободные ленты</span>
	<ul class="sub-menu sub-menu--level_1">
		<li id="menu-item-24866" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24866 menu-item"><a href="/kamery-dlya-spectehniki">Камеры для спецтехники</a></li>
		<li id="menu-item-24869" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-24869 menu-item"><a href="/obondnye-lenty-flipper">Ободные ленты (Флиппер)</a></li>
	</ul>
	</li>
</ul>
</li>
</ul></nav>    <nav class="menu-primary"><ul id="primary-menu" class="menu-list"><li id="menu-item-5891" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5891 menu-item"><a href="/actions">Акции</a></li>
<li id="menu-item-5892" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5892 menu-item"><a href="/dostavka">Доставка и оплата</a></li>
<li id="menu-item-9353" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9353 menu-item"><a href="/comp">О компании</a></li>
<li id="menu-item-14813" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-14813 menu-item"><a href="/info">Полезная информация</a>
<ul class="sub-menu sub-menu--level_0">
	<li id="menu-item-14857" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-14857 menu-item"><a href="/articles">Каталог статей</a></li>
	<li id="menu-item-21999" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-21999 menu-item"><a href="/news">Новости</a></li>
</ul>
</li>
<li id="menu-item-5895" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5895 menu-item"><a href="/contacts">Контакты</a></li>
</ul></nav>    <div class="menu-block menu-block--empty"></div>
</div>
<button type="button" class="header-menu-mobile" aria-label="Открыть каталог" aria-controls="mobile-site-menu" aria-expanded="false"><i class="fas fa-bars" aria-hidden="true"></i></button>


<!--<div class="menu-wrapper">
    </div>-->
	<div class="right-panel flex flex-align-center">
		<a href="/comparison" class="compare-link font-14">
			<span class="count flex-align-center flex-center">0</span> <span class="header-compare-word">Сравнение</span>
		</a>
		<a href="user/bookmarks" class="icon-wish menu-buttons__link menu-buttons__link-wish wish-btn-menu off" title="Избранное">
			<span class="menu-buttons__count menu-buttons__wish-count wish-btn__count">0</span>
			<span class="menu-buttons__wish-title">Избранное</span>
		</a>
		<ul id="site-header-cart" class="site-header-cart menu font-14 flex flex-align-center flex-center site-header-cart--small-count">
			<li class="">
				<a class="cart-contents" href="cart" title="Просмотрите свою корзину покупок">
					<span class="cart-icon-count position-relative">
											
					</span>
					<span id="cart-total" class="header-cart-count count flex-align-center flex-center simpleCart_qty">
						<?php if(!empty($_SESSION['cart'])): ?>								
							<?=$_SESSION['cart.qty']?>							
						<?php else: ?>
							0
						<?php endif; ?>
					</span>
					<span class="header-cart-word">Корзина</span>
				</a>
			</li>
		</ul>
    </div>
</div>

</div>    
</header>
			
<!-- #content -->
<div class="content">
    <div class="container">
        <div class="row">			
            <div class="col-md-12">
				<noindex>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
				<?php 
					$informations = \ishop\App::options('option_informations');				
					if($informations):
				?>
                    <div class="alert alert-danger">
                        <?=$informations?>
                    </div>
                <?php endif; ?>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
				</noindex>
            </div>
        </div>
    </div>

	<?php /*debug($main_title);*/ if(!empty($main_title[2])) { echo "".$main_title[2].""; } ?>
    <?php if(!empty($content)) { echo $content; } ?>
</div>

<!-- #footer -->
<div id="footer_site_map" class="widget_text site-map position-relative">
    <div class="textwidget custom-html-widget">
                <div class="site-map__block font-14 hidden-phone fadeout">
            <p class="text-uppercase text-bold font-14">ООО «ИТС-Центр»</p>
            <p>Московская область, г.Подольск, микрорайон Климовск, Коммунальная улица, 26</p>
                    <div class="grid site-map__phone">
                                                            <div class="width-1-2">
                            <div>
								<a class="footer-contacts-phone" href="tel:+79250707707">+7(925) <span>070 77 07</span></a><br>
								<a class="footer-contacts-phone" href="tel:+79035406060">+7(903) <span>540 60 60</span></a>
							</div>
                        </div>
                                                </div>
        </div>
    </div>
</div>
<div class="site-contacts">
    <div class="widget_text col-full">
        <div class="textwidget custom-html-widget">
            <div class="grid grid-small">
                <div class="width-1-4">
                    <p class="text-bold font-15 footer-caption">Каталог продукции</p>
					<div>
						<a class="contact-link" href="/industrialnye-shiny">Индустриальные шины</a><br />
						<a class="contact-link" href="/catalog-kvadrotciklov">Шины для квадроциклов</a><br />
						<a class="contact-link" href="/filtra">Фильтры для погрузчиков</a><br />
						<a class="contact-link" href="/catalog-disk">Диски для погрузчиков</a><br />
						<a class="contact-link" href="/kamery-i-obodnye-lenty">Камеры и ободные ленты</a>
					</div>
                </div>
				<div class="width-1-4">
                    <p class="text-bold font-15 footer-caption">Покупателям</p>
					<div>
						<a class="contact-link" href="/actions">Акции и скидки</a><br />
						<a class="contact-link" href="/dostavka">Доставка</a><br />
						<a class="contact-link" href="/comp">О компании</a><br />
						<a class="contact-link" href="/news">Новости</a><br />
						<a class="contact-link" href="/contacts">Контакты</a>
					</div>
                </div>
				<div class="width-1-4 text-top">
                    <p class="text-bold font-15 footer-caption">Полезная информация</p>
					<div>
						<a class="contact-link" href="/articles">Каталог статей</a><br />
						<a class="contact-link" href="/sitemap">Карта сайта</a><br />
					</div>
                </div>
				<div class="width-1-4 text-top">
                   <div class="widget_text widget widget_custom_html">
						<div class="textwidget custom-html-widget">
							<p class="text-bold font-15 footer-caption">Присоединяйтесь к нам в соцсетях</p>
							<div class="socimg">
								<a target="_blank" href="https://vk.com/armourshina" title="ВКонтакте" rel="nofollow"><i class="fab fa-vk" style="font-size:24px;line-height: 1; color: var(--white);"></i></a>
								<a target="_blank" href="https://ok.ru/armourshina" title="Одноклассники" rel="nofollow"><i class="fab fa-odnoklassniki-square" style="font-size:24px;line-height: 1; color: var(--white);"></i></a>
							</div>
						</div>
					</div>
                </div>                
            </div>
        </div>
    </div>
</div>
<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="col-full">
        <div class="footer-widgets row fix">
			<div class="block footer-widget-1 col-3">
				<div class="widget_text widget widget_custom_html">
					<div class="textwidget custom-html-widget">
						<p class="text-bold font-15 footer-caption">Режим работы</p>
						<div class="font-15"><i class="icon icon-office"></i><span class="contact-link">Офис: Пн-Пт: 09:00-17:00</span></div>
						<div class="font-15"><i class="icon icon-warehouse"></i><span class="contact-link">Склад: Пн-Пт: 09:00-17:00<br><i>(отгрузка товара)</i></span></div>
					</div>
				</div>
			</div>
			<div class="block footer-widget-2 col-3">
				<p class="text-bold font-15 footer-caption">Контактные телефоны</p>
					<div>
						<a class="footer-contacts-phone contact-link" href="tel:+79250707707">+7(925) <span>070 77 07</span></a><br />
						<a class="footer-contacts-phone contact-link" href="tel:+79035406060">+7(903) <span>540 60 60</span></a>
					</div>
				
			</div>
			<div class="block footer-widget-3 col-3">
				<div class="widget_text widget widget_custom_html">
					<p class="text-bold font-15 footer-caption">Остались вопросы?</p>
					<div>
						<i class="icon icon-mail-white"></i><a href="mailto:info@armour-shina.ru">info@armour-shina.ru</a>
					</div>
					<div class="text-center">
						<a href="#" class="text-uppercase font-14 btn btn-border btn-border-white btn-send show_form" rel="form3">Задать вопрос</a>
					</div>        
				</div>
			</div>
			<div class="block footer-widget-4 col-3">
				<div class="widget_text widget widget_custom_html">
					<div class="textwidget custom-html-widget">
						<p>© ООО "ИТС-Центр" г. Подольск<br>2010-2024 г.</p>
						<p>ОГРН 1105074000096</p>
						<p><a href="/o-kompanii/politika-konfidentsialnosti">Политика конфиденциальности</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>
</div>

<div id="wpcf7-zakazat-zvonok-modal" rel="form2" class="black wpcf7-zakazat-zvonok-modal modal" style="display: none;">
    <div class="big_box_close"></div>
	<div class="form_box" style="top: 201px;">
		<a class="a_close_box" rel="form2">x</a>
		<div class="form_title">Закажите обратный звонок</div>
		<div class="form_form">                
			<div class="js wpcf7-inited" id="wpcf7-f9770-o2" lang="en-US" dir="ltr" style="padding-bottom: 0px;">
				<form action="/callback" method="post" class="wpcf7-form init wpcf7-lazy-load-form wpcf7-zakazat-zvonok" data-toggle="validator" novalidate="true">										
					<div>
						
							<span class="wpcf7-form-control-wrap">
								<input id="phone-input" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш телефон*" value="" type="tel" name="phone">
							</span>
						
					</div>					
					<span class="wpcf7-form-control-wrap">
						<span class="wpcf7-form-control wpcf7-acceptance">
							<span class="wpcf7-list-item">
								<label>
									<input type="checkbox" name="accept-this-3" value="1" class="check-val">
									<span class="wpcf7-list-item-label">Вы соглашаетесь на обработку персональных данных</span>
								</label>
							</span>
						</span>
					</span>
					<input class="wpcf7-form-control wpcf7-submit has-spinner btn-send" type="submit" value="Отправить" disabled="">
				</form>
			</div>
		</div>
	</div>
</div>
<div id="wpcf7-napishite-nam-modal" rel="form3" class="black wpcf7-napishite-nam-modal modal" style="display: none;">
    <div class="big_box_close"></div>
    <div class="form_box">
         <a class="a_close_box" rel="form3">x</a>
         <div class="form_title">Напишите нам</div>
         <div class="form_form">                
			<div class="wpcf7 no-js" id="wpcf7-f9816-o3" lang="en-US" dir="ltr">
				<form action="/sendmail" method="post" class="wpcf7-form init wpcf7-lazy-load-form wpcf7-napishite-nam" aria-label="Contact form" novalidate="novalidate" data-status="init">
					<div class="flex two-input">
						<div>
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Ваше имя*" value="" type="text" name="name" />
							</span>
						</div>
						<div>
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш E-mail*" value="" type="email" name="uemail" />
							</span>
						</div>
					</div>
					<div class="flex two-input">
						<div>
							<span class="wpcf7-form-control-wrap">
								<input id="phone-input" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш телефон*" value="" type="tel" name="tell_modal">
							</span>
						</div>
					</div>
					<span class="wpcf7-form-control-wrap">
						<textarea cols="40" rows="10" maxlength="2000" class="wpcf7-form-control wpcf7-textarea form-control" aria-invalid="false" placeholder="Ваше сообщение" name="note"></textarea>
					</span>
					<span class="wpcf7-form-control-wrap">
						<span class="wpcf7-form-control wpcf7-acceptance">
							<span class="wpcf7-list-item">
								<label>
									<input type="checkbox" name="accept-this-3" value="1" class="check-val" />
									<span class="wpcf7-list-item-label">Вы соглашаетесь на обработку персональных данных</span>
								</label>
							</span>
						</span>
					</span>
					<input class="wpcf7-form-control wpcf7-submit has-spinner btn-send" type="submit" value="Отправить" disabled="" />
				</form>
			</div>
		</div>
	</div>
</div>
<div id="wpcf7-zakazat-v-odin-klik-modal" rel="form4" class="black wpcf7-zakazat-v-odin-klik-modal modal" style="display: none;">
	<div class="big_box_close"></div>
	<div class="form_box" style="top: 112px;">
		<a class="a_close_box" rel="form4">x</a>
		<div class="form_title">Заказать в один клик</div>
		<div class="form_form">                
			<div class="js wpcf7-inited">
				<div class="screen-reader-response">
					<p role="status" aria-live="polite" aria-atomic="true"></p>
				</div>
				<form action="/oneclick" method="post" class="wpcf7-form init wpcf7-lazy-load-form wpcf7-zakazat-v-odin-klik" aria-label="Contact form" enctype="multipart/form-data" novalidate="novalidate" data-status="init">
					<span class="wpcf7-form-control-wrap">
						<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" readonly="readonly" value="<?=$product->name?>" type="text" name="name_tovar">
						<input type="hidden" name="product_id" value="<?=$product->id?>">
						<input type="hidden" name="user_id" value="<?=$_SESSION['user']['id'];?>">
					</span>			
					<div class="flex two-input">
						<div>					
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Ваше имя*" value="" type="text" name="fio_modal">
							</span>					
						</div>
						<div>					
							<span class="wpcf7-form-control-wrap">
								<input id="phone-input" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш телефон*" value="" type="tel" name="tell_modal">
							</span>					
						</div>
					</div>
					<div class="flex two-input">
						<div>
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш E-mail*" value="" type="email" name="email_modal" />
							</span>
						</div>
					</div>			
					<span class="wpcf7-form-control-wrap" data-name="your-message">
						<textarea cols="40" rows="10" maxlength="2000" class="wpcf7-form-control wpcf7-textarea" aria-invalid="false" placeholder="Ваше сообщение" name="prim_modal"></textarea>
					</span>			
					<span class="wpcf7-form-control-wrap" data-name="accept-this-3">
						<span class="wpcf7-form-control wpcf7-acceptance">
							<span class="wpcf7-list-item">
								<label>
									<input type="checkbox" name="accept-this-3" value="1" class="check-val">
									<span class="wpcf7-list-item-label">Вы соглашаетесь на обработку персональных данных</span>
								</label>
							</span>
						</span>
					</span>
					<input class="wpcf7-form-control wpcf7-submit has-spinner btn-send" type="submit" value="Отправить" disabled="" />
				</form>
			</div>
		</div>
	</div>
</div>
<div id="wpcf7-zakazat-v-odin-klik-modal" rel="form5" class="black wpcf7-zakazat-v-odin-klik-modal modal" style="display: none;">
	<div class="big_box_close"></div>
	<div class="form_box" style="top: 112px;">
		<a class="a_close_box" rel="form5">x</a>
		<div class="form_title">Запросить счёт на оплату</div>
		<div class="form_form">                
			<div class="js wpcf7-inited">
				<div class="screen-reader-response">
					<p role="status" aria-live="polite" aria-atomic="true"></p>
				</div>
				<form action="/zchet" method="post" class="wpcf7-form init wpcf7-lazy-load-form wpcf7-zakazat-v-odin-klik" aria-label="Contact form" enctype="multipart/form-data" novalidate="novalidate" data-status="init">
					<span class="wpcf7-form-control-wrap">
						<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" readonly="readonly" value="<?=$product->name?>" type="text" name="name_tovar">
						<input type="hidden" name="product_id" value="<?=$product->id?>">
						<input type="hidden" name="user_id" value="<?=$_SESSION['user']['id'];?>">
					</span>			
					<div class="flex two-input">
						<div>					
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Ваше имя*" value="" type="text" name="fio_modal">
							</span>					
						</div>
						<div>					
							<span class="wpcf7-form-control-wrap">
								<input id="phone-input" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш телефон*" value="" type="tel" name="tell_modal">
							</span>					
						</div>
					</div>
					<div class="flex two-input">
						<div>
							<span class="wpcf7-form-control-wrap">
								<input size="40" maxlength="400" class="wpcf7-form-control wpcf7-tel wpcf7-validates-as-required wpcf7-text wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" placeholder="Ваш E-mail*" value="" type="email" name="email_modal" />
							</span>
						</div>
					</div>			
					<span class="wpcf7-form-control-wrap" data-name="your-message">
						<textarea cols="40" rows="10" maxlength="2000" class="wpcf7-form-control wpcf7-textarea" aria-invalid="false" placeholder="Ваше сообщение" name="prim_modal"></textarea>
					</span>
					<div class="flex two-input">
						<table class="zakaz-data" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr class="notauth">
									<td class="zakaz-txt"><font color="red">*</font> Физ. лицо</td>
									<td class="zakaz-inpt2"><label><input type="radio" name="face" value="None" aria-required="true" onchange="Selected(this)"></label>
									<input type="radio" checked="" name="zap_nds" value="без НДС" required="required" style="display:none;">
									</td>
								</tr>
								<tr class="notauth">
									<td class="zakaz-txt"><font color="red">*</font> Юр. лицо</td>
									<td class="zakaz-inpt2"><label><input type="radio" name="face" value="Open" aria-required="true" onchange="Selected(this)"></label></td>
								</tr>	  
							</tbody>
						</table>
					</div>
					<div class="flex two-input" id='Block1' style='display: none;'>					
						<table class="zakaz-data" border="0" cellspacing="0" cellpadding="0">
							<tr class="notauth">
								<td class="zakaz-txt"><font color="red">*</font> Система налогообложения</td>
								<td class="zakaz-inpt2">
									<table>
										<tr>
										 <td class="vbr1"><input type="radio" name="zap_nds" class="btn-color-blk" value="с НДС" required="required" /></td><td class="vbr2"> с НДС</td><td class="vbr1"><input type="radio" name="zap_nds" value="без НДС" required="required" /></td><td class="vbr2"> без НДС</td>
										</tr>
									</table>
								</td>
							 </tr>
							 <tr class="notauth">
								<td class="zakaz-txt"><font color="red">*</font> Прикрепить реквизиты</td>
								<td class="zakaz-inpt2">
								 <table>
									  <tr>
										  <td class="vbr-file"><input class="btn btn-default" type="file" name="filename_rekvizit" /></td>
										</tr>
									</table>
								</td>
							 </tr>		
						</table>
					</div>
					<span class="wpcf7-form-control-wrap" data-name="accept-this-3">
						<span class="wpcf7-form-control wpcf7-acceptance">
							<span class="wpcf7-list-item">
								<label>
									<input type="checkbox" name="accept-this-3" value="1" class="check-val">
									<span class="wpcf7-list-item-label">Вы соглашаетесь на обработку персональных данных</span>
								</label>
							</span>
						</span>
					</span>
					<input class="wpcf7-form-control wpcf7-submit has-spinner btn-send" type="submit" value="Отправить" disabled="" />
				</form>
			</div>
		</div>
	</div>
</div>
<script>
// Получаем все чекбоксы и кнопки
const checkboxes = document.querySelectorAll('input.check-val[type="checkbox"]');

// Функция проверки обязательных полей
function areRequiredFieldsFilled(modal) {
    const requiredFields = modal.querySelectorAll('[aria-required="true"]');
    return Array.from(requiredFields).every(field => {
        if (field.id === 'phone-input') {
            return field.value.trim().length >= 18; // Проверяем, чтобы длина была не менее 18 символов
        }
        return field.value.trim() !== '';
    });
}

// Функция для обработки активации кнопки в соответствующем модальном окне
function toggleButtonState(event) {
    // Определяем модальное окно, содержащее активированный чекбокс
    const modal = event.target.closest('.modal');
    if (!modal) return;

    // Находим кнопку в этом модальном окне
    const button = modal.querySelector('input.btn-send');
    if (!button) return;

    // Проверяем, есть ли активные чекбоксы и заполнены ли обязательные поля
    const isChecked = Array.from(modal.querySelectorAll('input.check-val[type="checkbox"]')).some(checkbox => checkbox.checked);
    const areFieldsFilled = areRequiredFieldsFilled(modal);

    // Активируем или деактивируем кнопку
    if (isChecked && areFieldsFilled) {
        button.removeAttribute('disabled');
    } else {
        button.setAttribute('disabled', '');
    }
}

// Добавляем обработчик события на каждый чекбокс
checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', toggleButtonState);
});

// Добавляем обработчик события для ввода в обязательные поля
const requiredFields = document.querySelectorAll('[aria-required="true"]');
requiredFields.forEach(field => {
    field.addEventListener('input', (event) => {
        const modal = event.target.closest('.modal');
        if (modal) toggleButtonState(event);
    });
});
</script>
<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<script>
    var path = '<?=PATH;?>',
        course = <?=$curr['value'];?>,
        symboleLeft = '<?=$curr['symbol_left'];?>',
        symboleRight = '<?=$curr['symbol_right'];?>';
</script>
<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="public/adminlte/plugins/select2/js/select2.full.min.js"></script>
<script src="js/validator.js"></script>
<script src="js/typeahead.bundle.js"></script>
<script src="js/imagezoom.js"></script>
<script src="js/slider.js"></script>
<script defer src="js/jquery.flexslider.js"></script>
<script src="js/swiper-bundle.min.js"></script>
<script src="/js/main.js?v=<?=filemtime(WWW.'/js/main.js')?>"></script>


<!-- IMask -->
<script>
// Функция для применения маски телефона в открытом модальном окне
function applyPhoneMaskToVisibleModal() {
  // Находим все модальные окна с классом "modal"
  const modals = document.querySelectorAll('.modal');

  // Проходим по каждому модальному окну
  modals.forEach(modal => {
    // Проверяем, если модальное окно отображается (display: block)
    if (modal.style.display === 'block') {
      // Ищем поле ввода телефона внутри модального окна
      const phoneEl = modal.querySelector('#phone-input');

      if (phoneEl) {
        // Применяем маску телефона с использованием IMask
        let phoneMask = IMask(phoneEl, {
          mask: '{+7} (#00) 000-00-00',
          definitions: { '#': /[012345679]/ },
          lazy: false,
          placeholderChar: ' '
        });

        // Добавляем обработчик события для ввода
        phoneEl.addEventListener("input", phoneInputHandler);
      }
    }
  });
}

// Вызов функции при открытии модального окна
document.addEventListener('click', (event) => {
  // Проверяем, если кликнули по элементу с классом "show_form"
  if (event.target.classList.contains('show_form')) {
    // Немного подождем, чтобы модальное окно стало видимым
    setTimeout(applyPhoneMaskToVisibleModal, 50);
  }
});
</script>
<!-- IMask -->

</body>
</html>
