$boot(function() {
	
	datetime('#pubdate');
	autosize('#body');
	autosize('#summary');
	wysiwyg('#body','#html','#edtrig');
	//filedrop('#drop', '#dropped', '#attach');
	
	attachView('#attach', '#dropped');
	
	// Enable elements to be shown after page load
	$each( $all('.showload'), function(e, i, t) {
		$toggle(e, 'showload');
	});
	
	$each( $all('.hideload'), function(e, i, t) {
		$toggle(e, 'hideload');
	});
	
	var 
	ns	= 2000,
	nh	= function(){
			$each( $all('nav.showload'), function(al) {
				$toggle(al, 'showload');
			});
		},
	nht	= setTimeout(nh, ns);
	
	$each( $all('nav.showload'), function(al) {
		$on( al, 'mouseover', function() {
			clearTimeout( nht );
		})
		$on( al, 'mouseout', function() {
			al.classList.remove('showload');
			al.className += ' showload';
			nht = setTimeout( nh, ns);
		});
	});
});
