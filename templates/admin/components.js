// Helpers

// Rudimentary browser detection
function browser() {
	var ua = navigator.userAgent.toLowerCase();
	if (ua.indexOf('safari') != -1) {
		if (ua.indexOf('chrome') > -1) {
			return 'c';
		} else {
			return 's';
		}
	} else {
		return 'n';
	}
}

// Default option constructor
function defaults(def, opts) {
	if(opts != null && opts != undefined && opts != 'undefined') {
		for( var o in opts ) {
			def[o] = opts[o];
		}
	}
	return def;
}

// Special character formatter (for image file names etc...)
function entities(s) {
	var e = {
		'"' : '&quot;',
		'&' : '&amp;',
		'<' : '&lt;',
		'>' : '&gt;'
	};
	return s.replace(/["&<>]/g, function (m) {
		return e[m];
	});
}


// Components

// Textarea height autoresizer
var autosize	= (function(t, h) {
	var tx = $get(t);
	if (!tx) {
		return;
	}
	var hmax = h || 2000;
	tx.style.resize	= 'none';
	var adjust = function() {
		tx.style.height = '';
		tx.style.height = 
			Math.min( tx.scrollHeight + 2, hmax ) + 'px';
	};
	
	$on( tx, 'input', adjust );
	$on( tx, 'focus', adjust );
});

// Publish date updater
var datetime	= (function(t) {
	var dt = document.querySelector(t);
	if (!dt || dt.value != '') {
		return;
	}
	var du, methods = {}
	function pub() {
		var n = new Date(),y,mo,d,h,m;
		
		y = n.getFullYear();
		mo = n.getMonth().toString().length === 1 ? '0' + 
		(n.getMonth() + 1).toString() : n.getMonth() + 1;
		
		d = n.getDate().toString().length === 1 ? '0' + 
		(n.getDate()).toString() : n.getDate();
		
		h = n.getHours().toString().length === 1 ? '0' + 
		n.getHours().toString() : n.getHours();
		
		m = n.getMinutes().toString().length === 1 ? '0' + 
		n.getMinutes().toString() : n.getMinutes();
		
		return y + '-' + mo + '-' + d + 'T' + h + ':' + m;
	}
	
	// Chrome/Safari format
	function cs() {
		dt.value = pub();
		du = setInterval( function() {
			dt.value = pub();
		}, 1000 );
	}
	
	// Others
	function ot() {
		dt.value = 
			new Date(pub()).toLocaleString();
			du = setInterval( function() {
			dt.value = new Date(pub()).toLocaleString();
		}, 1000 );
	}
	
	switch(browser()) { 
		// Chrome/Safari needs this in UTC format
		case 'c':
		case 's':
			cs();
			break;
		
		default:
			ot();
	}
	
	$on(dt, 'mousedown', function() {
		clearInterval(du);
	});
});

var datalist	= (function(url,s,t) {
	var dest	= $get(t);
	var search	= $get(s);
	var cache	= [];
	
	if (!dest || !search) {
		return;
	}
	
	var cache = {}, req = null, wait = false, url = null;
	
	function populate(data, dest) {
		data.forEach(function(i) {
			var o = document.createElement('option');
			o.value = i;
			$append(dest,o);
		});
	}
	
	function store(key, data) {
		cache[cache.count-1] = [key,data];
	}
	
	function get(terms) {
		// Wait for user to stop typing
		if ( wait ) { return; }
		
		req.open('GET', url + terms, true);
		req.send();
	}
	
	function find(k) {
		// Check cache to see if terms already exist
		// Get the entire input on keydown. 
		// Use that as an index key to store returned search values
		var r = null;
		cache.forEach(function(e,i,a){
			// TODO
		});
	}
	
	req = new XMLHttpRequest();
	req.onreadystatechange = function(resp) {
		if (req.readyState === 4) {
			if (req.status === 200) {
				var data = JSON.parse(req.responseText);
				popDataList(data,dest);
			} else {
				// Error
			}
		}
	};
});

var library	= (function(){
	// TODO
});

// Safe to preview
var 
uPreview		= {
	'image/png'	: true,
	'image/jpg'	: true,
	'image/jpeg'	: true,
	'image/bmp'	: true,
	'image/gif'	: true,
	'image/svg'	: true,
	'image/svg+xml'	: true
};

var preview	= (function(dest, e, file) {
	var img		= new Image();
	var fig	= $new( 'figure' );
	if ( uPreview[file.type] !== true ) {
		img.src		= '/templates/admin/file.png';
	} else {
		img.src		= e.target.result;
	}
	$attribute(fig, 'title', entities(file.name));
	$append(fig,img);
	$append(dest,fig);
});

var filePreview	= (function(dest,src) {	
	for (var i = 0; i < src.files.length; i++ ) {
		var r		= new FileReader();
		
		r.onloadend = (function(file) {
			return function(e) {
				preview(dest, e, file);
			};
		})(src.files[i]);
		r.readAsDataURL(src.files[i]);
	}
});

