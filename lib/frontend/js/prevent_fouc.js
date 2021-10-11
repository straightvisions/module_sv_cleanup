window.addEventListener('load', function() {
	document.querySelector('body').classList.remove('sv100_companion_fouc');
	document.dispatchEvent( new Event('sv100_companion_fouc_done') );
});