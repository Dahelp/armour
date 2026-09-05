/* Filters */
$('body').on('change', '.w_sidebar select', function(){
    var data = $('.w_sidebar option:selected').map(function () {
		return /^\d+$/.test(this.value) ? this.value : null;
	}).get().filter(function(value,index,values){return values.indexOf(value)===index;}).join(',');
    if(data){
		var requestUrl = new URL(window.location.href);
		requestUrl.searchParams.set('filter', data);
		requestUrl.searchParams.delete('page');
        $.ajax({
            url: requestUrl.pathname + requestUrl.search,
            type: 'GET',
            beforeSend: function(){
                $('.preloader').fadeIn(300, function(){
                    $('.product-one').hide();
                });
            },
            success: function(res){
                $('.preloader').delay(500).fadeOut('slow', function(){
                    $('.product-one').html(res).fadeIn();
					history.pushState({}, '', requestUrl.pathname + requestUrl.search);
                });
            },
            error: function () {
                alert('Ошибка!');
            }
        });
    }else{
        window.location = location.pathname;
    }
});

$(function(){


	$(".js-select2").select2({
			closeOnSelect : false,
			placeholder : "Click to select an option",
			allowHtml: true,
			allowClear: true,
			tags: false
		});

	$('.icons_select2').select2({
		width: "100%",
		templateSelection: iformat,
		templateResult: iformat,
		allowHtml: true,
		placeholder: "Click to select an option",
		dropdownParent: $( '.select-icon' ),//обавили класс
		allowClear: true,
		multiple: false
	});


	function iformat(icon, badge,) {
		var originalOption = icon.element;
		var originalOptionBadge = $(originalOption).data('badge');
	 
		return $('<span><i class="fa ' + $(originalOption).data('icon') + '"></i> ' + icon.text + '<span class="badge">' + originalOptionBadge + '</span></span>');
	}})

/* Sort product */
$(document).ready(function () {
 $(".sort-inner span").click(function () {
	var id = $(this).attr('id');
	
	$('.sort-inner span').toggleClass('active', false);
	$('.sort-inner span#'+$(this).attr('id')+'').toggleClass('active');
	$.ajax({
	    url: location.href,
            data: 'sort='+id,
            type: 'GET',
            beforeSend: function(){
                $('.preloader').fadeIn(300, function(){
                    $('.product-one').hide();
                });
		
            },
            success: function(res){
                $('.preloader').delay(500).fadeOut('slow', function(){
                    $('.product-one').html(res).fadeIn();
                    var url = location.search.replace(/sort(.+?)(&|$)/g, ''); //$2
                    var newURL = location.pathname + url + (location.search ? "&" : "?") + "sort=" + id;
                    newURL = newURL.replace('&&', '&');
                    newURL = newURL.replace('?&', '?');
                    history.pushState({}, '', newURL);
			
                });
		
            },
            error: function () {
                alert('Ошибка!');
            }
        });
		
 });   
});

/* Menu */
/* Раскрытие меню горизонтальное*/
$(document).ready(function () {
	// Находим все элементы с классом menu-item-type-custom
	const menuItems = document.querySelectorAll('.menu-item-type-custom');

	// Добавляем обработчики событий для каждого элемента
	menuItems.forEach(item => {
		item.addEventListener('mouseenter', () => {
			item.classList.add('open'); // Добавляем класс при наведении
		});

		item.addEventListener('mouseleave', () => {
			item.classList.remove('open'); // Убираем класс, когда курсор уходит
		});
	});
});

/* Раскрытие подменю горизонтальное */
document.addEventListener("DOMContentLoaded", () => {
    // Находим все элементы с классом fa-chevron-down
    const chevrons = document.querySelectorAll(".fa-chevron-down");

    // Добавляем обработчик клика для каждого из них
    chevrons.forEach(chevron => {
        chevron.addEventListener("click", () => {
            // Находим следующий элемент ul с классом sub-menu--level_2
            const subMenu = chevron.nextElementSibling;
            if (subMenu && subMenu.classList.contains("sub-menu--level_2")) {
                // Добавляем или убираем класс active
                subMenu.classList.toggle("active");
            }
        });
    });
});