var attachView	= (function(file, view, fd) {
	var 
	src	= $get(file),
	dest	= $get(view)
	
	if (!src || !dest) {
		return;
	}
	
	var 
	update = function() {
		filePreview(dest,src);
	};
	
	$on( src, 'change', update );
});

var fileParse = (function(fi, pr, fd, files) {
	var upf	= $attribute_of(fi, 'name');
	
	for (var i = 0; i < files.length; i++) {
		if (!!window.formdata) {
			fd.append(upf, files[i]);
		}
		var r		= new FileReader();
		r.onloadend = (function(file) {
			return function(e) {
				preview(pr, e, file);
			};
		})(files[i]);
		r.readAsDataURL(files[i]);
	}
});

// Needs more work
var filedrop	= (function(dr, st, field, fd) {
	var 
	d	= $get(dr),
	fi	= $get(field),
	pr	= $get(st);
	
	if (!d || !pr || !fi) {
		return;
	}
	
	var
	filter	= function(e) {
		var files	= e.dataTransfer.files;
		
		if (e.dataTransfer.types) {
			fileParse(fi,pr,fd,files);
		} else {
			alert( 'Drag and Drop is not available' );
		}
	};
	
	d.ondragover = function() {
		$toggle(this, 'drophover');
		return false; 
	};
	
	d.ondragend = function() { 
		$toggle(this, 'drophover');
		return false;
	};
	
	$on( d, 'drop', function(e) {
		$cancel(e);
		filter(e);
	});
});

var wysiwyg = (function(text,body,trig) {
	var 
	t		= $get(text),
	p		= $get(body),
	et		= $get(trig);
	
	if (!p || !t || !et) {
		return;
	}
	
	var 
	bplace	= $attribute_of( t, 'placeholder' ) || 'Body',
	edt	= new showdown.Converter(),
	eopts	= {
		toolbar		: {
			buttons : [
				'bold', 'italic', 'underline', 'anchor', 
				'h2','h3', 'quote'
			]
		},
		placeholder	: { text: bplace },
		autolink	: true,
		paste		: {
			cleanPastedHTML : true,
			cleanAttrs	: ['class', 'style', 'dir'],
			cleanTags	: ['meta','iframe','embed','object']
		},
		imageDragging: false
	},
	picFmt	= 
	function(innerHTML, node) {
		
	},
	imgFmt	= 
	function(innerHTML, node) {
		var
		a = $attribute_of( node, 'alt' )	|| 'alt text',
		w = node.style.width,
		d = $attribute_of( node, 'longdesc' )	|| '',
		s = $attribute_of( node, 'src' ),
		p = node.parentNode.nodeName.toLowerCase();
		
		if ( p == 'figure' || p == 'picture' ) {
			if ( p == 'picture' ) {
				return '<img src="' + s + '" alt="' + a + 
					'" height="auto" />\n';
			}
			return '<picture><img src="' + s + '" alt="' + a + 
				'" height="auto" /></picture>';
		}
		
		if ( w == null ||  w == '' ) {
			w = $attribute_of( node, 'width' ) || '';
		}
		
		if ( w == null ||  w == '' ) {
			return '!['+ a +']('+ s +')\n\n';
		}
		w  = w.substring(0, w.lastIndexOf('px'));
		return '<img src="' + s + '" alt="' + a + 
			'" width="' + w + '" height="auto" />\n\n';
	},
	figFmt	= 
	function(innerHTML, node) {
		return '<figure>' + innerHTML + '</figure>';
	},
	topts	= { // https://github.com/domchristie/to-markdown
		gfm:		true,
		converters:	[{
			filter:		['img', 'image'],
			replacement:	imgFmt
		}, {
			filter:		'figure',
			replacement:	figFmt
		}]
	},
	tags	= [ 'youtube', 'vimeo', 'video', 'audio'],
	toEd	= function() { p.innerHTML = edt.makeHtml(t.value); },
	toMd	= function() { t.value = toMarkdown(p.innerHTML, topts); },
	toTxt	= function() { t.value	= p.innerHTML; },
	fromSh	= function(text) {
			var r = /([\w\-.:]+)\s*=\s*"([^"]*)"/g, v = {}, m;
			while( m = r.exec(text) ) {
				v[m[1]] = m[2];
			}
			for (var i = 0; i<v.length; i++) {
			
			}
			return text;
		},
	toSh	= function(html) { return html; },
	tcursor = 
	function(text) {
		var
		i	= t.selectionStart,
		v	= t.value();
		
		t.val( v.substring(0, i) + text + v.substring(i) );
		toEd();
	};
	
	if ( t.value ) {
		eopts.placeholder = false;
	}
	
	var m		= new MediumEditor(p, eopts);
	et.checked	= false;
	
	toEd();
	
	$on( t,	'blur',		toEd );
	$on( p,	'blur',		toMd );
	$on( p,	'change',	toMd );
	$on( p,	'mouseout',	toMd );
});
