{literal}(function(g) {
	document.getElementById("sInformer"+g.biid).innerHTML=g.data;
	function loadScripts(array,callback){
	    var loader = function(src,handler){
	    	if(src[0]=='css'){
	    		var script = document.createElement("link");
	    		script.href = src[1];
	    		script.rel = 'stylesheet';
	    	}else{
	    		var script = document.createElement("script");
	    		script.src = src[1];
	    		script.type = 'text/javascript';
	    	}
	        script.onload = script.onreadystatechange = function(){
	        script.onreadystatechange = script.onload = null;
	        	handler();
	        }
	        var head = document.getElementsByTagName("head")[0];
	        (head || document.body).appendChild( script );
	    };
	    (function(){
	        if(array.length!=0){
	        	loader(array.shift(),arguments.callee);
	        }else{
	        	callback && callback();
	        }
	    })();
	}
	
	if(g.view_type=='hs'){
		var deps=[['css', '{/literal}{$site_url}{literal}/apps/banner/site/template/slideshow.css'], ['js', '{/literal}{$site_url}{literal}/apps/banner/site/template/jquery.jcarousellite.min.js']];
	}else if(g.view_type=='hs2'){
		var deps=[['css', '{/literal}{$site_url}{literal}/apps/banner/site/template/example.css'], ['js', '{/literal}{$site_url}{literal}/apps/banner/site/template/example.js']];
	}else if(g.view_type=='vs'){
		var deps=[['css', '{/literal}{$site_url}{literal}/apps/banner/site/template/slideshow.css'], ['js', '{/literal}{$site_url}{literal}/apps/banner/site/template/jquery.jcarousellite.min.js']];
	}else if(g.view_type=='hs_o'){
		var deps=[
		          ['css', '{/literal}{$site_url}{literal}/apps/banner/site/template/owl.carousel.css'], 
		          ['js', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js'], 
		          ['js', '{/literal}{$site_url}{literal}/apps/banner/site/template/owl.carousel.min.js']
		          ];
	}
	
	loadScripts(deps, scriptLoadHandler);
	
	function scriptLoadHandler() {
		initSlider();
	}
	
	function initSlider(){
		if(g.view_type=='hs'){
			var params={};
			params.btnNext='.sInformer-slider-'+g.biid+' .next';
			params.btnPrev='.sInformer-slider-'+g.biid+' .prev';
			params.visible=g.visels;
			if(g.autoslide){
				params.auto=4000;
			}
			var w=$('.sInformer-slider-'+g.biid+' .sInformer-slider-hor-container').width();
			var i=parseInt(w/g.ewidth);
			var i=parseFloat(w/g.ewidth)/*.toPrecision(1)*/;
			//console.log(i.toPrecision(1));
			//var i=w/g.ewidth;
			params.visible=i;
			$('.sInformer-slider-'+g.biid+' .sInformer-carousel').jCarouselLite(params);
		}else if(g.view_type=='hs2'){
			$("div.gallery-3").slider();
		}else if(g.view_type=='vs'){
			var params={};
			params.btnNext='.sInformer-slider-'+g.biid+' .next';
			params.btnPrev='.sInformer-slider-'+g.biid+' .prev';
			params.visible=g.visels;
			/*params.visible=20;*/
			params.vertical=true;
			if(g.autoslide){
				params.auto=4000;
			}
			$('.sInformer-slider-'+g.biid+' .sInformer-carousel').jCarouselLite(params);
		}else if(g.view_type=='hs_o'){
			var owl = $("#sInformer-owl-demo-"+g.biid);
			
			var w=owl.width();
			
			var i=parseInt(w/g.ewidth);
			
			  owl.owlCarousel({
			      items : i, //10 items above 1000px browser width
			      /*itemsDesktop : [1000,5], //5 items between 1000px and 901px
			      itemsDesktopSmall : [900,3], // betweem 900px and 601px
			      itemsTablet: [600,2], //2 items between 600 and 0*/
			      itemsMobile : false // itemsMobile disabled - inherit from itemsTablet option
			  });
		}
		
	}
	
})({/literal}{$data}{literal})
{/literal}