/*Раскрытие подменю вертикального меню*/
document.addEventListener('DOMContentLoaded', () => {
    // Находим все элементы с классом right-arrow или down-arrow
    const listItems = document.querySelectorAll('li.right-arrow, li.down-arrow');

    listItems.forEach(item => {
        item.addEventListener('click', () => {
            const ul = item.querySelector('ul');

            if (ul) {
                if (item.classList.contains('right-arrow')) {
                    // Открываем ul и меняем класс
                    ul.style.display = 'block';
                    item.classList.remove('right-arrow');
                    item.classList.add('down-arrow');
                } else if (item.classList.contains('down-arrow')) {
                    // Закрываем ul и возвращаем класс
                    ul.style.display = 'none';
                    item.classList.remove('down-arrow');
                    item.classList.add('right-arrow');
                }
            }
        });
    });
});

/* Menu */

/*MODAL*/

// Добавляем обработчик события на загрузку документа
document.addEventListener("DOMContentLoaded", function() {
  // Находим все элементы с классом "show_form"
  const links = document.querySelectorAll(".show_form");

  // Перебираем найденные элементы
  links.forEach(link => {
    link.addEventListener("click", function(event) {
      event.preventDefault(); // Предотвращаем переход по ссылке

      // Получаем значение атрибута rel у ссылки
      const relValue = this.getAttribute("rel");

      // Находим div с соответствующим значением rel
      const targetDiv = document.querySelector(`div[rel='${relValue}']`);

      if (targetDiv) {
        // Меняем стиль отображения div
        targetDiv.style.display = "block";
      }
    });
  });

  // Находим все элементы с классом "a_close_box"
  const closeButtons = document.querySelectorAll(".a_close_box");

  // Перебираем найденные элементы
  closeButtons.forEach(button => {
    button.addEventListener("click", function(event) {
      event.preventDefault(); // Предотвращаем переход по ссылке

      // Получаем значение атрибута rel у кнопки
      const relValue = this.getAttribute("rel");

      // Находим div с соответствующим значением rel
      const targetDiv = document.querySelector(`div[rel='${relValue}']`);

      if (targetDiv) {
        // Меняем стиль отображения div обратно на none
        targetDiv.style.display = "none";
      }
    });
  });
});


/*MODAL*/

/* Search */
var products = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        wildcard: '%QUERY',
        url: path + '/search/typeahead?query=%QUERY'
    }
});

products.initialize();

$("#typeahead").typeahead({
    // hint: false,
    highlight: true
},{
    name: 'products',
    display: 'name',
    limit: 10,
    source: products,
	templates: {
        empty: 'Товар не найден. Попробуйте ввести запрос по другому.', //optional
        suggestion: function(el){return '<a href="'+el.alias+'"><div class="tt-images"><img class="img-fit" src="images/product/mini/'+el.img+'" /></div><div class="tt-info"><div class="tt-name">'+el.name+'</div><div class="tt-price"><span class="fw-600 fs-16 text-primary">'+el.price+' руб</span></div></div></a>';}
    }
});

$('#typeahead').bind('typeahead:select', function(ev, suggestion) {
    // console.log(suggestion);
    window.location = path + '/search/?s=' + encodeURIComponent(suggestion.name);
});

/*Cart*/
function setProductCartQuantity(id, qty) {
	qty = Math.max(0, parseInt(qty, 10) || 0);
	$('.qty-item-' + id).val(qty > 0 ? qty : 1).attr('value', qty > 0 ? qty : 1);
	$('.my-minus-' + id + ', .my-plus-' + id).attr('data-qty', qty).data('qty', qty);
	if (qty > 0) {
		$('.korzina-' + id).attr('style', 'display: none !important');
		$('.vkorzine-' + id + ', .my_quant-' + id).css('display', 'inline-flex');
	} else {
		$('.my_quant-' + id + ', .vkorzine-' + id).attr('style', 'display: none !important');
		$('.korzina-' + id).css('display', 'inline-flex');
	}
}

function refreshCartSurfaces() {
	if ($('.product-cart').length) {
		$.get('/cart/deletecart', {id: 0}, function (html) {
			if ($(html).filter('.cart-empty-state').length || $(html).find('.cart-empty-state').length) {
				window.location.reload();
				return;
			}
			$('.product-cart').html(html);
		});
	}
	if ($('#exampleModalLive .modal-body').length && $('#exampleModalLive').hasClass('show')) {
		$.get('/cart/show', function (html) { $('#exampleModalLive .modal-body').html(html); });
	}
}

$('body').on('click', '.add-to-cart-link', function(e){
     e.preventDefault();
     var id = $(this).data('id'),
	 max = $(this).data('max'),
	 qty = $('.qty-item-'+id+'').val() ? $('.qty-item-'+id+'').val() : 1,
	 mod = $('.available select').val();
	 var cqty = $('.simpleCart_qty').text();
     $.ajax({
         url: '/cart/add',
         data: {id: id, qty: qty, mod: mod, max:max, format: 'json'},
         type: 'GET',
         dataType: 'json',
         success: function(res){
			setProductCartQuantity(id, res.result);
			$('.cart-contents').html("<span id=\"cart-total\" class=\"header-cart-count count flex-align-center flex-center simpleCart_qty\">"+res.result2+"</span><span class=\"header-cart-word\">Корзина</span>");
			refreshCartSurfaces();
         },
         error: function(){
             alert('Ошибка! Попробуйте позже');
         }
     });
});

$('body').on('input', '.detail-quantity', function(){

	var value = this.value.replace(/[^0-9]/g, '');

	if (value < $(this).data('min')) {

		this.value = $(this).data('min');

	} else if (value > $(this).data('max')) {

		this.value = $(this).data('max');

	} else {
		this.value = value;
	}

});

$('#exampleModalLive .modal-body').on('click', '.del-item', function(){
    var id = $(this).data('id');
    $.ajax({
        url: '/cart/delete',
        data: {id: id},
        type: 'GET',
        success: function(res){
            showCart(res);
		$('.korzina-'+id+'').attr('style', 'display: block !important');
		$('.vkorzine-'+id+'').css('display', 'none');
		recalCart(res);	
        },
        error: function(){
            alert('Error!');
        }
    });
});

$('body').on('click', '.del-items', function(){
    var id = $(this).data('id');
    $.ajax({
        url: '/cart/deletecart',
        data: {id: id},
        type: 'GET',
        success: function(res){
		recalCart(res);
        },
        error: function(){
            alert('Error!');
        }
    });
});

$('body').on('click', '.my-plus', function(){
    var id = $(this).data('id');
	var qty = $(this).data('qty');
    $.ajax({
        url: '/cart/pluscart',
        data: {id: id, qty: qty},
        type: 'GET',
		dataType: 'json',
        success: function(res){
			setProductCartQuantity(id, res.result);
			$('.cart-qty').html(""+res.result2+"");
			$('.simpleCart_qty').html(""+res.result2+"");
			refreshCartSurfaces();
		},
        error: function(){
            alert('Ошибка при пересчёте!');
        }
    });
});

$('body').on('click', '.my-minus', function(){
    var id = $(this).data('id');
	var qty = $(this).data('qty');
    $.ajax({
        url: '/cart/minuscart',
        data: {id: id, qty: qty},
        type: 'GET',
		dataType: 'json',
        success: function(res){
			setProductCartQuantity(id, res.result);
			$('.cart-qty').html(""+res.result2+"");
			$('.simpleCart_qty').html(""+res.result2+"");
			refreshCartSurfaces();
		},
        error: function(){
            alert('Ошибка при пересчёте!');
        }
    });
});

function recalCart(cart){
	if($.trim(cart) == '<h3>Корзина пуста</h3>'){
        $('#exampleModalLive .modal-footer a, #exampleModalLive .modal-footer .btn-primary').css('display', 'none');	
   	 }else{
        $('#exampleModalLive .modal-footer a, #exampleModalLive .modal-footer .btn-primary').css('display', 'inline-block');
    	}
    	$('#exampleModalLive .modal-body').html(cart);
	$('.product-cart').html(cart);

	if($('.cart-qty').text()){
      		 $('.simpleCart_qty').html($('#exampleModalLive .cart-qty').text());	
    	}else{
      	  	$('.simpleCart_qty').text('0');
   	}
  	if($('.cart-sum').text()){
   	     $('.simpleCart_total').html($('#exampleModalLive .cart-sum').text());	
  	}else{
   	     $('.simpleCart_total').text('0');
   	}
}

function showCart(cart){
	if($('.cart-qty').text()){
       	 $('.simpleCart_qty').html($('#exampleModalLive .cart-qty').text());	
    	}else{
        $('.simpleCart_qty').text('0');
    }
    if($('.cart-sum').text()){
        $('.simpleCart_total').html($('#exampleModalLive .cart-sum').text());	
    }else{
        $('.simpleCart_total').text('0');
    }
	
}

function showCarts(cart){

    $('#exampleModalLive .modal-body').html(cart);
    $('#exampleModalLive').modal();
	if($('.cart-qty').text()){
       	 $('.simpleCart_qty').html($('#exampleModalLive .cart-qty').text());	
    	}else{
        $('.simpleCart_qty').text('0');
    }
    if($('.cart-sum').text()){
        $('.simpleCart_total').html($('#exampleModalLive .cart-sum').text());	
    }else{
        $('.simpleCart_total').text('0');
    }
	
}

function getCart() {
    $.ajax({
        url: '/cart/show',
        type: 'GET',
        success: function(res){
            showCart(res);		
        },
        error: function(){
            alert('Ошибка! Попробуйте позже');
        }
    });
}

function clearCart() {
    $.ajax({
        url: '/cart/clear',
        type: 'GET',
        success: function(res){
            showCart(res);
		$('.clear-korzina').attr('style', 'inline-display: block !important');
		$('.clear-vkorzine').css('display', 'none');
		recalCart(res);
        },
        error: function(){
            alert('Ошибка! Попробуйте позже');
        }
    });
}
/*Cart*/

$('#currency').change(function(){
    window.location = 'currency/change?curr=' + $(this).val();
});

$('.available select').on('change', function(){
    var modId = $(this).val(),
        color = $(this).find('option').filter(':selected').data('name'),
        price = $(this).find('option').filter(':selected').data('price'),
        basePrice = $('#base-price').data('base');
    if(price){
        $('#base-price').text(symboleLeft + price + symboleRight);
    }else{
        $('#base-price').text(symboleLeft + basePrice + symboleRight);
    }
});

/*Scroll Menu*/
document.addEventListener("DOMContentLoaded", function () {
    var previousScrollPosition = 0;
    var masthead = document.getElementById("masthead");
    var navigation = masthead.querySelector(".storefront-primary-navigation");
    var mastheadHeight = masthead.offsetHeight;
    var navHeight = navigation.offsetHeight;
    var threshold = mastheadHeight - navHeight;

    function handleScroll() {
        var currentScrollPosition = window.scrollY;

        if (currentScrollPosition <= threshold) {
            navigation.classList.remove("nav-up", "nav-down", "cart_on");
        } else if (previousScrollPosition <= currentScrollPosition && currentScrollPosition > threshold + navHeight) {
            navigation.classList.remove("nav-down");
            navigation.classList.add("nav-up", "cart_on");
        } else {
            navigation.classList.remove("nav-up", "cart_on");
            navigation.classList.add("nav-down");
        }

        previousScrollPosition = currentScrollPosition;
    }

    window.addEventListener("load", function () {
        if (window.scrollY > mastheadHeight) {
            navigation.classList.add("nav-up", "cart_on");
        } else {
            navigation.classList.remove("nav-up", "cart_on");
        }
    });

    window.addEventListener("scroll", handleScroll);
});
/*
(function (t) {
  function e(t) {
    var e = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
    var a = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
    var n = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
    return function (e) {
      var o,
        i = [],
        r = [1, 3],
        d = "rgba(255, 255, 255, " + e + ")",
        e = document.createElement("canvas"),
        c = e.getContext("2d"),
        s = t.width() + n,
        l = t.height() + a;
      e.width = s, e.height = l, e.style.position = "absolute", e.style.top = "0", e.style.left = "0", t.append(e);
      for (var h = 0; h < 50; h++) o = {
        x: Math.random() * s,
        y: Math.random() * l,
        speed: .2 * Math.random(),
        radius: Math.random() * (r[1] - r[0]) + r[0],
        direction: .5 < Math.random() ? 1 : -1,
        speedX: .1 * Math.random()
      }, i.push(o);
      !function t() {
        c.clearRect(0, 0, s, l);
        for (var e = 0; e < i.length; e++) c.beginPath(), c.fillStyle = d, c.arc(i[e].x, i[e].y, i[e].radius, 0, 2 * Math.PI), c.fill(), i[e].x += i[e].speedX * i[e].direction, i[e].y += i[e].speed, i[e].y > l && (i[e].y = 0), (i[e].x < 0 || i[e].x > s) && (i[e].direction = -i[e].direction);
        requestAnimationFrame(t);
      }();
    }(e);
  }
  t(document).ready(function () {
    t("body").addClass("newYear2025"), e(t(".storefront-primary-navigation"), .5), e(t(".site-contacts"), .3, 60), e(t(".site-footer .col-full"), .3), t(".category-box__block .menu__title").each(function () {
      e(t(this), .2, 20, 80);
    });
  });
})(window.jQuery = window.$ = jQuery);
*/
/*scroll tabs product*/
document.addEventListener('DOMContentLoaded', () => {
    // Находим div с классом "tab-list"
    const tabList = document.querySelector('.tab-list');

    if (tabList) {
        // Находим все ссылки внутри div с классом "tab-list" с атрибутом href, начинающимся с '#'
        const links = tabList.querySelectorAll('a[href^="#"]');

        // Функция для обновления активной ссылки
        const updateActiveLink = () => {
            let activeLink = null;

            links.forEach((link, index) => {
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const rect = targetElement.getBoundingClientRect();
                    const offset = 200; // Отступ в пикселях

                    // Проверяем, находится ли div в области видимости или выше
                    if (rect.top <= offset && rect.bottom > offset) {
                        activeLink = link;
                    }

                    // Если последний div, делаем его активным, если прокрутка ниже
                    if (index === links.length - 1 && rect.top <= offset) {
                        activeLink = link;
                    }
                }
            });

            // Устанавливаем класс active только для одного элемента
            links.forEach(link => link.classList.remove('active'));
            if (activeLink) {
                activeLink.classList.add('active');
            }
        };

        // Добавляем обработчики событий на клики по ссылкам
        links.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault(); // Отменяем стандартное поведение ссылки

                // Получаем значение href (id целевого div)
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    // Рассчитываем позицию с учётом отступа 60px
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - 60;

                    // Прокручиваем страницу к элементу с учётом отступа
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth' // Плавная прокрутка
                    });
                }
            });
        });

        // Добавляем обработчик события на прокрутку
        window.addEventListener('scroll', updateActiveLink);

        // Инициализируем активное состояние при загрузке страницы
        updateActiveLink();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Находим все ссылки внутри div с классом "box-price-btn"
    const links = document.querySelectorAll('.box-price-btn a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Предотвращаем стандартное поведение ссылки

            // Получаем ID из href
            const targetId = this.getAttribute('href').substring(1);

            // Ищем элемент с этим ID
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                // Вычисляем позицию с учётом сдвига
                const offset = 120; // Значение не докрутки в пикселях
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                // Скроллим к элементу
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth' // Плавный скроллинг
                });
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.querySelector('.header-menu-mobile');
    const menu = document.querySelector('.menu-wrapper');

    if (menuButton && menu) {
        menu.id = menu.id || 'mobile-site-menu';
        menuButton.addEventListener('click', function () {
            const isOpen = menu.classList.toggle('mobile-menu-open');
            menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            menuButton.setAttribute('aria-label', isOpen ? 'Закрыть каталог' : 'Открыть каталог');
            const icon = menuButton.querySelector('.fas');
            if (icon) {
                icon.classList.toggle('fa-bars', !isOpen);
                icon.classList.toggle('fa-times', isOpen);
            }
        });
    }

    const phoneButton = document.querySelector('.mobile-phone-toggle');
    const phonePanel = document.querySelector('.mobile .hide-phone');
    if (phoneButton && phonePanel) {
        phoneButton.addEventListener('click', function () {
            const isOpen = phonePanel.classList.toggle('mobile-phone-open');
            phoneButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            phonePanel.style.display = isOpen ? 'block' : '';
        });
    }
});
